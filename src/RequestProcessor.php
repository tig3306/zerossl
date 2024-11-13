<?php

namespace ZeroSSL\CliClient;

use Exception;
use JsonException;
use ZeroSSL\CliClient\Dto\Options;
use ZeroSSL\CliClient\Enum\CertificateStatus;
use ZeroSSL\CliClient\Enum\CertificateValidationType;
use ZeroSSL\CliClient\Exception\ConfigurationException;
use ZeroSSL\CliClient\Exception\NotYetSupportedException;
use ZeroSSL\CliClient\Exception\OpenSSLGenerationException;
use ZeroSSL\CliClient\Exception\OpenSSLSigningException;
use ZeroSSL\CliClient\Exception\RemoteRequestException;
use ZeroSSL\CliClient\Signers\SelfSigner;
use ZeroSSL\CliClient\Signers\ZeroSSL\ApiEndpointRequester;
use JetBrains\PhpStorm\ArrayShape;

class RequestProcessor
{

    public const ISSUANCE_SLEEP_SECONDS = 3;
    public const ISSUANCE_CHECK_SECONDS = 10;
    public const VALIDATION_SUBFOLDER = "validation";
    public static bool $out = true;

    /**
     * @param Options $options
     * @param bool $nonInteractive
     * @return array
     * @throws ConfigurationException
     * @throws OpenSSLGenerationException
     * @throws OpenSSLSigningException
     * @throws RemoteRequestException
     * @throws NotYetSupportedException
     * @throws JsonException
     */
    #[ArrayShape(["private_key" => "string", "csr" => "string", "certificate" => "string",])]
    public static function generate(Options $options, bool $nonInteractive = false): array{
        $result = [];
        $pkey = CSRTool::generatePrivateKey($options->useEccDefaults, $options->privateKeyOptions);
        openssl_pkey_export($pkey, $pKeyOut, $options->privateKeyPassword);

        self::dumpGeneratedContent("PRIVATE KEY", $pKeyOut, !$options->noOut);

        if(!empty($options->targetPath)) {
            //file_put_contents($options->fPrivate,$pKeyOut);

            file_put_contents($options->sslDirDomain  .$options->privateKey ,$pKeyOut);
            openssl_pkey_export_to_file($pkey,$options->targetPath . DIRECTORY_SEPARATOR . "private" . $options->suffix . ".key",$options->privateKeyPassword);
        }

        $csr = CSRTool::getCSR($pkey,$options->csrData,$options->domains,$options->useEccDefaults,$options->csrOptions);
        openssl_csr_export($csr, $csrOut);
        self::dumpGeneratedContent("CSR", $csrOut, !$options->noOut);

        if(!empty($options->targetPath)) {
            file_put_contents($options->sslDirDomain  .$options->csrPem ,$csr);
            openssl_csr_export_to_file($csr,$options->targetPath . DIRECTORY_SEPARATOR .$options->csrPem,$options->privateKeyPassword);

        }

        $certificateOut = null;
        $certificateOutPfx = null;

        if(!$options->csrOnly) {

            // sign with ZeroSSL
            if($options->apiKey) {
                echo "\nInitiating contact with ZeroSSL CA, relax a little bit ... ".PHP_EOL;

                if(is_null($options->validationType)) {
                    throw new ConfigurationException("You need to configure a validation method for a ZeroSSL certificate.");
                }

                // call create certificate
                $requester = new ApiEndpointRequester($options->debug);
                $endpoint = $requester->apiEndpointInfo->create_certificate;
                $draft = $requester->requestJson($endpoint,["API_URL" => $requester->apiUrl, "ACCESS_KEY" => $options->apiKey],["certificate_domains" => implode(",",$options->domains), "certificate_csr" => $csrOut, "certificate_validity_days" => $options->validityDays, "strict_domains" => 1],!empty($options->debug));

                if(!empty($draft["id"])) {

                    $hash = $draft["id"];

                    if(!$options->noOut) {
                        echo "\nFirst step successfully proceeded ".PHP_EOL;

                        self::dumpGeneratedContent("CERTIFICATE HASH",$hash,true);                                      //todo hash
                        echo "\nFirst step successfully initiated. Now lets verify your ownership and sign it ".PHP_EOL;
                    }

                    if($options->targetPath) {
                        $validationPath = $options->targetPath . DIRECTORY_SEPARATOR . self::VALIDATION_SUBFOLDER;
                        if($options->validationType !== CertificateValidationType::EMAIL && !is_dir($validationPath) && !mkdir($concurrentDirectory = $validationPath) && !is_dir($concurrentDirectory)) {
                                throw new ConfigurationException(sprintf('Creation of validation files folder "%s" was not possible. Permission problem?', $concurrentDirectory));
                        }
                    }

                  if ($options->validationType==CertificateValidationType::HTTP_CSR_HASH) {
                            $remoteFileInfo = "";
                            foreach($draft["validation"]["other_methods"] as $domain => $info) {
                                if(!array_key_exists("file_validation_url_http",$info)) {
                                    throw new ConfigurationException("HTTP file upload validation is not possible for this certificate.");
                                }
                                $tmp = explode("/",$info["file_validation_url_http"]);
                                $fileName = end($tmp);

                                if (!empty($info["file_validation_url_http"]??'')){
                                     $path = parse_url($info["file_validation_url_http"], PHP_URL_PATH);
                                     $filename = basename($path);
                                    echo 'mkdir path'. $options->targetPath. dirname($path);
                                if ( !is_dir( $options->targetPath. dirname($path))) mkdir( $options->targetPath. dirname($path),0777,true);
                                    file_put_contents( $options->targetPath.$path,implode("\n",$info["file_validation_content"]));
                                }


                                $currentInfo = "URL: " . $info["file_validation_url_http"] . "\n" . implode("\n",$info["file_validation_content"])."\n\n";
                                file_put_contents($validationPath . DIRECTORY_SEPARATOR . $domain . "-validate-" . $options->suffix . "-" . $fileName  . ".txt", $currentInfo);
                                $remoteFileInfo .= $currentInfo;
                            }
                            self::dumpGeneratedContent("FILES",$remoteFileInfo, !$options->noOut);

                        }

                    /*    default: {
                            throw new NotYetSupportedException("Unsupported validation method.");
                        }
          */

                    if($nonInteractive && !$options->createOnly) {
                        $verificationResult = self::zeroSign($hash,$options,true);
                        if(!is_null($verificationResult)) {
                            $result = array_merge($result,$verificationResult);
                        }
                    } else {
                        echo "\nEnter VERIFY and press enter for issuing the certificate (any other input will finish script execution): ";
                        $handle = fopen ("php://stdin", 'rb');
                        $line = fgets($handle);
                        if(trim($line) === 'VERIFY'){
                            fclose($handle);
                            $verificationResult = self::zeroSign($hash,$options,true);
                            if(!is_null($verificationResult)) $result = array_merge($result,$verificationResult);

                        } else {
                            echo "\nCertificate will not be signed for now. Continue with verification in the CLI seperatly or continue in your ZeroSSL dashboard: https://app.zerossl.com/certificate/verify/".$hash;
                            fclose($handle);
                        }
                    }
                    // choose validation type
                } elseif(!$options->noOut) {
                    echo "\nThe CA does unfortunately not accept your certificate request. Check what is wrong and maybe try with a different input 🧐 Here is the answer:\n";
                    self::dumpGeneratedContent("ANSWER FROM ZEROSSL",print_r($draft,true),true);
                }
            }
            else {
                $certificate = SelfSigner::sign($csr,$pkey,$options->validityDays,$options->csrOptions); // could add serial argument here
                openssl_x509_export($certificate, $certificateOut);
                openssl_pkcs12_export($certificate, $certificateOutPfx,$pkey,$options->privateKeyPassword);
                self::dumpGeneratedContent("CERTIFICATE", $certificateOut, !$options->noOut);
                self::dumpGeneratedContent("PKCS#12: .p12PKCS#12 / .pfx / .p12", $certificateOutPfx, !$options->noOut);

                if(!empty($options->targetPath)) {
                    openssl_x509_export_to_file($certificate,$options->targetPath . DIRECTORY_SEPARATOR . "certificate" . $options->suffix . ".pem", $options->privateKeyPassword);
                    openssl_pkcs12_export_to_file($certificate,$options->targetPath . DIRECTORY_SEPARATOR . "certificate" . $options->suffix . ".pfx", $pkey, $options->privateKeyPassword);
                }
                echo "\nGenerator finished successfully. Cheers 🍻".PHP_EOL;
            }
        }

        $result['private.key'] = $pKeyOut;
        $result['certificate-signing-request.csr'] = $csrOut;
        $result['certificate.crt'] = $certificateOut;
        $result['certificate.pfx'] = $certificateOutPfx;

        return $result;
    }


    public function httpsHash($options,$draft,$validationPath){      //CertificateValidationType::HTTPS_CSR_HASH


            $remoteFileInfo = "";
            foreach($draft["validation"]["other_methods"] as $domain => $info) {
                if(!array_key_exists("file_validation_url_https",$info)) throw new ConfigurationException("HTTPS file upload validation is not possible for this certificate.");
                $currentInfo = $info["file_validation_url_https"] . "\n\n". implode("\n",$info["file_validation_content"])."\n\n";
                $tmp = explode("/",$info["file_validation_url_https"]);
                $fileName = end($tmp);
                file_put_contents($validationPath . DIRECTORY_SEPARATOR . $domain . "-validate-" . $options->suffix . "-" . $fileName  . ".txt", $currentInfo);
                $remoteFileInfo .= $currentInfo;
            }
            self::dumpGeneratedContent("FILES",$remoteFileInfo, !$options->noOut);


    }




    public function csrHash($options,$draft){      //CertificateValidatioCNAME_CSR_HASHnType::CNAME_CSR_HASH

            echo "\nPlease make sure the following CNAME entries exist:\n";
            $cnameInfo = "";
            foreach($draft["validation"]["other_methods"] as $domain => $info) {
                if(!array_key_exists("cname_validation_p1",$info)) {
                    throw new ConfigurationException("CNAME validation is not possible for this certificate.");
                }
                $cnameInfo .= "\nName: " . $info["cname_validation_p1"] . "\nValue: " . $info["cname_validation_p2"].PHP_EOL;
            }
            self::dumpGeneratedContent("CNAME ENTRIES",$cnameInfo, !$options->noOut);

    }









    /**
     * @param string $hash
     * @param Options $options
     * @param bool $repeat
     * @return array|null
     * @throws JsonException
     * @throws RemoteRequestException
     */
    public static function zeroSign(string $hash,Options $options, bool $repeat = false): ?array{
        $requester = new ApiEndpointRequester($options->debug);                                                                    // lets the certificate get signed

        $pending = $requester->requestJson($requester->apiEndpointInfo->verify_domains, ["API_URL" => $requester->apiUrl, "ACCESS_KEY" => $options->apiKey, "CERT_HASH" => $hash], ["validation_method" => $options->validationType->value, "validation_email" => ($options->validationType === CertificateValidationType::EMAIL) ? implode(",",$options->validationEmail) : null],!empty($options->debug));

        return  ( ($pending["status"]??'') === "pending_validation") ?static::zeroSignPendingvalidationDownload($requester, $hash, $options):static::zeroSignFail($hash,$options,$pending,  $repeat );

    }


    public static function zeroSignPendingvalidationDownload(ApiEndpointRequester $requester,string $hash,Options $options){
        echo "pending_validation ...".PHP_EOL;
        if($options->validationType === CertificateValidationType::EMAIL) echo "The script is waiting for you to confirm the emails ...".PHP_EOL;


        $counter = 0;
        while(true) {
            sleep(self::ISSUANCE_SLEEP_SECONDS);
            $counter++;
            echo "🗎";

            if($counter % self::ISSUANCE_CHECK_SECONDS !== 0) continue;
            $info = $requester->requestJson($requester->apiEndpointInfo->get_certificate,["API_URL" => $requester->apiUrl, "ACCESS_KEY" => $options->apiKey, "CERT_HASH" => $hash],[],!empty($options->debug));
            if(!empty($info["id"])) break;

            if ( CertificateStatus::tryFrom($info["status"]??'') ==CertificateStatus::ISSUED) return       static::zeroSignIssued($requester,$hash,$options);


        }
            return null;
    }





    public static function zeroSignIssued(ApiEndpointRequester $requester,string $hash,Options $options){
        $certificateActual = $requester->requestJson($requester->apiEndpointInfo->download_certificate_json,["API_URL" => $requester->apiUrl, "ACCESS_KEY" => $options->apiKey, "CERT_HASH" => $hash, "INCLUDE_CROSS_SIGNED" => $options->includeCrossSigned],[],!empty($options->debug));
        if(!empty($certificateActual["certificate.crt"]))    return null;

        self::dumpGeneratedContent("CERTIFICATE", $certificateActual["certificate.crt"], !$options->noOut);
        self::dumpGeneratedContent("CA BUNDLE", $certificateActual["ca_bundle.crt"], !$options->noOut);
        file_put_contents($options->sslDirDomain  . $options->certificateCrt,$certificateActual["certificate.crt"]);      // integrate generator: self::dumpGeneratedContent("PKCS#12: .p12PKCS#12 / .pfx / .p12",$certificateActual["ca_bundle.crt"]);
        file_put_contents($options->sslDirDomain  .$options->caBundleCrt,$certificateActual["ca_bundle.crt"]);
        file_put_contents($options->fhash. "hash.txt",$hash,FILE_APPEND);
        echo "\nGenerator finished successfully. Cheers 🍻.".PHP_EOL;
        return $certificateActual;

    }




    public static function zeroSignFail(string $hash,Options $options,$pending, bool $repeat = false){

        echo "\nValidation failed or can not be processed at the moment 🧐 Here is the answer:\n";
        self::dumpGeneratedContent("ANSWER FROM ZEROSSL",print_r($pending,true),true);

        if($options->validationType === CertificateValidationType::EMAIL) {
            echo "\nERROR: Your validation emails must match the correct validation email string. Please re-verify with different string.\n";
            return null;
        }

        else if($repeat) {
            $count = 0;
            while($count < self::ISSUANCE_CHECK_SECONDS) {
                echo "🗎";
                sleep(1);
                $count++;
            }
            self::zeroSign($hash,$options,$repeat);
        }
        return null;

    }





    /**
     * @param Options $options
     * @return void
     * @throws Exception
     */
    public static function process(Options $options): void{
        if($options->noOut && !$options->apiKey  && !is_dir(realpath($options->targetPath))) throw new ConfigurationException("If the no output option is specified, you have to pass a valid target path for self-signed certificates.");


        if(!empty($options->targetPath) && !is_dir(realpath($options->targetPath)))     throw new ConfigurationException("The target path \"" . $options->targetPath . "\" appears not to be a valid path.");




        if(!$options->noOut) echo "Generator started.".PHP_EOL;

        self::generate($options,true);

        if(!$options->noOut) {
            echo "\nAll done. Script exiting ".PHP_EOL;
            exit();
        }
        exit();
    }








    /**
     * @param string $label
     * @param string $content
     * @param bool $printInfo
     * @return void
     */
    public static function dumpGeneratedContent(string $label, string $content, bool $printInfo): void{
        if($printInfo) echo "\n\n### " . $label . " ###\n\n" . $content . "\n\n### END: " . $label . " ###\n\n";

    }
}


/*if(!empty($options->targetSubfolder)) {
    $newTargetPath = $options->targetPath . DIRECTORY_SEPARATOR . $options->targetSubfolder;
    if(!is_dir($newTargetPath) && !mkdir($newTargetPath) && !is_dir($newTargetPath)) {throw new ConfigurationException(sprintf('Creation of output directory "%s" was not possible. Permission problem?', $newTargetPath));}
    $options->targetPath = $newTargetPath;
}*/
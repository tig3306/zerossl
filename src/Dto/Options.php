<?php

namespace ZeroSSL\CliClient\Dto;

use ZeroSSL\CliClient\Enum\CertificateValidationType;

class Options{
    public array $domains;
    public string $domain;
    public string $privateKeyPassword;
    public bool $noOut;
    public ?string $targetPath;
    public ?string $targetSubfolder;
    public string $suffix;
    public ?string $apiKey;
    public ?CertificateValidationType $validationType;
    public array $validationEmail;
    public bool $csrOnly;
    public bool $createOnly=false;
    public bool $useEccDefaults;
    public array $privateKeyOptions;
    public array $csrData;
    public array $csrOptions;
    public int $validityDays;
    public bool $includeCrossSigned;
    public string $debug = "";

    public string $fhash = "/www/wwwroot/ssl/hash.txt";
    public string $sslDir = "/www/wwwroot/ssl/";
    public string $sslDirDomain ;
    public string $caBundleCrt="ca_bundle.crt";
    public string $privateKey= "private_me.key";
    public string $csrPem= "csr.pem";
    public string $certificateCrt="certificate.crt";

}

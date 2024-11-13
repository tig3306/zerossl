<?php

require getcwd() . '/vendor/autoload.php';

// just in case remote calls take a bit longer
use ZeroSSL\CliClient\Dto\Options;
use ZeroSSL\CliClient\Enum\CertificateValidationType;
use ZeroSSL\CliClient\Enum\InputType;
use ZeroSSL\CliClient\InputSanitizer;
use ZeroSSL\CliClient\RequestProcessor;

ini_set('max_execution_time', 1200);
set_time_limit(1200);

// set CLI options
$short_options = "d:p::nt::a::s::k::m::ey::orc::s::v::ziq::";
$long_options = [
    "domains:",
    "privateKeyPassword::",
    "noOut",
    "targetPath::",
    "targetSubfolder::",
    "suffix::",
    "apiKey::",
    "validationMethod::",
    "useEccDefaults",
    "privateKeyOptions::",
    "csrOnly",
    "createOnly",
    "csrData::",
    "csrOptions::",
    "validityDays::",
    "validationEmail::",
    "includeCrossSigned",
    "debug::"
];
$options = getopt($short_options, $long_options);

$pOptions = new Options();
$pOptions->domains = InputSanitizer::getCliArgument("d", "domains", $options, [], InputType::DOMAINS);
$pOptions->domain =current($pOptions->domains);
$pOptions->sslDirDomain =$pOptions->sslDir.$pOptions->domain;
if (!is_dir($pOptions->sslDir))    mkdir($pOptions->sslDir, 0755,true);
if (!is_dir($pOptions->sslDirDomain))    mkdir($options->$pOptions, 0755,true);
$pOptions->privateKeyPassword = InputSanitizer::getCliArgument("p", "privateKeyPassword", $options, "",InputType::STRING);
$pOptions->noOut = InputSanitizer::getCliArgument("n", "noOut", $options, false,InputType::BOOL);
$pOptions->targetPath = InputSanitizer::getCliArgument("t", "targetPath", $options, "",InputType::STRING);
$pOptions->targetSubfolder = InputSanitizer::getCliArgument("a", "targetSubfolder", $options, "",InputType::STRING);
$pOptions->suffix = InputSanitizer::getCliArgument("s", "suffix", $options, "",InputType::STRING);
$pOptions->apiKey = InputSanitizer::getCliArgument("k", "apiKey", $options, "",InputType::STRING);
$pOptions->validationType = CertificateValidationType::tryFrom(InputSanitizer::getCliArgument("m", "validationMethod", $options, "HTTP_CSR_HASH",InputType::STRING));
$pOptions->useEccDefaults = InputSanitizer::getCliArgument("d", "useEccDefaults", $options, false,InputType::BOOL);
$pOptions->privateKeyOptions = InputSanitizer::getCliArgument("y", "privateKeyOptions", $options, [],InputType::QUERY_STRING, true);
$pOptions->csrOnly = InputSanitizer::getCliArgument("o", "csrOnly", $options, false,InputType::BOOL);
$pOptions->createOnly = InputSanitizer::getCliArgument("r", "createOnly", $options, false,InputType::BOOL);
$pOptions->csrData = InputSanitizer::getCliArgument("c", "csrData", $options, [],InputType::QUERY_STRING, false);
$pOptions->csrOptions = InputSanitizer::getCliArgument("s", "csrOptions", $options, [],InputType::QUERY_STRING, true);
$pOptions->validityDays = InputSanitizer::getCliArgument("v", "validityDays", $options, 90,InputType::INT);
$pOptions->validationEmail = InputSanitizer::getCliArgument("z", "validationEmail", $options, "",InputType::VALIDATION_EMAIL);
$pOptions->includeCrossSigned = InputSanitizer::getCliArgument("i", "includeCrossSigned", $options, false,InputType::BOOL);
$pOptions->debug = InputSanitizer::getCliArgument("q", "debug", $options, false,InputType::STRING);

RequestProcessor::process($pOptions);

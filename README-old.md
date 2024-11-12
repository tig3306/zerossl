```shell
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
git clone git@github.com:tig3306/zerossl.git
cd zerossl
composer update -vvv
echo yes|composer install -vvv
D="47.236.97.31"
php generator.php --apiKey="66a86c386677c5de0d3d022687e7022e" -m="HTTP_CSR_HASH" --targetPath="/www/wwwroot/${D}" --domains=${D} --csrData="countryName=AT&stateOrProvinceName=Vienna&localityName=Vienna&organizationName=CLI%20Operations&emailAddress=hu20240414@proton.me"
php downloader.php --hash="CERTIFICATE_HASH" --apiKey="66a86c386677c5de0d3d022687e7022e" --formats=der --targetPath="/var/www/ssl"
```











## Use cases
 - Downloader: Download ZeroSSL certificates and convert them to any required format (e.g. .der, .pem, .p12, .pfx)
 - Generator: Automate the creation of a huge amount of certificates with the ZeroSSL API (can be used as a library / standalone toolkit)
 - Generator: Everything except for certificate creation and domain validation will happen in your local system
 - Generator: Can be used as a free tool for CSR creation
 - Generator: Can be used as a free tool for self-signed certificate creation

## How to get an API key for creating valid SSL certificates signed by ZeroSSL

1. Go to: https://zerossl.com/
2. Create a free account
3. Within your dashboard navigate to developer and get your API key
4. Now you can create valid SSL certificates with ZeroSSL

## Requirements

 - PHP 8.1 or higher
 - PHP OpenSSL extension (https://www.php.net/manual/en/book.openssl.php)
 - PHP CURL extension (https://www.php.net/manual/de/book.curl.php)
 - Optional: Composer (PHP dependency manager, only needed for development, used for autoloading, https://getcomposer.org/)

## Components

 - [Generator](./README-generator.md): Get a signed SSL certificate in the command line from scratch in less than a minute
 - [Downloader](./README-downloader.md): Download ZeroSSL certificates in any format which might be required
 - [CSR Tool](./README-csrtool.md): Advanced tool for CSR generation (under construction)
 - [Certificate Converter](./README-converter.md): Certificate format conversion tool (under construction)

## Errors

The script will display native PHP errors with the stack trace in case something goes wrong. In order
to understand what is not working check the first line of the error output. Most likely
you will have a `ConfigurationException`, which means you have to adjust your input.

## Usage as a library

Note: This is not yet completely ready, be free to make MRs for improvement.

### Including in custom PHP scripts

Create an options object: `$options = new ZeroSSL\CliClient\Dto\Options()`

Run: `$certificateData = RequestProcessor::generate($options,true);`

Have a look at the RequestProcessor objects for the functions you need to achieve what you actually want.
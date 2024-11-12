###ip
```shell
cd zerossl
cat <<EOF>ip.txt
103.24.207.1
103.24.207.2
103.24.207.3
103.24.207.4
103.24.207.5
103.24.207.6
103.24.207.7
103.24.207.8
103.24.207.9
103.24.207.10
103.24.207.11
103.24.207.12
103.24.207.13
103.24.207.30
103.24.207.31
103.24.207.32
103.24.207.33
103.24.207.34
103.24.207.35
103.24.207.36
103.24.207.37
103.24.207.38
103.24.207.39
103.24.207.40
103.24.207.41
103.24.207.42
103.24.207.43
103.24.207.45
103.24.207.46
103.24.207.47
103.24.207.49
103.24.207.50
EOF
cat ip.txt
```

```shell
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer
git clone https://github.com/tig3306/zerossl.git                                        #git clone git@github.com:tig3306/zerossl.git
cd zerossl
composer update -vvv
echo yes|composer install -vvv

apt install screen -y
screen -S ssl
ds=(
"103.24.207.1"
"103.24.207.2"
"103.24.207.3"
"103.24.207.4"
"103.24.207.5"
"103.24.207.6"
"103.24.207.7"
"103.24.207.8"
"103.24.207.9"
"103.24.207.10"
"103.24.207.11"
"103.24.207.12"
"103.24.207.13"
"103.24.207.30"
"103.24.207.31"
"103.24.207.32"
"103.24.207.33"
"103.24.207.34"
"103.24.207.35"
"103.24.207.36"
"103.24.207.37"
"103.24.207.38"
"103.24.207.39"
"103.24.207.40"
"103.24.207.41"
"103.24.207.42"
"103.24.207.43"
"103.24.207.45"
"103.24.207.46"
"103.24.207.47"
"103.24.207.49"
"103.24.207.50"
)
for D in "${ds[@]}"
do
    php generator.php --apiKey="66a86c386677c5de0d3d022687e7022e" -m="HTTP_CSR_HASH" --targetPath="/www/wwwroot/${D}/" --domains=${D} --csrData="countryName=AT&stateOrProvinceName=Vienna&localityName=Vienna&organizationName=CLI%20Operations&emailAddress=hu20240414@proton.me"
    echo ${D}
done
D="103.24.7.3"
php generator.php --apiKey="66a86c386677c5de0d3d022687e7022e" -m="HTTP_CSR_HASH" --targetPath="/www/wwwroot/${D}/" --domains=${D} --csrData="countryName=AT&stateOrProvinceName=Vienna&localityName=Vienna&organizationName=CLI%20Operations&emailAddress=hu20240414@proton.me"
   

```




###down
```shell
php downloader.php --hash="CERTIFICATE_HASH" --apiKey="66a86c386677c5de0d3d022687e7022e" --formats=der --targetPath="/var/www/ssl"
```



```shell
php apk.php
git pull origin main
```
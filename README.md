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





```




###down
```shell
php downloader.php --hash="CERTIFICATE_HASH" --apiKey="66a86c386677c5de0d3d022687e7022e" --formats=der --targetPath="/var/www/ssl"
```
<?php
class link{
    public static $src= '/www/wwwroot/io/';
    public static function readIp($fip){


        $file = fopen($fip, 'r');

        if (empty($file))  {
            echo "Error opening the file.";
            exit();
        }
        while (($line = fgets($file)) !== false) {
           self::createSoftLink(self::$src,"/www/wwwroot/{$line}/io");
        }
        fclose($file);

    }

   public static function createSoftLink($src, $target){


// 创建目录
       if (!is_dir($src)) {
           mkdir($src, 0755, true);
           echo "dir create success".PHP_EOL;
       }

// 创建软链接
       if (!is_link($target)) {
           symlink($src, $target);                                                                      //symlink("/home/kali/Desktop/webview-yde/","/home/kali/Desktop/test");

           echo "soft link success".PHP_EOL;
           return ;
       }

           echo "link exitS".PHP_EOL;

   }


}





link::readIp("ip.txt");
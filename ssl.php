<?php
class ssl{
    public static $src= '/www/wwwroot/io/';
    public static function readIp($fip){


        $file = fopen($fip, 'r');

        if (empty($file))  {
            echo "Error opening the file.";
            exit();
        }
        while (($line = fgets($file)) !== false) {
            try {
                if (!empty($line)) $line=trim($line);
                /www/server/panel/vhost/rewrite/103.24.207.11.conf
            }catch (Exception $e){
                echo $e->getMessage();
            }
        }
        fclose($file);

    }

    public static function createSoftLink($src, $target){

        if (!is_dir($src)) {
            mkdir($src, 0755, true);
            echo "dir create success".PHP_EOL;
        }
// 创建目录
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
            echo "dir create success".PHP_EOL;
        }

        // 创建软链接
        if (is_link($target)) return;
        symlink($target,$src);                                                                      //symlink("/home/kali/Desktop/webview-yde/","/home/kali/Desktop/test");

        echo "soft link success".PHP_EOL;




    }



}



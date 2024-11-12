<?php
class del{

    public static function readIp($fip){


        $file = fopen($fip, 'r');

        if (empty($file))  {
            echo "Error opening the file.";
            exit();
        }
        while (($line = fgets($file)) !== false) {
            try {
              $dir="/www/wwwroot/{$line}";
            if (!is_dir($dir)) continue;
            echo ((!rmdir($dir) ? "del dir succcess ": "del dir fail").$dir);

            }catch (Exception $e){
                echo $e->getMessage();
            }
        }
        fclose($file);

    }




}





apk::readIp("ip.txt");                                                                                  //php apk.php


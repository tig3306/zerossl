<?php
class del{

    public static function main($fip){


        $file = fopen($fip, 'r');

        if (empty($file))  {
            echo "Error opening the file.";
            exit();
        }
        while (($line = fgets($file)) !== false) {
            try {
              $dir="/www/wwwroot/{$line}/io";
            if (!is_dir($dir)) continue;
            echo ((!rmdir($dir) ? "del dir succcess ": "del dir fail").$dir);

            }catch (Exception $e){
                echo $e->getMessage();
            }
        }
        fclose($file);

    }




}





del::main("ip.txt");                                                                                  //php apk.php


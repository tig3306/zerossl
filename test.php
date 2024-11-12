<?php
$ds=[
    "103.24.207.3",
    "103.24.207.4"
];
foreach ($ds as $D){
    try {

        var_dump($D);

        sleep(mt_rand(120, 150));
    }catch (Exception $e){
        echo $e->getMessage();
    }
}



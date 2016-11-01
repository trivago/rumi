<?php

function inArray($array1, $array2) {
    $finalArray = Array();

    foreach ($array2 as $r) {
        foreach ($array1 as $string) {

            if(preg_match('/'. $string . '/', $r)){

            }
        }

    }


}

$a2 = ["lively", "alive", "harp", "sharp", "armstrong"];
$a1 = ["arp", "live", "strong"];
inArray($a1, $a2);

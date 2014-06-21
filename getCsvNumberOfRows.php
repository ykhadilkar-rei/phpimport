<?php
/**
 * Utility to count number of rows.
 * User: ykhadilkar
 * Date: 6/20/14
 * Time: 11:31 AM
 */

$dir = $argv[1];

// start at -1 number of rows excluding header row. 
$i = -1;
if ($handle = opendir($dir)) {
    echo "Start counting .. .\n";
    while (($file = readdir($handle)) !== false){
        if (endsWith($file,".csv") && !is_dir($dir.$file)){
            $i++;
            //count rows
            $fh = fopen($dir."/".$file,'rb') or die("ERROR OPENING DATA \n");
            $linecount=0;
            while (fgets($fh) !== false) {
                $linecount++;
            }
            fclose($fh);
            echo "Filename: ".$file.", Number of rows: ".$linecount."\n";
        }
    }
}
// prints out how many were in the directory
echo "There are $i CSV files in ".$dir."\n";

function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}
function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

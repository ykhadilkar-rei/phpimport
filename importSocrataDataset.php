<?php
/**
 * Created by PhpStorm.
 * User: ykhadilkar
 * Date: 6/6/14
 * Time: 10:33 AM
 */
require_once('inc/add_dataset.php');
require_once('inc/ckan_map.php');
require_once('inc/curl_http_request.php');
require_once('inc/get_map.php');
require_once('inc/functions.php');

//make sure all args are provided
if (count($argv) < 2) {
    die("Usage: php import.php (datasetFile:)path-To-Datasets-Info-CSV-file\n");
}
$env = $argv[1];
$csvFilePath = $argv[2];

//parse CSV file and datset urls
$dataset_meta_Data = parse_csv_file($csvFilePath);

//number of records
$number_of_datasets = count($dataset_meta_Data);


//test file download
//download_data('https://explore.data.gov/api/views/4dkz-64bn/rows.csv?accessType=DOWNLOAD');

//parse ini file and put all info into $server array
$ini_array = parse_ini_file("ini/server.ini", true);

if (!isset($ini_array[$env])) {
    die("Could not find server info for $env.\n");
}

$server = array(
    "source_type" => $ini_array['source_type'],
    "url_src" => $ini_array['url_src'],
    "url_dest" => $ini_array[$env]['url_dest'],
    "api" => $ini_array[$env]['api'],
    "auth" => array(
        'user' => $ini_array[$env]['auth_user'],
        'password' => $ini_array[$env]['auth_pass'],
    ),
    "org" => $org,
    "ckan_use_src_org" => $ini_array['ckan_use_src_org'],
    "ckan_src_org_map" => $ini_array['ckan_src_org_map'],
    "pagination_rows" => $ini_array['pagination_rows'] ? $ini_array['pagination_rows'] : 100,
    "pagination_start" => 0,
    "data_folder_path" => $ini_array['data_folder_path'],
);

//get mapping for source type
$map = get_map($server['source_type']);
if (empty($map)) {
    die("Could not find map for {$server['source_type']}.\n");
}

$file = 'dataset_names_'.time().'.txt';
//post datasets into desination server
switch ($server['source_type']) {

    case 'socratajson':
        $count = 1;
        for ($i = 0; $i < $number_of_datasets; $i++) {
            $current_ds = $dataset_meta_Data[$i];
            $server["url_src"] = $current_ds['MetaData Download URL'];

            // get source data in json
            $ret = curl_http_request($server);

            //org
            $remove_char = array(" ","(", ")", ":",".");
            $server["org"] = strtolower(str_replace($remove_char,"-",$current_ds['Agency']));

            if (empty($ret)) {
                die("No datasets fetched.");
            }else if (empty($ret['id'])){
                echo('Can not add dataset ' . $count . '/' . $number_of_datasets . ': ' . $current_ds['Name'] . ".\n");
            }else{
                //add resource in distribution
                $ret['distribution'][0]['upload'] = $server["data_folder_path"].$current_ds['File Name'];
                $ret['distribution'][0]['format'] = 'CSV';
                $ret['distribution'][0]['name'] = $current_ds['File Name'];

                $dataset = add_dataset($server, $map, $ret, $current_ds);

                //sample resource url - http://qa-datastore-fe-data.reisys.com/dataset/db1a3a30-41ee-46a1-b9d7-a2bfb8ca0943/resource/4b2ccc4d-706d-4516-b203-bd99e5cb5ff8

                $output = 'Added ' . $count . '/' . $number_of_datasets . ': ' . $dataset['name'] . ", ";
                $output .= '/dataset/'.$dataset['id'].'/resource/'.$dataset['resources'][0]['id']."\n";
                echo($output);

                file_put_contents($file, $output, FILE_APPEND | LOCK_EX);
            }
            $count = $count + 1;
        }
        break;

    default:
    //
}



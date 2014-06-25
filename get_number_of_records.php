<?php
/**
 * Created by PhpStorm.
 * User: ykhadilkar
 * Date: 6/23/14
 * Time: 1:15 PM
 */
require_once('inc/functions.php');
require_once('inc/curl_http_request.php');

//Search dataset by row and get dataset id

$env = $argv[1];
$csvFilePath = $argv[2];

//parse CSV file and datset urls
$dataset_meta_Data = parse_csv_file($csvFilePath);

//number of records
$number_of_datasets = count($dataset_meta_Data);

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

//post datasets into desination server
switch ($server['source_type']) {
    case 'socratajson':
        $count = 1;
        echo("Dataset Name, Number of records, CSV Rows, Filename\n");
        for ($i = 0; $i < $number_of_datasets; $i++) {
            $current_ds = $dataset_meta_Data[$i];
            $server["url_src"] = $current_ds['MetaData Download URL'];

            // get source data in json
            $dataset_meta_details = curl_http_request($server);

            if (empty($dataset_meta_details)) {
                die("No datasets fetched.");
            }else if (empty($dataset_meta_details['id'])){
                echo('Can not add dataset ' . $count . '/' . $number_of_datasets . ': ' . $current_ds['Name'] . ".\n");
            }

            //Step 1: search resource
            $search_title = rawurlencode($current_ds['File Name']);

            //create url
            $src_url = $server["url_dest"];
            $src_url .=  "api/action/resource_search?query=name:".$search_title;
            $resource_response = http_get_request($src_url);

            //Step 2: Get datastore details
            //get resource id from search result.
            $resource_id = $resource_response['result']['results'][0]['id'];
            $src_url = $server["url_dest"];
            $src_url .=  "api/action/datastore_search?resource_id=".$resource_id;
            $datastore_response = http_get_request($src_url);
            $number_of_records_in_datastore = $datastore_response['result']['total'];

            //count number if rows in a CSV file
            $number_of_rows = count_number_of_csv_rows($server["data_folder_path"].$current_ds['File Name']);

            $remove_char = array(",");
            echo(str_replace($remove_char," ",$current_ds['Name']).","
                .$number_of_records_in_datastore.","
                .$number_of_rows.","
                .str_replace($remove_char," ",$current_ds['File Name'])."\n");

            //http://uat-datastore-fe-data.reisys.com/api/3/action/datastore_search?resource_id=87cdade2-3bb5-47e3-8a52-f1c457f20230
            $count = $count + 1;
        }
        break;

    default:
        //
}

function count_number_of_csv_rows($file_address){
    //count rows
    $fh = fopen($file_address,'rb') or die("ERROR OPENING DATA \n");
    $linecount=0;
    while (fgets($fh) !== false) {
        $linecount++;
    }
    fclose($fh);
    return $linecount-1;
}

function http_get_request($search_url){
    $ch = curl_init($search_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if (!empty($server['auth']) && !empty($server['auth']['user'])) {
        curl_setopt($ch, CURLOPT_USERPWD, $server['auth']['user'] . ":" . $server['auth']['password']);
    }
    $json_response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($json_response, true);
    return $response;
}

//Store number of records in text file.

//Compare with CSV spit out error files.

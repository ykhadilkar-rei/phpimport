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

//if (!isset($ini_array[$env])) {
//    die("Could not find server info for $env.\n");
//}

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
            $server["org"] = $current_ds['Agency'];

            if (empty($ret)) {
                die("No datasets fetched.");
            }else if (empty($ret['id'])){
                echo('Can not add dataset ' . $count . '/' . $number_of_datasets . ': ' . $current_ds['Name'] . ".\n");
            }else{
                //add resource in distribution
                $ret['distribution'][0]['upload'] = $server["data_folder_path"].$current_ds['File Name'];
                $ret['distribution'][0]['format'] = 'text/CSV';
                $ret['distribution'][0]['name'] = $current_ds['Name'];

                $dataset_name = add_dataset($server, $map, $ret);
                echo('Added ' . $count . '/' . $number_of_datasets . ': ' . $dataset_name . ".\n");
            }
            $count = $count + 1;
        }
        break;

    default:
    //
}
//data pusher
//post here /dataset/achievement-results-for-state-assessments-in-mathematics-school-year-2010-11-1402692841/resource_data/444b624b-d84d-4fc4-81bf-c74e8c850a09


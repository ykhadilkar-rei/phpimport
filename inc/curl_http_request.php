<?php

function curl_http_request($server, $json_query = "", $action = 'package')
{

    //use $json_query as indicator whether it is a GET request to fetch date
    //or a POST request to write data.
    $b_fetch_not_post = empty($json_query);
    $b_tbd = false;

    if ($b_fetch_not_post) { // fetch data
        $src_url = $server["url_src"];
        if ($server['source_type'] == 'ckan') {
            $src_url .= "&rows={$server['pagination_rows']}&start={$server['pagination_start']}";
        }
        $ch = curl_init($src_url);
    } elseif (is_array($json_query) && $json_query['purpose'] == 'latest') { // check lastest on destination server
        //todo: $json_query has been re-purposed.
        $b_tbd = true;
        $ch = curl_init($server["url_dest"] . "api/action/package_search?q=organization:" . $json_query['data']['owner_org'] . "&sort=metadata_modified%20desc&rows=1");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-CKAN-API-Key: ' . $server['api'],
        ));
    } elseif (is_array($json_query) && $json_query['purpose'] == '2ndmap') {
        $b_tbd = true;
        $ch = curl_init($server["url_dest"] . "api/action/package_show?id=" . $json_query['data']['name']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-CKAN-API-Key: ' . $server['api'],
        ));
    } else { // Post data
        if ($action == '2ndmap') {
            $url = $server["url_dest"] . "api/action/package_update";
        } else {
            $url = $server["url_dest"] . "api/action/" . $action . "_create";
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        if (property_exists(json_decode($json_query), "upload")) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_decode($json_query));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-CKAN-API-Key: ' . $server['api'],
            ));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_query);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Length: ' . strlen($json_query),
                'Content-Type: application/json;charset=utf-8',
                'X-CKAN-API-Key: ' . $server['api'],
            ));
        }
    }

    curl_setopt($ch, CURLOPT_COOKIE, "auth_tkt=abc");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (!empty($server['auth']) && !empty($server['auth']['user'])) {
        curl_setopt($ch, CURLOPT_USERPWD, $server['auth']['user'] . ":" . $server['auth']['password']);
    }

    $response = curl_exec($ch);

    curl_close($ch);

    if ($b_fetch_not_post || $b_tbd) {
        //remove weird chars
        $response = utf8_encode($response);
        $ret = json_decode($response, true);
    } else {
        $ret = json_decode($response, true);

        if (empty($ret)) {
            $ret = $response;
        } elseif (empty($ret['success'])) {
            $ret = $ret['error'];
        } else {
            $ret = $ret['result'];
        }


        //if resource upload push it to datastore
        //data pusher
        //post here /dataset/achievement-results-for-state-assessments-in-mathematics-school-year-2010-11-1402692841/resource_data/444b624b-d84d-4fc4-81bf-c74e8c850a09
    }

    return $ret;
}
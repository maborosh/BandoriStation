<?php

function data_source_info($source, $function)
{
    $data_source_array = array(
        'source_name' => array(
            'type' => 'type',
            'token' => 'token'
        )
    );
    if ($function == 'check_data_source') {
        if (array_key_exists($source, $data_source_array)) {
            return true;
        } else {
            return false;
        }
    } elseif ($function == 'get_source_type') {
        return $data_source_array[$source]['type'];
    } elseif ($function == 'get_source_token') {
        return $data_source_array[$source]['token'];
    } else {
        return '';
    }
}

function micro_second_time()
{
    $time_array = explode(' ', microtime());
    return $time_array[1] * 1000  + intval($time_array[0] * 1000);
}

function get_online_number()
{
    $redis = new Redis();
    $redis->connect('127.0.0.1');
    $redis_key = 'bandori_station_online_number';
    return array(
        'status' => 'success',
        'response' => array(
            'online_number' => $redis->get($redis_key)
        )
    );
}
<?php

function query_room_number($latest_time = null)
{
    if ($latest_time and !is_numeric($latest_time)) {
        return array(
            'status' => 'failure',
            'response' => 'illegal_timestamp_format'
        );
    }

    $redis = new Redis();
    $redis->connect('127.0.0.1');
    $timestamp = micro_second_time();
    $response_room_number_list = array();
    $redis_key = 'bandori_room_number';
    $room_number_list = $redis->lRange($redis_key, 0, -1);
    for ($i = 0; $i < count($room_number_list); $i++) {
        $room_number_array = json_decode($room_number_list[$i], true);
        if ($timestamp - $room_number_array['time'] <= 120000) {
            if ($latest_time) {
                if ($room_number_array['time'] > $latest_time) {
                    $response_room_number_list[] = $room_number_array;
                }
            } else {
                $response_room_number_list[] = $room_number_array;
            }
        } else {
            $redis->lPop($redis_key);
        }
    }

    return array(
        'status' => 'success',
        'response' => $response_room_number_list
    );
}
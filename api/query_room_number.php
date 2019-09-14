<?php

function query_room_number($latest_time = null)
{
    if (!$latest_time) {
        if (isset($_GET['latest_time'])) {
            $latest_time = $_GET['latest_time'];
        }
    }

    $redis = new Redis();
    $redis->connect('127.0.0.1');
    $timestamp = micro_second_time();
    $room_number_set = array();
    $redis_key = 'bandori_room_number';
    $room_number_list = $redis->lRange($redis_key, 0, -1);
    for ($i = 0; $i < count($room_number_list); $i++) {
        $room_number_array = json_decode($room_number_list[$i], true);
        $duration = $timestamp - $room_number_array['time'];
        if ($duration <= 120000) {
            if ($latest_time) {
                if ($room_number_array['time'] > $latest_time) {
                    set_username($room_number_array);
                    $room_number_set[] = $room_number_array;
                }
            } else {
                set_username($room_number_array);
                $room_number_set[] = $room_number_array;
            }
        } else {
            $redis->lPop($redis_key);
        }
    }

    return $room_number_set;
}

function set_username(&$room_number_array)
{
    if (data_source_check($room_number_array['source'], 1)) {
        if (strlen($room_number_array['user_id']) == 5) {
            $room_number_array['username'] = '用户' . substr($room_number_array['user_id'], 0, 1) . '***' . substr($room_number_array['user_id'], 4);
        } elseif (strlen($room_number_array['user_id']) == 6) {
            $room_number_array['username'] = '用户' . substr($room_number_array['user_id'], 0, 1) . '****' . substr($room_number_array['user_id'], 5);
        } elseif (strlen($room_number_array['user_id']) == 7) {
            $room_number_array['username'] = '用户' . substr($room_number_array['user_id'], 0, 2) . '****' . substr($room_number_array['user_id'], 6);
        } else {
            $room_number_array['username'] = '用户' . substr($room_number_array['user_id'], 0, 3) . '****' . substr($room_number_array['user_id'], 7);
        }
    }
}
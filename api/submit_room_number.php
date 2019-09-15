<?php

function submit_room_number()
{
    $room_number_array = array();
    if (isset($_GET['number'])) {
        if ((strlen($_GET['number']) == 5 or strlen($_GET['number']) == 6) and is_numeric($_GET['number'])) {
            $room_number_array['number'] = $_GET['number'];
        } else {
            return array(
                'status' => 'failure',
                'response' => 'illegal_number_format'
            );
        }
    } else {
        return array(
            'status' => 'failure',
            'response' => 'lack_number_parameter'
        );
    }

    if (isset($_GET['user_id'])) {
        if (is_numeric($_GET['user_id'])) {
            $room_number_array['user_id'] = $_GET['user_id'];
        } else {
            return array(
                'status' => 'failure',
                'response' => 'illegal_user_id_formal'
            );
        }
    } else {
        return array(
            'status' => 'failure',
            'response' => 'lack_user_id_parameter'
        );
    }

    if (isset($_GET['raw_message'])) {
        $room_number_array['raw_message'] = urldecode(trim($_GET['raw_message']));
    } else {
        return array(
            'status' => 'failure',
            'response' => 'lack_raw_message_parameter'
        );
    }

    if (isset($_GET['source'])) {
        if (data_source_check($_GET['source'], 1)) {
            $room_number_array['source'] = $_GET['source'];
        } else {
            return array(
                'status' => 'failure',
                'response' => 'unregistered_data_source'
            );
        }
    } else {
        return array(
            'status' => 'failure',
            'response' => 'lack_source_parameter'
        );
    }

    if (isset($_GET['token'])) {
        if (!data_source_check($_GET['source'], 2, $_GET['token'])) {
            return array(
                'status' => 'failure',
                'response' => 'invalid_token'
            );
        }
    } else {
        return array(
            'status' => 'failure',
            'response' => 'lack_token_parameter'
        );
    }

    if (isset($_GET['type'])) {
        if ($_GET['type'] == 25) {
            $room_number_array['type'] = '25万房';
        } elseif ($_GET['type'] == 18) {
            $room_number_array['type'] = '18万大师房';
        } elseif ($_GET['type'] == 12) {
            $room_number_array['type'] = '12万高手房';
        } elseif ($_GET['type'] == 7) {
            $room_number_array['type'] = '7万常规房';
        } else {
            return array(
                'status' => 'failure',
                'response' => 'undefined_type_value'
            );
        }
    } else {
        if (
            stristr($room_number_array['raw_message'], '25w') or
            stristr($room_number_array['raw_message'], '25万')
        ) {
            $room_number_array['type'] = '25万房';
        } elseif (
            stristr($room_number_array['raw_message'], '18w') or
            stristr($room_number_array['raw_message'], '18万') or
            strstr($room_number_array['raw_message'], '大师房')
        ) {
            $room_number_array['type'] = '18万大师房';
        } elseif (
            stristr($room_number_array['raw_message'], '12w') or
            stristr($room_number_array['raw_message'], '12万') or
            strstr($room_number_array['raw_message'], '高手房')
        ) {
            $room_number_array['type'] = '12万高手房';
        } elseif (
            stristr($room_number_array['raw_message'], '7w') or
            stristr($room_number_array['raw_message'], '7万') or
            strstr($room_number_array['raw_message'], '常规房')
        ) {
            $room_number_array['type'] = '7万常规房';
        } else {
            $room_number_array['type'] = '';
        }
    }

    $timestamp = micro_second_time();
    $room_number_array['time'] = $timestamp;
    $redis = new Redis();
    $redis->connect('127.0.0.1');
    $redis_key = 'bandori_room_number';
    $redis_number_data = $redis->lRange($redis_key, 0, -1);
    $push_flag = true;
    for ($i = count($redis_number_data) - 1; $i >= 0; $i--) {
        $room_number_array_temp = json_decode($redis_number_data[$i], true);
        if ($timestamp - $room_number_array_temp['time'] < 10000) {
            if ($room_number_array['number'] == $room_number_array_temp['number']) {
                $push_flag = false;
            }
        } else {
            break;
        }
    }
    if ($push_flag) {
        $redis->rPush($redis_key, json_encode($room_number_array));
        return array(
            'status' => 'success',
            'response' => ''
        );
    } else {
        return array(
            'status' => 'failure',
            'response' => 'duplicate_number_submit'
        );
    }
}

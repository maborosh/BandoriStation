<?php

function submit_room_number($request)
{
    $room_number_array = array();
    if (isset($request['number'])) {
        if (preg_match('/^[0-9]{5,6}$/', $request['number'])) {
            $room_number_array['number'] = intval($request['number']);
        } else {
            return array(
                'status' => 'failure',
                'response' => 'illegal_number_format'
            );
        }
    } else {
        return array(
            'status' => 'failure',
            'response' => 'miss_number_parameter'
        );
    }

    if (isset($request['raw_message'])) {
        if ($request['raw_message'] == $request['number']) {
            return array(
                'status' => 'failure',
                'response' => 'miss_raw_message_description'
            );
        } elseif (strstr($request['raw_message'], $request['number'])) {
            if ($request['method'] == 'GET') {
                $room_number_array['raw_message'] = urldecode(trim($request['raw_message']));
            } else {
                $room_number_array['raw_message'] = trim($request['raw_message']);
            }
        } else {
            return array(
                'status' => 'failure',
                'response' => 'illegal_raw_message'
            );
        }
    } else {
        return array(
            'status' => 'failure',
            'response' => 'miss_raw_message_parameter'
        );
    }

    if (isset($request['source'])) {
        if ($request['method'] == 'GET') {
            $source = urldecode($request['source']);
        } else {
            $source = $request['source'];
        }
        if (data_source_info($source, 'check_data_source')) {
            $room_number_array['source_info'] = array(
                'name' => $source,
                'type' => data_source_info($request['source'], 'get_source_type')
            );
        } else {
            return array(
                'status' => 'failure',
                'response' => 'unregistered_data_source'
            );
        }
    } else {
        return array(
            'status' => 'failure',
            'response' => 'miss_source_parameter'
        );
    }

    if (isset($request['token'])) {
        if (data_source_info($request['source'], 'get_source_token') != $request['token']) {
            return array(
                'status' => 'failure',
                'response' => 'invalid_token'
            );
        }
    } else {
        return array(
            'status' => 'failure',
            'response' => 'miss_token_parameter'
        );
    }

    if (isset($request['type'])) {
        if ($request['type'] == '25') {
            $room_number_array['type'] = '25';
        } elseif ($request['type'] == '18') {
            $room_number_array['type'] = '18';
        } elseif ($request['type'] == '12') {
            $room_number_array['type'] = '12';
        } elseif ($request['type'] == '7') {
            $room_number_array['type'] = '7';
        } elseif ($request['type'] == 'other') {
            $room_number_array['type'] = 'other';
        } else {
            return array(
                'status' => 'failure',
                'response' => 'undefined_type_value'
            );
        }
    } else {
        $room_number_array['type'] = classify_room_number_type($room_number_array['raw_message']);
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
        if (isset($request['user_id'])) {
            if (is_numeric($request['user_id']) and !strpos($request['user_id'], '.')) {
                $dbh_bandori_station = db_select('bandori_station');
                if ($room_number_array['source_info']['type'] == 'qq') {
                    $sth = $dbh_bandori_station->prepare("SELECT user_id FROM block_list WHERE type = 'qq' AND user_id = " . $request['user_id']);
                    $sth->execute();
                    if ($sth->fetch(PDO::FETCH_ASSOC)) {
                        return array(
                            'status' => 'failure',
                            'response' => 'banned_user'
                        );
                    }

                    $sth = $dbh_bandori_station->prepare("SELECT user_id, username, avatar FROM website_account WHERE qq = " . $request['user_id']);
                    $sth->execute();
                    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
                    if ($sql_result) {
                        $sth = $dbh_bandori_station->prepare("SELECT user_id FROM block_list WHERE type = 'local' AND user_id = " . $sql_result['user_id']);
                        $sth->execute();
                        if ($sth->fetch(PDO::FETCH_ASSOC)) {
                            return array(
                                'status' => 'failure',
                                'response' => 'banned_user'
                            );
                        }

                        $room_number_array['user_info'] = array(
                            'type' => 'local',
                            'user_id' => intval($sql_result['user_id']),
                            'username' => $sql_result['username'],
                            'avatar' => $sql_result['avatar']
                        );
                    } else {
                        $room_number_array['user_info'] = array(
                            'type' => 'qq',
                            'user_id' => intval($request['user_id']),
                            'username' => 'QQ用户' . qq_mask($request['user_id']),
                            'avatar' => 'qq_user_icon.png'
                        );
                    }
                } else {
                    return array(
                        'status' => 'failure',
                        'response' => 'undefined_source_type'
                    );
                }
            } else {
                return array(
                    'status' => 'failure',
                    'response' => 'illegal_user_id_formal'
                );
            }
        } else {
            return array(
                'status' => 'failure',
                'response' => 'miss_user_id_parameter'
            );
        }

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
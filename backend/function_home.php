<?php

function home_initialize()
{
    session_start();
    if (!isset($_SESSION['login'])) {
        return array(
            'status' => 'failure',
            'response' => 'login_check_failure'
        );
    }
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("SELECT room_number_filter FROM website_account WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    return array(
        'status' => 'success',
        'response' => json_decode($sql_result['room_number_filter'], true)
    );
}

function home_send_room_number($room_number, $description)
{
    session_start();
    if (!isset($_SESSION['login'])) {
        return array(
            'status' => 'failure',
            'response' => 'login_check_failure'
        );
    }
    if (!preg_match('/^[0-9]{5,6}$/', $room_number)) {
        return array(
            'status' => 'failure',
            'response' => 'illegal_room_number'
        );
    }
    if (!$description) {
        return array(
            'status' => 'failure',
            'response' => 'empty_description'
        );
    }

    $timestamp = micro_second_time();
    $redis = new Redis();
    $redis->connect('127.0.0.1');
    $redis_key = 'bandori_room_number';
    $redis_number_data = $redis->lRange($redis_key, 0, -1);
    $push_flag = true;
    for ($i = count($redis_number_data) - 1; $i >= 0; $i--) {
        $room_number_array_temp = json_decode($redis_number_data[$i], true);
        if ($timestamp - $room_number_array_temp['time'] < 10000) {
            if ($room_number == $room_number_array_temp['number']) {
                $push_flag = false;
            }
        } else {
            break;
        }
    }

    if ($push_flag) {
        $dbh_bandori_station = db_select('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT user_id FROM block_list WHERE type = 'local' AND user_id = " . $_SESSION['user_id']);
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        if ($sql_result) {
            return array(
                'status' => 'failure',
                'response' => 'banned_user'
            );
        }

        $sth = $dbh_bandori_station->prepare("SELECT username, avatar FROM website_account WHERE user_id = " . $_SESSION['user_id']);
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        $room_number_array = array(
            'number' => $room_number,
            'raw_message' => $room_number . ' ' . $description,
            'source_info' => array(
                'name' => 'Bandori Station',
                'type' => 'website'
            ),
            'type' => classify_room_number_type($description),
            'time' => $timestamp,
            'user_info' => array(
                'type' => 'local',
                'user_id' => $_SESSION['user_id'],
                'username' => $sql_result['username'],
                'avatar' => $sql_result['avatar']
            )
        );
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

function home_update_room_number_filter($room_number_filter)
{
    session_start();
    if (!isset($_SESSION['login'])) {
        return array(
            'status' => 'failure',
            'response' => 'login_check_failure'
        );
    }
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("UPDATE website_account SET room_number_filter = '" . json_encode($room_number_filter, JSON_UNESCAPED_UNICODE) . "' WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();
    return array(
        'status' => 'success',
        'response' => ''
    );
}

function home_inform_user($type, $user_id, $reason)
{
    session_start();
    if (!isset($_SESSION['login'])) {
        return array(
            'status' => 'failure',
            'response' => 'login_check_failure'
        );
    }
    if (!$reason) {
        return array(
            'status' => 'failure',
            'response' => 'empty_reason'
        );
    } else {
        $dbh_bandori_station = db_select('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT MAX(id) FROM informant_list");
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        if ($sql_result['MAX(id)']) {
            $id = $sql_result['MAX(id)'] + 1;
        } else {
            $id = 1;
        }
        $timestamp = time();
        $sth = $dbh_bandori_station->prepare("INSERT INTO informant_list(id, informer_user_id, be_informed_user_type, be_informed_user_id, reason, timestamp) 
VALUES ($id, " . $_SESSION['user_id'] . ", '$type', $user_id, '$reason', $timestamp)");
        $sth->execute();
        return array(
            'status' => 'success',
            'response' => ''
        );
    }
}
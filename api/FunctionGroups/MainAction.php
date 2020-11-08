<?php


namespace BS_API\FunctionGroups;


use BS_API\Utility\DatabaseConfig;
use BS_API\Utility\GlobalConfig;
use BS_API\Utility\GlobalFunctions;
use PDO;

class MainAction
{
    public function initializeAccountSetting()
    {
        if (is_array($response = $user_id = GlobalFunctions::accessPermissionCheck())) {
            return $response;
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT avatar FROM website_account 
WHERE user_id = $user_id");
        $sth->execute();
        return GlobalFunctions::generateResponse($sth->fetch(PDO::FETCH_ASSOC), true);
    }

    public function getRoomNumberFilter()
    {
        if (is_array($response = $user_id = GlobalFunctions::accessPermissionCheck())) {
            return $response;
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT room_number_filter FROM website_account 
WHERE user_id = $user_id");
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        $room_number_filter = json_decode($sql_result['room_number_filter'], true);
        if (!$room_number_filter) {
            $room_number_filter = array(
                'type' => array(),
                'keyword' => array(),
                'user' => array()
            );
        }
        if ($room_number_filter['user']) {
            $local_user_list = array();
            foreach ($room_number_filter['user'] as $user) {
                if ($user['type'] == 'local') {
                    $local_user_list[] = $user['user_id'];
                }
            }
            $sth = $dbh_bandori_station->prepare("SELECT user_id, username, avatar FROM website_account 
WHERE user_id IN (" . implode(', ', $local_user_list) . ")");
            $sth->execute();
            $sql_result = $sth->fetchAll(PDO::FETCH_ASSOC);
            $user_setting_dict = array();
            foreach ($sql_result as $user_setting) {
                $user_setting_dict[$user_setting['user_id']] = $user_setting;
            }
            foreach ($room_number_filter['user'] as &$user) {
                if ($user['type'] == 'local') {
                    $user['username'] = $user_setting_dict[$user['user_id']]['username'];
                    $user['avatar'] = $user_setting_dict[$user['user_id']]['avatar'];
                } elseif ($user['type'] == 'qq') {
                    $user['username'] = GlobalFunctions::userIDMask($user['user_id']);
                    $user['avatar'] = '';
                }
            }
        }
        return GlobalFunctions::generateResponse(
            array('room_number_filter' => $room_number_filter), true
        );
    }

    public function updateRoomNumberFilter($request)
    {
        if (is_array($response = $user_id = GlobalFunctions::accessPermissionCheck())) {
            return $response;
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('room_number_filter'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        }
        $room_number_filter = array(
            'type' => array(),
            'keyword' => array(),
            'user' => array()
        );
        if (key_exists('type', $request['data']['room_number_filter'])) {
            $type_ref = array('7', '12', '18', '25', 'other');
            foreach ($request['data']['room_number_filter']['type'] as $value) {
                if (in_array($value, $type_ref, true)) {
                    $room_number_filter['type'][] = $value;
                }
            }
        }
        if (key_exists('keyword', $request['data']['room_number_filter'])) {
            foreach ($request['data']['room_number_filter']['keyword'] as $value) {
                $room_number_filter['keyword'][] = strval($value);
            }
        }
        if (key_exists('user', $request['data']['room_number_filter'])) {
            foreach ($request['data']['room_number_filter']['user'] as $value) {
                if (key_exists('type', $value) and key_exists('user_id', $value)) {
                    $room_number_filter['user'][] = array(
                        'type' => $value['type'],
                        'user_id' => $value['user_id']
                    );
                }
            }
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("UPDATE website_account 
SET room_number_filter = '" . json_encode($room_number_filter, JSON_UNESCAPED_UNICODE) . "' 
WHERE user_id = $user_id");
        $sth->execute();
        return GlobalFunctions::generateResponse('', true);
    }

    public function informUser($request)
    {
        if (is_array($response = $user_id = GlobalFunctions::accessPermissionCheck())) {
            return $response;
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('type', 'user_id', 'raw_message', 'reason'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $timestamp = time();
        $sth = $dbh_bandori_station->prepare("
INSERT INTO informant_list(informer_user_id, be_informed_user_type, be_informed_user_id, raw_message, reason, timestamp) 
VALUES ($user_id, '{$request['data']['type']}', {$request['data']['user_id']}, 
        '" . json_encode($request['data']['raw_message'], JSON_UNESCAPED_UNICODE) . "', 
        '{$request['data']['reason']}', $timestamp)");
        $sth->execute();
        return GlobalFunctions::generateResponse('', true);
    }
}
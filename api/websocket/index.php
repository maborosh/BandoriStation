<?php
chdir(dirname(__FILE__));
define('HEARTBEAT_TIME', 30);

require '../../functions/db_select.php';

require 'Workerman/Autoloader.php';
require '../other_functions.php';
require '../query_room_number.php';

use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use Workerman\Lib\Timer;

$worker = new Worker('websocket://127.0.0.1:9000');

// 首次连接发送消息
$worker->onConnect = function (TcpConnection $connection)
{
    $query_result = query_room_number();
    if ($query_result['status'] == 'success' and $query_result['response']) {
        $connection->send(json_encode($query_result));
    }
    $connection->type = 0;
};

$worker->onMessage = function($connection, $data) {
    $connection->lastMessageTime = time();
    if ($data == 'heartbeat-bandoristation') {
        $connection->type = 0;
    } else {
        $connection->type = 1;
    }
};

// 持续推送消息
$latest_time = 0;
$redis = new Redis();
$redis->pconnect('127.0.0.1');
$worker->onWorkerStart = function($worker) use (&$latest_time, $redis)
{
    Timer::add(0.5, function() use($worker, &$latest_time, $redis) {
        $query_result = query_room_number($latest_time);
        $time_now = time();
        if ($query_result['status'] == 'success' and $query_result['response']) {
            $latest_time = $query_result['response'][count($query_result['response']) - 1]['time'];
            $send_flag = true;
            log_room_number($query_result['response']);
        } else {
            $send_flag = false;
        }
        $online_count = 0;
        foreach($worker->connections as $connection) {
            if (empty($connection->lastMessageTime)) {
                $connection->lastMessageTime = $time_now;
            }
            if ($time_now - $connection->lastMessageTime > HEARTBEAT_TIME) {
                $connection->type = -1;
                $connection->close();
            } elseif ($send_flag) {
                $connection->send(json_encode($query_result));
            }
            if ($connection->type == 0) {
                $online_count += 1;
            }
        }
        $redis->set('bandori_station_online_number', $online_count);
    });
};

Worker::runAll();

function log_room_number($room_number_list)
{
    $list_length = count($room_number_list);
    if ($list_length == 0) {
        return;
    }
    $dbh_bandori_station = db_select('bandori_station');
    $sql_text = "INSERT INTO" . " submit_log(timestamp, number, raw_message, source_name, type, user_type, user_id) VALUES ";
    for ($i = 0; $i < $list_length; $i++) {
        if ($i == 0) {
            $sql_text .= "(" . $room_number_list[$i]['time'] . ", " . $room_number_list[$i]['number'] . ", '" . $room_number_list[$i]['raw_message'] .
                "', '" . $room_number_list[$i]['source_info']['name'] . "', '" . $room_number_list[$i]['type'] . "', '" .
                $room_number_list[$i]['user_info']['type'] . "', " . $room_number_list[$i]['user_info']['user_id'] . ")";
        } else {
            $sql_text .= ", (" . $room_number_list[$i]['time'] . ", " . $room_number_list[$i]['number'] . ", '" . $room_number_list[$i]['raw_message'] .
                "', '" . $room_number_list[$i]['source_info']['name'] . "', '" . $room_number_list[$i]['type'] . "', '" .
                $room_number_list[$i]['user_info']['type'] . "', " . $room_number_list[$i]['user_info']['user_id'] . ")";
        }
    }
    $sth = $dbh_bandori_station->prepare($sql_text);
    $sth->execute();
}

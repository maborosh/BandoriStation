<?php
define('API_ROOT', dirname(dirname(__FILE__)));
define('HEARTBEAT_TIME', 30);

require 'Workerman/Autoloader.php';
require API_ROOT . '/other_functions.php';
require API_ROOT . '/query_room_number.php';

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
};

$worker->onMessage = function($connection) {
    $connection->lastMessageTime = time();
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
        } else {
            $send_flag = false;
        }
        foreach($worker->connections as $connection) {
            if (empty($connection->lastMessageTime)) {
                $connection->lastMessageTime = $time_now;
            }
            if ($time_now - $connection->lastMessageTime > HEARTBEAT_TIME) {
                $connection->close();
            } elseif ($send_flag) {
                $connection->send(json_encode($query_result));
            }
        }
        $redis->set('bandori_station_online_number', count($worker->connections));
    });
};

Worker::runAll();
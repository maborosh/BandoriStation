<?php
define('API_ROOT', dirname(dirname(__FILE__)));
require 'Workerman/Autoloader.php';
require API_ROOT . '/other_functions.php';
require API_ROOT . '/query_room_number.php';
use Workerman\Worker;
use Workerman\Lib\Timer;

$worker = new Worker('websocket://127.0.0.1:9000');
// 进程启动后定时推送数据给客户端
$worker->onWorkerStart = function($worker) {
    Timer::add(0.5, function() use($worker) {
        $data = query_room_number();
        if ($data) {
            $response_data = json_encode(
                array(
                    'status' => 'success',
                    'data' => $data
                )
            );
            foreach($worker->connections as $connection) {
                $connection->send($response_data);
            }
        }
    });
};

Worker::runAll();
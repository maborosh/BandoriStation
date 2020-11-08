<?php

use BS_API\Utility\GlobalFunctions;
use BS_API\WebServer\ActionHandler;
use BS_API\WebServer\ClientManager;
use BS_API\WebServer\RequestHandler;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Process;
use Swoole\Timer;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebServer
{
    private Server $server;

    public static function getInstance($host, $port, $setting)
    {
        WebServer::initialize();
        return new WebServer($host, $port, $setting);
    }

    private static function initialize()
    {
        define('ROOT_PATH', dirname(dirname(__FILE__)));
        Swoole\Runtime::enableCoroutine();

        require ROOT_PATH . '/Utility/GlobalConfig.php';
        require ROOT_PATH . '/Utility/GlobalFunctions.php';
        require ROOT_PATH . '/Utility/class_loader.php';

        spl_autoload_register('class_loader');

        require ROOT_PATH . '/Utility/error_handler.php';

        ClientManager::createTable();
    }

    private function __construct($host, $port, $setting)
    {
        $this->server = new Server($host, $port);

        $this->server->set($setting);

        $this->server->on('open', function (Server $server, Request $request) {
            $frame = new Frame();
            $frame->fd = $request->fd;
            $frame->data = '{"action":"getServerTime","data":null}';
            ActionHandler::getInstance($server, $frame)->execute();
        });

        $this->server->on('message', function (Server $server, Frame $frame) {
            ActionHandler::getInstance($server, $frame)->execute();
        });

        $this->server->on('request', function (Request $request, Response $response) {
            RequestHandler::getInstance($this->server, $request, $response)->execute();
        });

        $this->server->on('close', function (Server $server, $fd) {
            ClientManager::removeClient($fd);
        });

        $this->setTaskProcess();
        $this->setChatLogProcess();
    }

    private function setTaskProcess()
    {
        $this->server->on('task', function (Server $server, $task_id, $src_worker_id, $data) {
            $client_list = ClientManager::getClientList();
            foreach ($client_list as $client) {
                if ($client[$data['type']]) {
                    $server->push($client['fd'], GlobalFunctions::generateWebSocketResponse(
                        $data['action'], $data['data']
                    ));
                }
            }
        });
    }

    private function setChatLogProcess()
    {
        $process = new Process(function () {
            Timer::tick(300000, function () {
                GlobalFunctions::logRoomNumber();
                GlobalFunctions::logChat();
            });
        }, false, 1, true);
        $this->server->addProcess($process);
    }

    public function start()
    {
        $this->server->start();
    }
}

WebServer::getInstance('127.0.0.1', 19820, array(
    'task_worker_num' => 5,
    'heartbeat_check_interval' => 30
))->start();
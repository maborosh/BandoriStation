<?php


namespace BS_API\WebServer;


use BS_API\Utility\DatabaseConfig;
use BS_API\Utility\GlobalConfig;
use BS_API\Utility\GlobalFunctions;
use PDO;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class ActionHandler
{
    private Server $server;
    private int $fd;
    private array $requestList;
    private bool $processingFlag = false;

    public static function getInstance($server, $frame)
    {
        return new ActionHandler($server, $frame);
    }

    private function __construct(Server $server, Frame $frame)
    {
        if ($request = json_decode($frame->data, true)) {
            $this->server = $server;
            $this->fd = $frame->fd;
            if ($this->checkAssoc($request)) {
                $this->parseRequest($request);
            } else {
                foreach ($request as $action) {
                    $this->parseRequest($action);
                }
            }
        }
    }

    private function parseRequest($request)
    {
        if (isset($request['action']) and key_exists('data', $request)) {
            $this->requestList[] = array(
                'action' => GlobalFunctions::camelize($request['action']),
                'data' => $request['data']
            );
            $this->processingFlag = true;
        }
    }

    public function execute()
    {
        if ($this->processingFlag) {
            foreach ($this->requestList as $action) {
                if (in_array($action['action'], GlobalConfig::ACTION_LIST)) {
                    $this->{$action['action']}($action['data']);
                }
            }
        }
    }

    function checkAssoc($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function setAccessPermission($data)
    {
        if (!isset($data['token'])) {
            $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                'sendNotice', GlobalConfig::UNDEFINED_ACCESS_TOKEN, false
            ));
        } elseif (is_array($response = $user_id = GlobalFunctions::accessPermissionCheck(
            null, 'login', $data['token']
        ))) {
            $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                'sendNotice', $response['response'], false
            ));
        } else {
            ClientManager::setClient($this->fd, array('user_id' => $user_id), true);
        }
    }

    private function getServerTime()
    {
        $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
            'sendServerTime', array('time' => GlobalFunctions::getMillisecondTime())
        ));
    }

    private function setClient($data)
    {
        ClientManager::setClient($this->fd, $data);
    }

    private function getRoomNumberList()
    {
        $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
            'sendRoomNumberList', GlobalFunctions::queryRoomNumber()
        ));
    }

    private function sendRoomNumber($data)
    {
        $user_id = ClientManager::getClientSetting($this->fd, 'user_id');
        if ($user_id === 0) {
            $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                'sendNotice', GlobalConfig::NO_PERMISSION, false
            ));
            return 0;
        } elseif (!GlobalFunctions::checkArrayKeys($data, array('room_number', 'description'))) {
            $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                'sendNotice', GlobalConfig::MISSING_PARAMETERS, false
            ));
            return 0;
        } elseif (!preg_match('/^[0-9]{5,6}$/', $data['room_number'])) {
            $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                'sendNotice', 'Illegal room number', false
            ));
            return 0;
        } elseif (!$data['description']) {
            $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                'sendNotice', 'Empty description', false
            ));
            return 0;
        }
        $timestamp = GlobalFunctions::getMillisecondTime();
        $redis = DatabaseConfig::redisConnect();
        $redis_key = GlobalFunctions::generateRedisKey('bandori_room_number');
        $room_number_data = $redis->lRange($redis_key, 0, -1);
        $push_flag = true;
        for ($i = count($room_number_data) - 1; $i >= 0; $i--) {
            $room_number_data_array_temp = json_decode($room_number_data[$i], true);
            if ($timestamp - $room_number_data_array_temp['time'] < 10000) {
                if ($data['room_number'] == $room_number_data_array_temp['number']) {
                    $push_flag = false;
                }
            } else {
                break;
            }
        }

        if ($push_flag) {
            $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
            $sth = $dbh_bandori_station->prepare("SELECT user_id FROM block_list 
WHERE type = 'local' AND user_id = $user_id");
            $sth->execute();
            if ($sth->fetch(PDO::FETCH_ASSOC)) {
                $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                    'sendNotice', 'Banned user', false
                ));
                return 0;
            }

            $sth = $dbh_bandori_station->prepare("SELECT username, avatar FROM website_account 
WHERE user_id = $user_id");
            $sth->execute();
            $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
            $room_number_message = array(
                'number' => $data['room_number'],
                'raw_message' => $data['room_number'] . ' ' . $data['description'],
                'source_info' => array(
                    'name' => 'BandoriStation',
                    'type' => 'website'
                ),
                'type' => GlobalFunctions::classifyRoomNumberType(
                    $data['description'],
                    isset($data['type']) ? $data['type'] : null
                ),
                'time' => $timestamp,
                'user_info' => array(
                    'type' => 'local',
                    'user_id' => $user_id,
                    'username' => $sql_result['username'],
                    'avatar' => $sql_result['avatar']
                )
            );
            $redis->rPush($redis_key, json_encode($room_number_message));
            $this->server->task(array(
                'type' => 'send_room_number',
                'action' => 'sendRoomNumberList',
                'data' => array($room_number_message)
            ));
            return 0;
        } else {
            $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                'sendNotice', 'Duplicate number submit', false
            ));
            return 0;
        }
    }

    private function initializeChatRoom()
    {
        $user_id = ClientManager::getClientSetting($this->fd, 'user_id');
        if ($user_id === 0) {
            $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                'sendNotice', GlobalConfig::NO_PERMISSION, false
            ));
            return 0;
        }
        $response_message_list = array();
        $redis = DatabaseConfig::redisConnect();
        $redis_key = GlobalFunctions::generateRedisKey('chat');
        $message_list = $redis->lRange($redis_key, 0, -1);
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        if ($message_list) {
            $user_list = array();
            $message_array_list = array();
            foreach ($message_list as $message) {
                $message_array = json_decode($message, true);
                $message_array_list[] = $message_array;
                if (!in_array($message_array['user_info']['user_id'], $user_list)) {
                    $user_list[] = $message_array['user_info']['user_id'];
                }
            }
            $sth = $dbh_bandori_station->prepare("SELECT user_id, username, avatar FROM website_account 
WHERE user_id IN (" . implode(', ', $user_list) . ")");
            $sth->execute();
            $sql_result = $sth->fetchAll(PDO::FETCH_ASSOC);
            $user_list = array();
            foreach ($sql_result as $row) {
                $user_list[$row['user_id']] = $row;
            }
            foreach ($message_array_list as $message) {
                $message['user_info'] = $user_list[$message['user_info']['user_id']];
                $response_message_list[] = $message;
            }
        }
        $residual_message_number = 40 - count($response_message_list);
        $is_end = false;
        if ($residual_message_number > 0) {
            $sth = $dbh_bandori_station->prepare("SELECT cl.user_id, timestamp, content, wa.username, wa.avatar
FROM chat_log cl
LEFT JOIN website_account wa on cl.user_id = wa.user_id
ORDER BY cl.id DESC LIMIT $residual_message_number");
            $sth->execute();
            $sql_result = $sth->fetchAll(PDO::FETCH_ASSOC);
            if (count($sql_result) < $residual_message_number) {
                $is_end = true;
            }
            foreach ($sql_result as $row) {
                array_unshift($response_message_list, array(
                    'timestamp' => $row['timestamp'],
                    'content' => $row['content'],
                    'user_info' => array(
                        'user_id' => $row['user_id'],
                        'username' => $row['username'],
                        'avatar' => $row['avatar']
                    )
                ));
            }
        }
        $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
            'initializeChatRoom', array(
                'message_list' => $response_message_list,
                'self_id' => $user_id,
                'is_end' => $is_end
            )
        ));
        return 0;
    }

    private function sendChat($data)
    {
        $user_id = ClientManager::getClientSetting($this->fd, 'user_id');
        if ($user_id === 0) {
            $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                'sendNotice', GlobalConfig::NO_PERMISSION, false
            ));
            return 0;
        } elseif (!GlobalFunctions::checkArrayKeys($data, array('message'))) {
            $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                'sendNotice', GlobalConfig::MISSING_PARAMETERS, false
            ));
            return 0;
        } elseif ($data['message'] == '') {
            return 0;
        }
        $timestamp = GlobalFunctions::getMillisecondTime();
        $redis = DatabaseConfig::redisConnect();
        $redis_key = GlobalFunctions::generateRedisKey('chat');
        $chat = array(
            'timestamp' => $timestamp,
            'content' => $data['message'],
            'user_info' => array(
                'user_id' => $user_id
            )
        );
        $redis->rPush($redis_key, json_encode($chat));
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT username, avatar FROM website_account 
WHERE user_id = $user_id");
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        $chat['user_info']['username'] = $sql_result['username'];
        $chat['user_info']['avatar'] = $sql_result['avatar'];
        $this->server->task(array(
            'type' => 'send_chat',
            'action' => 'sendChat',
            'data' => array($chat)
        ));
        return 0;
    }

    private function loadChatLog($data)
    {
        $user_id = ClientManager::getClientSetting($this->fd, 'user_id');
        if ($user_id === 0) {
            $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                'sendNotice', GlobalConfig::NO_PERMISSION, false
            ));
            return 0;
        } elseif (!GlobalFunctions::checkArrayKeys($data, array('timestamp'))) {
            $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
                'sendNotice', GlobalConfig::MISSING_PARAMETERS, false
            ));
            return 0;
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT cl.user_id, timestamp, content, wa.username, wa.avatar
FROM chat_log cl
LEFT JOIN website_account wa on cl.user_id = wa.user_id
WHERE cl.timestamp < {$data['timestamp']}
ORDER BY cl.id DESC LIMIT 40");
        $sth->execute();
        $sql_result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $response_message_list = array();
        foreach ($sql_result as $row) {
            array_unshift($response_message_list, array(
                'timestamp' => $row['timestamp'],
                'content' => $row['content'],
                'user_info' => array(
                    'user_id' => $row['user_id'],
                    'username' => $row['username'],
                    'avatar' => $row['avatar']
                )
            ));
        }
        $this->server->push($this->fd, GlobalFunctions::generateWebSocketResponse(
            'loadChatLog', array(
                'message_list' => $response_message_list,
                'is_end' => count($sql_result) < 40
            )
        ));
        return 0;
    }
}
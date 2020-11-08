<?php


namespace BS_API\FunctionGroups;


use BS_API\Utility\DatabaseConfig;
use BS_API\Utility\GlobalConfig;
use BS_API\Utility\GlobalFunctions;
use BS_API\WebServer\ClientManager;
use PDO;
use Swoole\Http\Request;
use Swoole\Http\Response;
use ZipArchive;

class Common
{
    public static function getInstance()
    {
        return new Common();
    }

    public function submitRoomNumber($request)
    {
        $room_number_message = array();
        if (isset($request['data']['number'])) {
            if (preg_match('/^[0-9]{5,6}$/', $request['data']['number'])) {
                $room_number_message['number'] = strval($request['data']['number']);
            } else {
                return GlobalFunctions::generateResponse('Illegal number format');
            }
        } else {
            return GlobalFunctions::generateResponse('Undefined room number');
        }

        if (isset($request['data']['description'])) {
            if (trim($request['data']['description'])) {
                if ($request['method'] == 'GET') {
                    $room_number_message['raw_message'] =
                        $room_number_message['number'] . ' ' . urldecode(trim($request['data']['description']));
                } else {
                    $room_number_message['raw_message'] =
                        $room_number_message['number'] . ' ' . trim($request['data']['description']);
                }
            } else {
                return GlobalFunctions::generateResponse('Empty description');
            }
        } elseif (isset($request['data']['raw_message'])) {
            if (trim($request['data']['raw_message']) == $request['data']['number']) {
                return GlobalFunctions::generateResponse('Undefined room number description');
            } elseif (strstr($request['data']['raw_message'], $request['data']['number'])) {
                if ($request['method'] == 'GET') {
                    $room_number_message['raw_message'] = urldecode(trim($request['data']['raw_message']));
                } else {
                    $room_number_message['raw_message'] = trim($request['data']['raw_message']);
                }
            } else {
                return GlobalFunctions::generateResponse('Illegal raw message');
            }
        } else {
            return GlobalFunctions::generateResponse('Undefined raw message or description');
        }

        if (isset($request['data']['source'])) {
            if ($request['method'] == 'GET') {
                $source = urldecode($request['data']['source']);
            } else {
                $source = $request['data']['source'];
            }
            if (GlobalFunctions::getRoomNumberDataSourceInfo($source, GlobalConfig::CHECK_DATA_SOURCE)) {
                $room_number_message['source_info'] = array(
                    'name' => $source,
                    'type' => GlobalFunctions::getRoomNumberDataSourceInfo(
                        $source,
                        GlobalConfig::GET_SOURCE_TYPE
                    )
                );
                if (isset($request['data']['token'])) {
                    if (GlobalFunctions::getRoomNumberDataSourceInfo(
                        $source,
                        GlobalConfig::GET_SOURCE_TOKEN) != $request['data']['token']
                    ) {
                        return GlobalFunctions::generateResponse('Invalid token');
                    }
                } else {
                    return GlobalFunctions::generateResponse('Undefined token');
                }
            } else {
                return GlobalFunctions::generateResponse('Unregistered data source');
            }
        } else {
            return GlobalFunctions::generateResponse('Undefined source');
        }

        $room_number_message['type'] = GlobalFunctions::classifyRoomNumberType(
            $room_number_message['raw_message'],
            isset($request['data']['type']) ? $request['data']['type'] : null
        );

        $timestamp = GlobalFunctions::getMillisecondTime();
        $room_number_message['time'] = $timestamp;
        $redis = DatabaseConfig::redisConnect();
        $redis_key = GlobalFunctions::generateRedisKey('bandori_room_number');
        $redis_number_data = $redis->lRange($redis_key, 0, -1);
        $push_flag = true;
        for ($i = count($redis_number_data) - 1; $i >= 0; $i--) {
            $room_number_message_temp = json_decode($redis_number_data[$i], true);
            if ($timestamp - $room_number_message_temp['time'] < 10000) {
                if ($room_number_message['number'] == $room_number_message_temp['number']) {
                    $push_flag = false;
                }
            } else {
                break;
            }
        }
        if ($push_flag) {
            if (isset($request['data']['user_id'])) {
                $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
                if ($room_number_message['source_info']['type'] == 'qq') {
                    $sth = $dbh_bandori_station->prepare("SELECT user_id FROM block_list 
WHERE type = 'qq' AND user_id = {$request['data']['user_id']}");
                    $sth->execute();
                    if ($sth->fetch(PDO::FETCH_ASSOC)) {
                        return GlobalFunctions::generateResponse('Banned user');
                    }

                    $sth = $dbh_bandori_station->prepare("SELECT user_id, username, avatar 
FROM website_account WHERE qq = {$request['data']['user_id']}");
                    $sth->execute();
                    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
                    if ($sql_result) {
                        $sth = $dbh_bandori_station->prepare("SELECT user_id FROM block_list 
WHERE type = 'local' AND user_id = {$sql_result['user_id']}");
                        $sth->execute();
                        if ($sth->fetch(PDO::FETCH_ASSOC)) {
                            return GlobalFunctions::generateResponse('Banned user');
                        }

                        $room_number_message['user_info'] = array(
                            'type' => 'local',
                            'user_id' => $sql_result['user_id'],
                            'username' => $sql_result['username'],
                            'avatar' => $sql_result['avatar']
                        );
                    } else {
                        $room_number_message['user_info'] = array(
                            'type' => 'qq',
                            'user_id' => intval($request['data']['user_id']),
                            'username' => 'QQ用户' . GlobalFunctions::userIDMask($request['data']['user_id']),
                            'avatar' => ''
                        );
                    }
                } else {
                    return GlobalFunctions::generateResponse('Invalid source type');
                }
            } else {
                return GlobalFunctions::generateResponse('Undefined user id');
            }
            $redis->rPush($redis_key, json_encode($room_number_message));
            $request['server']->task(array(
                'type' => 'send_room_number',
                'action' => 'sendRoomNumberList',
                'data' => array($room_number_message)
            ));
            return GlobalFunctions::generateResponse('', true);
        } else {
            return GlobalFunctions::generateResponse('Duplicate submit');
        }
    }
    
    public function queryRoomNumber($request)
    {
        if (isset($request['data']['latest_time'])) {
            if (!is_numeric($request['data']['latest_time'])) {
                return GlobalFunctions::generateResponse('Illegal timestamp');
            }
            $latest_time = intval($request['data']['latest_time']);
        } else {
            $latest_time = null;
        }
        return GlobalFunctions::generateResponse(GlobalFunctions::queryRoomNumber($latest_time), true);
    }

    public function getOnlineNumber()
    {
        $client_list = ClientManager::getClientList();
        $online_number = 0;
        foreach ($client_list as $client) {
            if ($client['client'] == 'BandoriStation') {
                $online_number += 1;
            }
        }
        return GlobalFunctions::generateResponse(array('online_number' => $online_number), true);
    }

    public function getRoomNumberStat($request, Request $requestObj, Response $responseObj)
    {
        require_once ROOT_PATH . '/Libraries/jieba/text_segmentation.php';

        $dbh_bandori_db = DatabaseConfig::mysqlDBHelper('bandori_db');
        if (!isset($request['data']['event_id'])) {
            $sth = $dbh_bandori_db->prepare("SELECT eventId FROM event_id WHERE server = 'cn'");
            $sth->execute();
            $request['data']['event_id'] = $sth->fetch(PDO::FETCH_ASSOC)['eventId'];
        }
        $sth = $dbh_bandori_db->prepare("SELECT startAt, endAt FROM event_data_cn WHERE eventId = {$request['data']['event_id']}");
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        $start_time = $sql_result['startAt'];
        $end_time = $sql_result['endAt'];

        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sql_text = "SELECT timestamp, number, raw_message, source_name FROM room_number_log 
WHERE timestamp >= {$start_time}000 AND timestamp < {$end_time}000";
        $sth = $dbh_bandori_station->prepare($sql_text);
        $sth->execute();
        $sql_result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $room_number_data_check_list = array();
        $stat = array(
            'quantity_per_hour' => array(),
            'source' => array(),
            'keyword' => array()
        );
        foreach ($sql_result as $room_number_data) {
            if (array_key_exists($room_number_data['number'], $room_number_data_check_list)) {
                if ($room_number_data['timestamp'] - $room_number_data_check_list[$room_number_data['number']]['timestamp'] < 130000) {
                    $room_number_data_check_list[$room_number_data['number']] = $room_number_data;
                    continue;
                }
            }
            $room_number_data_check_list[$room_number_data['number']] = $room_number_data;

            $timestamp = floor($room_number_data['timestamp'] / 1000);
            $start_hour = floor($start_time / 3600);
            $hour_count = intval(floor($timestamp / 3600) - $start_hour);
            if (array_key_exists($hour_count, $stat['quantity_per_hour'])) {
                $stat['quantity_per_hour'][$hour_count]['count'] += 1;
            } else {
                $stat['quantity_per_hour'][$hour_count] = array(
                    'time' => date('Y/m/d H:i:s', ($start_hour + $hour_count) * 3600),
                    'count' => 1
                );
            }

            if (array_key_exists($room_number_data['source_name'], $stat['source'])) {
                $stat['source'][$room_number_data['source_name']] += 1;
            } else {
                $stat['source'][$room_number_data['source_name']] = 1;
            }

            $room_number_description = strtolower(trim(str_replace($room_number_data['number'], '', $room_number_data['raw_message'])));
            $room_number_keyword_list = text_segmentation($room_number_description, 1);
            foreach ($room_number_keyword_list as $keyword) {
                if ($keyword == "\n" or $keyword == '"') {
                    continue;
                }
                if (array_key_exists($keyword, $stat['keyword'])) {
                    $stat['keyword'][$keyword] += 1;
                } else {
                    $stat['keyword'][$keyword] = 1;
                }
            }

//            echo date('Y-m-d H:i:s', $timestamp);
//            echo ' ';
//            echo $room_number_data['number'];
//            echo ' ';
//            echo $room_number_description;
//            echo ' ';
//            echo '【' . implode('| ', $room_number_keyword_list) . '】';
//            echo '<br>';
        }
        arsort($stat['source']);
        arsort($stat['keyword']);

        if (isset($request['data']['generate_csv']) and $request['data']['generate_csv']) {
            $file_path_list = array(
                ROOT_PATH . '/Assets/temp/quantity_per_hour.csv',
                ROOT_PATH . '/Assets/temp/source.csv',
                ROOT_PATH . '/Assets/temp/keyword.csv',
                ROOT_PATH . '/Assets/temp/event_' . $request['data']['event_id'] . '_stat.zip',
            );
            foreach ($file_path_list as $file_path) {
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            $file_pointer = fopen($file_path_list[0], 'a');
            fputcsv($file_pointer, array('time', 'count'));
            foreach ($stat['quantity_per_hour'] as $per_hour_count) {
                fputcsv($file_pointer, array($per_hour_count['time'], $per_hour_count['count']));
            }
            fclose($file_pointer);

            $file_pointer = fopen($file_path_list[1], 'a');
            fputcsv($file_pointer, array('source_name', 'count'));
            foreach ($stat['source'] as $source_name => $count) {
                fputcsv($file_pointer, array($source_name, $count));
            }
            fclose($file_pointer);

            $file_pointer = fopen($file_path_list[2], 'a');
            fputcsv($file_pointer, array('keyword', 'count'));
            foreach ($stat['keyword'] as $keyword => $count) {
                fputcsv($file_pointer, array($keyword, $count));
            }
            fclose($file_pointer);

            $zip = new ZipArchive();
            $zip->open($file_path_list[3], ZipArchive::CREATE);
            for ($i = 0; $i < 3; $i++) {
                $zip->addFile($file_path_list[$i], basename($file_path_list[$i]));
            }
            $zip->close();

            $file_pointer = fopen($file_path_list[3], 'r');
            $file_size = filesize($file_path_list[3]);
            $file_data = '';
            $buffer = 8192;
            while (!feof($file_pointer)) {
                $file_data .= fread($file_pointer, $buffer);
            }
            fclose($file_pointer);

            $responseObj->header('Content-type', 'application/octet-stream');
            $responseObj->header('Accept-Ranges', 'bytes');
            $responseObj->header('Accept-Length', strval($file_size));
            $responseObj->header('Content-Disposition', 'attachment;filename=' . basename($file_path_list[3]));

            return $file_data;
        } else {
            return array(
                'status' => 'success',
                'response' => $stat
            );
        }
    }
}
<?php


namespace BS_API\Utility;


use ClientException;
use DefaultAcsClient;
use DefaultProfile;
use Dm\Request\V20151123 as Dm;
use ServerException;

class GlobalFunctions
{
    public static function generateResponse($response, $status = false)
    {
        return array(
            'status' => $status ? 'success' : 'failure',
            'response' => $response
        );
    }

    public static function generateWebSocketResponse($action, $response, $status = true)
    {
        return json_encode(array(
            'status' => $status ? 'success' : 'failure',
            'action' => $action,
            'response' => $response
        ));
    }

    public static function camelize($uncamelized_words, $separator = '_')
    {
        if (strstr($uncamelized_words, $separator)) {
            $uncamelized_words = $separator . str_replace($separator, " ", strtolower($uncamelized_words));
            return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator);
        } else {
            return $uncamelized_words;
        }
    }

    public static function generateRedisKey($function, $params = array())
    {
        $redis_key = "BS:$function";
        foreach ($params as $key => $value) {
            $redis_key .= ",$key=$value";
        }
        return $redis_key;
    }

    public static function checkArrayKeys($be_checked_array, $key_list)
    {
        foreach ($key_list as $key) {
            if (!isset($be_checked_array[$key])) {
                return false;
            }
        }
        return true;
    }

    public static function getAccessIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public static function callLimitCheck($key, $limit_times, $redis = null, $check_and_log = true, $duration = 600)
    {
        if (!$redis) {
            $redis = DatabaseConfig::redisConnect();
        }
        $call_list_length = $redis->lLen($key);
        $timestamp = time();
        if ($call_list_length < $limit_times) {
            if ($check_and_log) {
                $redis->rPush($key, $timestamp);
            }
            return false;
        } else {
            while ($call_list_length > $limit_times) {
                $redis->lPop($key);
                $call_list_length -= 1;
            }
            if ($timestamp - $redis->lIndex($key, 0) < $duration) {
                return true;
            } else {
                $redis->lPop($key);
                if ($check_and_log) {
                    $redis->rPush($key, $timestamp);
                }
                return false;
            }
        }
    }

    public static function generateRandomCode($length, $range_from = 0, $range_to = 61)
    {
        $char_array = array(
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
            'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
        );
        $response = '';
        for ($i = 0; $i < $length; $i++) {
            $response .= $char_array[rand($range_from, $range_to)];
        }
        return $response;
    }

    public static function setAccessStatus($user_id, $type, $time, $random_key, $redis = null, $is_set = true)
    {
        if (!$redis) {
            $redis = DatabaseConfig::redisConnect();
        }
        $redis_key = GlobalFunctions::generateRedisKey(
            'access_token',
            array(
                'user_id' => $user_id,
                'type' => $type,
                'key' => $random_key
            )
        );
        if ($is_set) {
            $redis->setex(
                $redis_key,
                $type == 'login' ? 2592000 : 600,
                $time
            );
        } else {
            $redis->del($redis_key);
        }
    }

    public static function checkAccessStatus($user_id, $type, $random_key, $time, $redis = null)
    {
        if (!$redis) {
            $redis = DatabaseConfig::redisConnect();
        }
        $data = $redis->get(GlobalFunctions::generateRedisKey(
            'access_token',
            array(
                'user_id' => $user_id,
                'type' => $type,
                'key' => $random_key
            )
        ));
        if ($data and $data == $time) {
            return true;
        } else {
            return false;
        }
    }

    public static function generatePasswordCipher($user_id, $password)
    {
        // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ SECRET ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

        $confusion_code = [
            'CF3i9Gy+', 'cRKeRZ3{', '@wm4$fC？', '5_Zi5$zJ',
            '9x+c6^-M', '$u8cGJe*', '4】Ee5k8&', '5+nDZ8Tu',
            'tJ7】pn_Z', '7-sT4brB', '$P6LEBKe', '4v-5$Zf￥',
            'MT97&6^i', 'VR+679Sh', '_WSfju！9', 'Un{NtL43'
        ];
        $password_hash = sha1($password);
        $plain_text = $password_hash . $user_id;
        for ($i = 0; $i < 6; $i++) {
            $plain_text .= $confusion_code[hexdec(substr($password_hash, $i, 1))] . $password_hash;
        }

        // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑ SECRET ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        return hash('sha256', $plain_text);
    }

    public static function generateAccessToken($message_array)
    {
        // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ SECRET ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

        return base64_encode(openssl_encrypt(
            json_encode($message_array),
            'AES-256-CBC',
            GlobalConfig::AES_KEY,
            OPENSSL_RAW_DATA,
            GlobalConfig::AES_IV
        ));

        // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑ SECRET ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
    }

    public static function decryptAccessToken($token)
    {
        // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ SECRET ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

        return json_decode(
            openssl_decrypt(
                base64_decode($token),
                'AES-256-CBC',
                GlobalConfig::AES_KEY,
                OPENSSL_RAW_DATA,
                GlobalConfig::AES_IV
            ),
            true
        );

        // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑ SECRET ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
    }

    public static function getAccessToken()
    {
        if (isset($_SERVER['HTTP_AUTH_TOKEN']) and $_SERVER['HTTP_AUTH_TOKEN'] != '') {
            return $_SERVER['HTTP_AUTH_TOKEN'];
        } else {
            return false;
        }
    }

    public static function verifyAccessToken($message_array, $redis = null, $permission_type = 'login')
    {
        if (
            isset($message_array['user_id']) and
            isset($message_array['type']) and
            isset($message_array['set_time']) and
            isset($message_array['key'])
        ) {
            if (!$redis) {
                $redis = DatabaseConfig::redisConnect();
            }
            if (self::checkAccessStatus(
                $message_array['user_id'], $permission_type, $message_array['key'], $message_array['set_time'], $redis
            )) {
                return $message_array['user_id'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function accessPermissionCheck($redis = null, $permission_type = 'login', $token = null)
    {
        $access_token = $token ? $token : GlobalFunctions::getAccessToken();
        if ($access_token) {
            if ($user_id = GlobalFunctions::verifyAccessToken(
                GlobalFunctions::decryptAccessToken($access_token), $redis, $permission_type
            )) {
                return $user_id;
            } else {
                return GlobalFunctions::generateResponse(GlobalConfig::TOKEN_VALIDATION_FAILURE);
            }
        } else {
            return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_ACCESS_TOKEN);
        }
    }

    public static function sendEmail($to_address, $subject, $html_body)
    {
        include_once ROOT_PATH . '/Libraries/AliyunDirectMail/aliyun-php-sdk-core/Config.php';
        //需要设置对应的region名称，如华东1（杭州）设为cn-hangzhou，新加坡Region设为ap-southeast-1，澳洲Region设为ap-southeast-2。
        $iClientProfile = DefaultProfile::getProfile(
            "cn-hangzhou",
            GlobalConfig::ALIYUN_ACCESS_KEY,
            GlobalConfig::ALIYUN_ACCESS_SECRET
        );
        //新加坡或澳洲region需要设置服务器地址，华东1（杭州）不需要设置。
        //$iClientProfile::addEndpoint("ap-southeast-1","ap-southeast-1","Dm","dm.ap-southeast-1.aliyuncs.com");
        //$iClientProfile::addEndpoint("ap-southeast-2","ap-southeast-2","Dm","dm.ap-southeast-2.aliyuncs.com");
        $client = new DefaultAcsClient($iClientProfile);
        $request = new Dm\SingleSendMailRequest();
        //新加坡或澳洲region需要设置SDK的版本，华东1（杭州）不需要设置。
        //$request->setVersion("2017-06-22");
        $request->setAccountName("verify@bandoristation.com");
        $request->setFromAlias("Bandori车站");
        $request->setAddressType(1);
        //$request->setTagName("控制台创建的标签");
        $request->setReplyToAddress("false");
        $request->setToAddress($to_address);
        //可以给多个收件人发送邮件，收件人之间用逗号分开,若调用模板批量发信建议使用BatchSendMailRequest方式
        //$request->setToAddress("邮箱1,邮箱2");
        $request->setSubject($subject);
        $request->setHtmlBody($html_body);
        try {
            $response = $client->getAcsResponse($request);
            //print_r($response);
        } catch (ClientException $e) {
            return false;
            //print_r($e->getErrorCode());
            //print_r($e->getErrorMessage());
        } catch (ServerException $e) {
            return false;
            //print_r($e->getErrorCode());
            //print_r($e->getErrorMessage());
        }
        return true;
    }

    public static function userIDMask($user_id)
    {
        if (strlen($user_id) == 5) {
            return substr($user_id, 0, 1) . '***' . substr($user_id, 4);
        } elseif (strlen($user_id) == 6) {
            return substr($user_id, 0, 1) . '****' . substr($user_id, 5);
        } elseif (strlen($user_id) == 7) {
            return substr($user_id, 0, 2) . '****' . substr($user_id, 6);
        } else {
            return substr($user_id, 0, 3) . '****' . substr($user_id, 7);
        }
    }

    public static function emailMask($email_address)
    {
        $email_array = explode('@', $email_address);
        $email_user = $email_array[0];
        $email_user_length = strlen($email_user);
        if ($email_user_length < 4) {
            $email_user_cipher = '';
            for ($i = 0; $i < $email_user_length; $i++) {
                $email_user_cipher .= '*';
            }
        } elseif ($email_user_length < 6) {
            $email_user_cipher = substr($email_user, 0, 1) . '**' . substr($email_user, -1, 1);
        } else {
            $email_user_cipher = substr($email_user, 0, 2) . '**' . substr($email_user, -2, 2);
        }
        return $email_user_cipher . '@' . $email_array[1];
    }

    public static function getMillisecondTime()
    {
        $time_array = explode(' ', microtime());
        return $time_array[1] * 1000  + intval($time_array[0] * 1000);
    }

    public static function classifyRoomNumberType($description, $type)
    {
        if ($type == '25' or $type == '18' or $type == '12' or $type == '7') {
            return strval($type);
        } else {
            if (
                stristr($description, '25w') or
                stristr($description, '25万')
            ) {
                return '25';
            } elseif (
                stristr($description, '18w') or
                stristr($description, '18万') or
                strstr($description, '大师房')
            ) {
                return '18';
            } elseif (
                stristr($description, '12w') or
                stristr($description, '12万') or
                strstr($description, '高手房')
            ) {
                return '12';
            } elseif (
                stristr($description, '7w') or
                stristr($description, '7万') or
                strstr($description, '常规房')
            ) {
                return '7';
            } else {
                return 'other';
            }
        }
    }

    public static function queryRoomNumber($latest_time = null)
    {
        $redis = DatabaseConfig::redisConnect();
        $timestamp = self::getMillisecondTime();
        $response_room_number_list = array();
        $redis_key = self::generateRedisKey('bandori_room_number');
        $room_number_list = $redis->lRange($redis_key, 0, -1);
        foreach ($room_number_list as $room_number) {
            $room_number_array = json_decode($room_number, true);
            if ($timestamp - $room_number_array['time'] <= 120000) {
                if ($latest_time) {
                    if ($room_number_array['time'] > $latest_time) {
                        $response_room_number_list[] = $room_number_array;
                    }
                } else {
                    $response_room_number_list[] = $room_number_array;
                }
            } else {
                $redis->lPop($redis_key);
            }
        }
        return $response_room_number_list;
    }

    public static function getRoomNumberDataSourceInfo($source, $action)
    {
        if ($action == GlobalConfig::CHECK_DATA_SOURCE) {
            if (array_key_exists($source, GlobalConfig::DATA_SOURCE_LIST)) {
                return true;
            } else {
                return false;
            }
        } elseif ($action == GlobalConfig::GET_SOURCE_TYPE) {
            return GlobalConfig::DATA_SOURCE_LIST[$source]['type'];
        } elseif ($action == GlobalConfig::GET_SOURCE_TOKEN) {
            return GlobalConfig::DATA_SOURCE_LIST[$source]['token'];
        } else {
            return '';
        }
    }

    public static function logRoomNumber()
    {
        $redis = DatabaseConfig::redisConnect();
        $timestamp = self::getMillisecondTime();
        $redis_key = self::generateRedisKey('bandori_room_number');
        $room_number_list = $redis->lRange($redis_key, 0, -1);
        $log_sql_list = array();
        foreach ($room_number_list as $room_number) {
            $room_number_array = json_decode($room_number, true);
            if ($timestamp - $room_number_array['time'] > 120000) {
                $log_sql_list[] =
                    "({$room_number_array['time']}, {$room_number_array['number']}, '" .
                    addslashes($room_number_array['raw_message']) . "', '{$room_number_array['source_info']['name']}', " .
                    "'{$room_number_array['type']}', '{$room_number_array['user_info']['type']}', " .
                    "{$room_number_array['user_info']['user_id']})";
                $redis->lPop($redis_key);
            }
        }
        if ($log_sql_list) {
            $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
            $sql_text = 
                "INSERT INTO room_number_log(timestamp, number, raw_message, source_name, type, user_type, user_id)
VALUES " . implode(', ', $log_sql_list);
            $sth = $dbh_bandori_station->prepare($sql_text);
            $sth->execute();
        }
    }

    public static function logChat()
    {
        $redis = DatabaseConfig::redisConnect();
        $timestamp = self::getMillisecondTime();
        $redis_key = self::generateRedisKey('chat');
        $message_list = $redis->lRange($redis_key, 0, -1);
        $log_sql_list = array();
        foreach ($message_list as $message) {
            $message_array = json_decode($message, true);
            if ($timestamp - $message_array['timestamp'] > 600000) {
                $log_sql_list[] =
                    "({$message_array['user_info']['user_id']}, " .
                    "{$message_array['timestamp']}, '" . addslashes($message_array['content']) . "')";
                $redis->lPop($redis_key);
            }
        }
        if ($log_sql_list) {
            $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
            $sql_text = "INSERT INTO chat_log(user_id, timestamp, content)
VALUES " . implode(', ', $log_sql_list);
            $sth = $dbh_bandori_station->prepare($sql_text);
            $sth->execute();
        }
    }
}
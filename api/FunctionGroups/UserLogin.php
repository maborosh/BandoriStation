<?php


namespace BS_API\FunctionGroups;


use BS_API\Utility\DatabaseConfig;
use BS_API\Utility\GlobalConfig;
use BS_API\Utility\GlobalFunctions;
use PDO;

class UserLogin
{
    public function login($request)
    {
        if (GlobalFunctions::getAccessToken()) {
            return GlobalFunctions::generateResponse(GlobalConfig::NOT_ALLOWED);
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('username', 'password'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        } elseif ($request['data']['username'] == '' or $request['data']['password'] == '') {
            return GlobalFunctions::generateResponse('Null username or password');
        }
        $request['data']['username'] = addslashes($request['data']['username']);
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        if (filter_var($request['data']['username'], FILTER_VALIDATE_EMAIL)) {
            $sql_text = "SELECT user_id, password, email, avatar 
FROM website_account WHERE email = '{$request['data']['username']}'";
        } else {
            $sql_text = "SELECT user_id, password, email, avatar 
FROM website_account WHERE username = '{$request['data']['username']}'";
        }
        $sth = $dbh_bandori_station->prepare($sql_text);
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$sql_result) {
            return GlobalFunctions::generateResponse(GlobalConfig::NONEXISTENT_USER);
        }
        $redis = DatabaseConfig::redisConnect();
        $user_ip = GlobalFunctions::getAccessIP();
        if (GlobalFunctions::callLimitCheck(
            GlobalFunctions::generateRedisKey(
                'login_limit',
                array('ip' => $user_ip)
            ),
            5,
            $redis
        )) {
            return GlobalFunctions::generateResponse('Too many logins');
        } else {
            if ($sql_result['password'] === GlobalFunctions::generatePasswordCipher(
                    $sql_result['user_id'], $request['data']['password'])) {
                $set_time = time();
                $random_key = GlobalFunctions::generateRandomCode(9);
                if ($sql_result['email']) {
                    GlobalFunctions::setAccessStatus(
                        $sql_result['user_id'], 'login', $set_time, $random_key, $redis
                    );
                    $sth = $dbh_bandori_station->prepare(
                        "INSERT INTO login_log(user_id, user_ip, time) 
VALUES ({$sql_result['user_id']}, '{$user_ip}', {$set_time})");
                    $sth->execute();
                    return GlobalFunctions::generateResponse(
                        array(
                            'token' => GlobalFunctions::generateAccessToken(array(
                                'user_id' => $sql_result['user_id'],
                                'type' => 'login',
                                'set_time' => $set_time,
                                'key' => $random_key
                            )),
                            'avatar' => $sql_result['avatar']
                        ),
                        true
                    );
                } else {
                    GlobalFunctions::setAccessStatus(
                        $sql_result['user_id'], 'verification', $set_time, $random_key, $redis
                    );
                    return GlobalFunctions::generateResponse(
                        array(
                            'token' => GlobalFunctions::generateAccessToken(array(
                                'user_id' => $sql_result['user_id'],
                                'type' => 'verification',
                                'set_time' => $set_time,
                                'key' => $random_key
                            )),
                            'redirect_to' => 'verify-email'
                        ),
                        true
                    );
                }
            } else {
                return GlobalFunctions::generateResponse('Wrong password');
            }
        }
    }

    public function logout()
    {
        if ($token = GlobalFunctions::getAccessToken()) {
            if (GlobalFunctions::verifyAccessToken(
                $message_array = GlobalFunctions::decryptAccessToken($token),
                $redis = DatabaseConfig::redisConnect()
            )) {
                GlobalFunctions::setAccessStatus(
                    $message_array['user_id'],
                    'login',
                    $message_array['set_time'],
                    $message_array['key'],
                    $redis,
                    false
                );
                return GlobalFunctions::generateResponse('', true);
            } else {
                return GlobalFunctions::generateResponse(GlobalConfig::TOKEN_VALIDATION_FAILURE);
            }
        } else {
            return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_ACCESS_TOKEN);
        }
    }

    public function signup($request)
    {
        if (GlobalFunctions::getAccessToken()) {
            return GlobalFunctions::generateResponse(GlobalConfig::NOT_ALLOWED);
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('username', 'password', 'email'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        } elseif ($request['data']['username'] == '') {
            return GlobalFunctions::generateResponse('Undefined username');
        } elseif (filter_var($request['data']['username'], FILTER_VALIDATE_EMAIL)) {
            return GlobalFunctions::generateResponse('Not allowed username format');
        } elseif (mb_strlen($request['data']['password']) < 6) {
            return GlobalFunctions::generateResponse('Short password');
        } elseif (!filter_var($request['data']['email'], FILTER_VALIDATE_EMAIL)) {
            return GlobalFunctions::generateResponse(GlobalConfig::INVALID_EMAIL);
        }
        $redis = DatabaseConfig::redisConnect();
        if (GlobalFunctions::callLimitCheck(
            GlobalFunctions::generateRedisKey(
                'signup_limit',
                array('ip' => GlobalFunctions::getAccessIP())
            ),
            5,
            $redis,
            true,
            1800
        )) {
            return GlobalFunctions::generateResponse('Too many signups');
        } else {
            $request['data']['username'] = addslashes($request['data']['username']);
            $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
            $sth = $dbh_bandori_station->prepare("SELECT username FROM website_account 
WHERE username = '{$request['data']['username']}' OR email = '{$request['data']['email']}'");
            $sth->execute();
            if ($sth->fetch(PDO::FETCH_ASSOC)) {
                return GlobalFunctions::generateResponse('Username or email already exists');
            } else {
                $set_time = time();
                $sth = $dbh_bandori_station->prepare("SELECT MAX(user_id) FROM website_account");
                $sth->execute();
                if ($max_user_id_result = $sth->fetch(PDO::FETCH_ASSOC)) {
                    $user_id = $max_user_id_result['MAX(user_id)'] + 1;
                } else {
                    $user_id = 1;
                }
                $password_hash = GlobalFunctions::generatePasswordCipher($user_id, $request['data']['password']);
                $sth = $dbh_bandori_station->prepare(
                    "INSERT INTO website_account(user_id, username, password, email, sign_up_time, avatar) 
VALUES ($user_id, '{$request['data']['username']}', '$password_hash', '', $set_time, '')");
                $sth->execute();
                $random_key = GlobalFunctions::generateRandomCode(9);
                GlobalFunctions::setAccessStatus($user_id, 'verification', $set_time, $random_key, $redis);

                $redis_key = GlobalFunctions::generateRedisKey(
                    'user_email',
                    array('user_id' => $user_id)
                );
                $redis->setex($redis_key, 600, $request['data']['email']);

                return GlobalFunctions::generateResponse(
                    array(
                        'token' => GlobalFunctions::generateAccessToken(array(
                            'user_id' => $user_id,
                            'type' => 'verification',
                            'set_time' => $set_time,
                            'key' => $random_key
                        )),
                        'redirect_to' => 'verify-email'
                    ),
                    true
                );
            }
        }
    }

    public function getCurrentEmail()
    {
        $redis = DatabaseConfig::redisConnect();
        if (is_array(
            $response = $user_id = GlobalFunctions::accessPermissionCheck($redis, 'verification')
        )) {
            return $response;
        }
        $redis_key = GlobalFunctions::generateRedisKey(
            'user_email',
            array('user_id' => $user_id)
        );
        $result = $redis->get($redis_key);
        if ($result) {
            $email = $result;
        } else {
            $email = '';
        }
        return GlobalFunctions::generateResponse(
            array('email' => $email),
            true
        );
    }

    public function changeEmail($request)
    {
        $redis = DatabaseConfig::redisConnect();
        if (is_array(
            $response = $user_id = GlobalFunctions::accessPermissionCheck($redis, 'verification')
        )) {
            return $response;
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('email'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        } elseif (!filter_var($request['data']['email'], FILTER_VALIDATE_EMAIL)) {
            return GlobalFunctions::generateResponse(GlobalConfig::INVALID_EMAIL);
        }
        $redis_key = GlobalFunctions::generateRedisKey(
            'user_email',
            array('user_id' => $user_id)
        );
        $redis->setex($redis_key, 600, $request['data']['email']);
        return GlobalFunctions::generateResponse(
            array('new_email' => $request['data']['email']),
            true
        );
    }

    public function sendEmailVerificationCode()
    {
        $redis = DatabaseConfig::redisConnect();
        if (is_array(
            $response = $user_id = GlobalFunctions::accessPermissionCheck(
                $redis, 'verification'
            )
        )) {
            return $response;
        } elseif (GlobalFunctions::callLimitCheck(
            GlobalFunctions::generateRedisKey(
                'send_verification_code_limit',
                array('user_id' => $user_id)
            ),
            1,
            $redis,
            true,
            60
        )) {
            return GlobalFunctions::generateResponse('Function suspend');
        }
        $redis_key = GlobalFunctions::generateRedisKey(
            'user_email',
            array('user_id' => $user_id)
        );
        $email = $redis->get($redis_key);
        if (!$email) {
            return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_EMAIL);
        }

        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT email FROM website_account 
WHERE user_id = {$user_id}");
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$sql_result) {
            return GlobalFunctions::generateResponse(GlobalConfig::NONEXISTENT_USER);
        } elseif ($sql_result['email']) {
            return GlobalFunctions::generateResponse(GlobalConfig::VERIFIED_EMAIL);
        }
        $verification_code = GlobalFunctions::generateRandomCode(6, 0, 9);
        $redis->setex(GlobalFunctions::generateRedisKey(
            'verification_code', array('user_id' => $user_id)
        ), 600, $verification_code);
        $email_content = '<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>【Bandori车站】邮箱安全验证</title>
</head>
<body>
<p><b>亲爱的用户：</b></p>
<p>您正在使用Bandori车站邮箱验证服务，本次请求的验证码为：<span style="font-size: 20px; color: orange"><b>' . $verification_code . '</b></span></p>
<p></p>
</body>
</html>';
        if (GlobalFunctions::sendEmail($email, '【Bandori车站】邮箱安全验证', $email_content)) {
            return GlobalFunctions::generateResponse(
                array('email' => GlobalFunctions::emailMask($email)),
                true
            );
        } else {
            return GlobalFunctions::generateResponse('Send failed');
        }
    }

    public function verifyEmail($request)
    {
        if (!$token = GlobalFunctions::getAccessToken()) {
            return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_ACCESS_TOKEN);
        } elseif (!GlobalFunctions::verifyAccessToken(
            $message_array = GlobalFunctions::decryptAccessToken($token),
            $redis = DatabaseConfig::redisConnect(),
            'verification'
        )) {
            return GlobalFunctions::generateResponse(GlobalConfig::TOKEN_VALIDATION_FAILURE);
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('verification_code'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        }
        $redis_key = GlobalFunctions::generateRedisKey(
            'user_email',
            array('user_id' => $message_array['user_id'])
        );
        $email = $redis->get($redis_key);
        if (!$email) {
            return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_EMAIL);
        }

        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT email 
FROM website_account WHERE user_id = {$message_array['user_id']}");
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$sql_result) {
            return GlobalFunctions::generateResponse(GlobalConfig::NONEXISTENT_USER);
        } elseif ($sql_result['email']) {
            return GlobalFunctions::generateResponse(GlobalConfig::VERIFIED_EMAIL);
        }
        $verification_code = $redis->get(GlobalFunctions::generateRedisKey(
            'verification_code', array('user_id' => $message_array['user_id'])
        ));
        if (!$verification_code) {
            return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_VERIFICATION_CODE);
        } elseif ($request['data']['verification_code'] !== $verification_code) {
            return GlobalFunctions::generateResponse(GlobalConfig::INVALID_VERIFICATION_CODE);
        }
        $sth = $dbh_bandori_station->prepare("UPDATE website_account 
SET email = '$email' WHERE user_id = {$message_array['user_id']}");
        $sth->execute();
        GlobalFunctions::setAccessStatus(
            $message_array['user_id'],
            'verification',
            $message_array['set_time'],
            $message_array['key'],
            $redis,
            false
        );
        $set_time = time();
        $random_key = GlobalFunctions::generateRandomCode(9);
        GlobalFunctions::setAccessStatus(
            $message_array['user_id'], 'login', $set_time, $random_key, $redis
        );
        $user_ip = GlobalFunctions::getAccessIP();
        $sth = $dbh_bandori_station->prepare(
            "INSERT INTO login_log(user_id, user_ip, time) 
VALUES ({$message_array['user_id']}, '{$user_ip}', {$set_time})");
        $sth->execute();
        return GlobalFunctions::generateResponse(
            array(
                'token' => GlobalFunctions::generateAccessToken(array(
                    'user_id' => $message_array['user_id'],
                    'type' => 'login',
                    'set_time' => $set_time,
                    'key' => $random_key
                ))
            ),
            true
        );
    }

    public function resetPasswordSendEmailVerificationCode($request)
    {
        if (GlobalFunctions::getAccessToken()) {
            return GlobalFunctions::generateResponse(GlobalConfig::NOT_ALLOWED);
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('email'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        } elseif (!filter_var($request['data']['email'], FILTER_VALIDATE_EMAIL)) {
            return GlobalFunctions::generateResponse(GlobalConfig::INVALID_EMAIL);
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT user_id FROM website_account 
WHERE email = '{$request['data']['email']}'");
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$sql_result) {
            return GlobalFunctions::generateResponse(GlobalConfig::NONEXISTENT_USER);
        }
        $redis = DatabaseConfig::redisConnect();
        $redis_key = GlobalFunctions::generateRedisKey(
            'send_verification_code_limit',
            array('user_id' => $sql_result['user_id'])
        );
        if (GlobalFunctions::callLimitCheck(
            $redis_key,
            1,
            $redis,
            true,
            60
        )) {
            return GlobalFunctions::generateResponse('Function suspend');
        }
        $verification_code = GlobalFunctions::generateRandomCode(6, 0, 9);
        $redis->setex($redis_key, 600, json_encode(
            array('email' => $request['data']['email'], 'verification_code' => $verification_code)
        ));
        $email_content = '<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>【Bandori车站】重置账号密码</title>
</head>
<body>
<p><b>亲爱的用户：</b></p>
<p>您正在使用Bandori车站重置账号密码服务，本次请求的验证码为：<span style="font-size: 20px; color: orange"><b>' . $verification_code . '</b></span></p>
<p></p>
</body>
</html>';
        if (GlobalFunctions::sendEmail($request['data']['email'], '【Bandori车站】重置账号密码', $email_content)) {
            return GlobalFunctions::generateResponse(
                array('email' => GlobalFunctions::emailMask($request['data']['email'])),
                true
            );
        } else {
            return GlobalFunctions::generateResponse('Send failed');
        }
    }

    public function resetPasswordVerifyEmail($request)
    {
        if (GlobalFunctions::getAccessToken()) {
            return GlobalFunctions::generateResponse(GlobalConfig::NOT_ALLOWED);
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('email', 'verification_code'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        } elseif ($request['data']['verification_code'] == '') {
            return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_VERIFICATION_CODE);
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT user_id FROM website_account 
WHERE email = '{$request['data']['email']}'");
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$sql_result) {
            return GlobalFunctions::generateResponse(GlobalConfig::NONEXISTENT_USER);
        }
        $redis = DatabaseConfig::redisConnect();
        $redis_key = GlobalFunctions::generateRedisKey(
            'send_verification_code_limit',
            array('user_id' => $sql_result['user_id'])
        );
        if (!$redis_value = $redis->get($redis_key)) {
            return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_VERIFICATION_REQUEST);
        }
        $redis_value_array = json_decode($redis_value, true);
        if ($redis_value_array['email'] !== $request['data']['email'] or
            $redis_value_array['verification_code'] !== $request['data']['verification_code']) {
            return GlobalFunctions::generateResponse(GlobalConfig::INVALID_VERIFICATION_CODE);
        }
        $set_time = time();
        $random_key = GlobalFunctions::generateRandomCode(9);
        GlobalFunctions::setAccessStatus(
            $sql_result['user_id'], 'resetPassword', $set_time, $random_key, $redis
        );
        return GlobalFunctions::generateResponse(
            array(
                'token' => GlobalFunctions::generateAccessToken(array(
                    'user_id' => $sql_result['user_id'],
                    'type' => 'resetPassword',
                    'set_time' => $set_time,
                    'key' => $random_key
                ))
            ),
            true
        );
    }

    public function resetPassword($request)
    {
        $redis = DatabaseConfig::redisConnect();
        if (is_array(
            $response = $user_id = GlobalFunctions::accessPermissionCheck($redis, 'resetPassword')
        )) {
            return $response;
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('password'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $password_hash = GlobalFunctions::generatePasswordCipher($user_id, $request['data']['password']);
        $sth = $dbh_bandori_station->prepare("UPDATE website_account SET password = '$password_hash' 
WHERE user_id = {$user_id}");
        $sth->execute();
        $redis_key = GlobalFunctions::generateRedisKey(
            'send_verification_code_limit',
            array('user_id' => $user_id)
        );
        $redis->del($redis_key);
        return GlobalFunctions::generateResponse('', true);
    }
}
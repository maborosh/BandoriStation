<?php


namespace BS_API\FunctionGroups;


use BS_API\Utility\DatabaseConfig;
use BS_API\Utility\GlobalConfig;
use BS_API\Utility\GlobalFunctions;
use PDO;

class AccountManage
{
    public function getInitialData()
    {
        if (is_array($response = $user_id = GlobalFunctions::accessPermissionCheck())) {
            return $response;
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT username, email, qq 
FROM website_account WHERE user_id = $user_id");
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        $sql_result['email'] = $sql_result['email'] ? GlobalFunctions::emailMask($sql_result['email']) : '';
        $sql_result['qq'] = $sql_result['qq'] ? GlobalFunctions::userIDMask($sql_result['qq']) : '';
        return GlobalFunctions::generateResponse(
            array(
                'username' => $sql_result['username'],
                'email' => $sql_result['email'],
                'qq' => $sql_result['qq']
            ),
            true
        );
    }

    public function updateAvatar($request)
    {
        if (is_array($response = $user_id = GlobalFunctions::accessPermissionCheck())) {
            return $response;
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('image'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        } elseif (substr($request['data']['image'], 0, 22) !== 'data:image/png;base64,') {
            return GlobalFunctions::generateResponse('Invalid image format');
        }
        $image_bin = base64_decode(substr($request['data']['image'], 22));
        $image_name = md5($image_bin) . '.png';
        file_put_contents(ROOT_PATH . "/Assets/images/user-avatar/$image_name", $image_bin);
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("UPDATE `website_account` SET `avatar` = '{$image_name}' 
WHERE `user_id` = {$user_id}");
        $sth->execute();
        return GlobalFunctions::generateResponse(array('avatar' => $image_name), true);
    }

    public function updateUsername($request)
    {
        if (is_array($response = $user_id = GlobalFunctions::accessPermissionCheck())) {
            return $response;
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('username'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        } elseif ($request['data']['username'] == '') {
            return GlobalFunctions::generateResponse('Undefined username');
        } elseif (filter_var($request['data']['username'], FILTER_VALIDATE_EMAIL)) {
            return GlobalFunctions::generateResponse('Not allowed username format');
        }
        $username = addslashes($request['data']['username']);
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT user_id FROM website_account 
WHERE username = '$username'");
        $sth->execute();
        if ($sth->fetch(PDO::FETCH_ASSOC)) {
            return GlobalFunctions::generateResponse('Username already exists');
        }
        $sth = $dbh_bandori_station->prepare("UPDATE `website_account` 
SET `username` = '$username' WHERE `user_id` = $user_id");
        $sth->execute();
        return GlobalFunctions::generateResponse(array('username' => $request['data']['username']), true);
    }

    public function updatePassword($request)
    {
        if (is_array($response = $user_id = GlobalFunctions::accessPermissionCheck())) {
            return $response;
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('password', 'new_password'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        } elseif ($request['data']['password'] == '' or $request['data']['new_password'] == '') {
            return GlobalFunctions::generateResponse('Undefined password');
        } elseif (mb_strlen($request['data']['new_password']) < 6) {
            return GlobalFunctions::generateResponse('Short password');
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT password FROM website_account WHERE user_id = $user_id");
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        if ($sql_result['password'] ===
            GlobalFunctions::generatePasswordCipher($user_id, $request['data']['password'])) {
            $password_hash = GlobalFunctions::generatePasswordCipher($user_id, $request['data']['new_password']);
            $sth = $dbh_bandori_station->prepare("UPDATE `website_account` SET `password` = '$password_hash' 
WHERE `user_id` = $user_id");
            $sth->execute();
            return GlobalFunctions::generateResponse('', true);
        } else {
            return GlobalFunctions::generateResponse('Wrong password');
        }
    }

    public function updateEmailSendVerificationCode($request)
    {
        $redis = DatabaseConfig::redisConnect();
        if (is_array($response = $user_id = GlobalFunctions::accessPermissionCheck($redis))) {
            return $response;
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('email'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        } elseif (!filter_var($request['data']['email'], FILTER_VALIDATE_EMAIL)) {
            return GlobalFunctions::generateResponse(GlobalConfig::INVALID_EMAIL);
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT email FROM website_account 
WHERE email = '{$request['data']['email']}'");
        $sth->execute();
        if ($sth->fetch(PDO::FETCH_ASSOC)) {
            return GlobalFunctions::generateResponse(GlobalConfig::DUPLICATE_EMAIL);
        }
        $redis_key = GlobalFunctions::generateRedisKey(
            'send_verification_code_limit',
            array('user_id' => $user_id)
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
    <title>【Bandori车站】邮箱安全验证</title>
</head>
<body>
<p><b>亲爱的用户：</b></p>
<p>您正在使用Bandori车站邮箱验证服务，本次请求的验证码为：<span style="font-size: 20px; color: orange"><b>' . $verification_code . '</b></span></p>
<p></p>
</body>
</html>';
        if (GlobalFunctions::sendEmail($request['data']['email'], '【Bandori车站】邮箱安全验证', $email_content)) {
            return GlobalFunctions::generateResponse(
                array('email' => GlobalFunctions::emailMask($request['data']['email'])),
                true
            );
        } else {
            return GlobalFunctions::generateResponse('Send failed');
        }
    }

    public function updateEmailVerifyEmail($request)
    {
        $redis = DatabaseConfig::redisConnect();
        if (is_array($response = $user_id = GlobalFunctions::accessPermissionCheck($redis))) {
            return $response;
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('verification_code'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        } elseif ($request['data']['verification_code'] == '') {
            return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_VERIFICATION_CODE);
        }
        $redis_key = GlobalFunctions::generateRedisKey(
            'send_verification_code_limit',
            array('user_id' => $user_id)
        );
        if (!$redis_value = $redis->get($redis_key)) {
            return GlobalFunctions::generateResponse(GlobalConfig::UNDEFINED_VERIFICATION_REQUEST);
        }
        $redis_value_array = json_decode($redis_value, true);
        if ($redis_value_array['verification_code'] !== $request['data']['verification_code']) {
            return GlobalFunctions::generateResponse(GlobalConfig::INVALID_VERIFICATION_CODE);
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("UPDATE website_account 
SET email = '{$redis_value_array['email']}' WHERE user_id = {$user_id}");
        $sth->execute();
        return GlobalFunctions::generateResponse(
            array('email' => GlobalFunctions::emailMask($redis_value_array['email'])),
            true
        );
    }

    public function bindQQ($request)
    {
        if (is_array($response = $user_id = GlobalFunctions::accessPermissionCheck())) {
            return $response;
        } elseif (!GlobalFunctions::checkArrayKeys($request['data'], array('qq'))) {
            return GlobalFunctions::generateResponse(GlobalConfig::MISSING_PARAMETERS);
        }
        $qq = intval($request['data']['qq']);
        if (!preg_match('/^[1-9][0-9]{4,10}$/', strval($qq))) {
            return GlobalFunctions::generateResponse('Invalid qq');
        }
        $dbh_bandori_station = DatabaseConfig::mysqlDBHelper('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT user_id FROM website_account 
WHERE qq = $qq");
        $sth->execute();
        if ($sth->fetch(PDO::FETCH_ASSOC)) {
            return GlobalFunctions::generateResponse('QQ already exists');
        }
        $sth = $dbh_bandori_station->prepare("UPDATE `website_account` 
SET `qq` = $qq WHERE `user_id` = $user_id");
        $sth->execute();
        return GlobalFunctions::generateResponse(
            array('qq' => GlobalFunctions::userIDMask($qq)),
            true
        );
    }
}
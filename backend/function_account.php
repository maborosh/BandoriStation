<?php

function account_initialize()
{
    session_start();
    if (!isset($_SESSION['login'])) {
        return array(
            'status' => 'failure',
            'response' => 'login_check_failure'
        );
    }
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("SELECT username, email, email_verification_flag, qq FROM website_account WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    if ($sql_result['email_verification_flag']) {
        $sql_result['email'] = email_mask($sql_result['email']);
    } else {
        $sql_result['email'] = null;
    }
    if ($sql_result['qq']) {
        $sql_result['qq'] = qq_mask($sql_result['qq']);
    } else {
        $sql_result['qq'] = null;
    }
    return array(
        'status' => 'success',
        'response' => array(
            'username' => $sql_result['username'],
            'email' => $sql_result['email'],
            'qq' => $sql_result['qq']
        )
    );
}

function verify_email_change_email($email)
{
    session_start();
    if (!isset($_SESSION['user_id'])) {
        return array(
            'status' => 'failure',
            'response' => 'user_check_failure'
        );
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return array(
            'status' => 'failure',
            'response' => 'invalid_email'
        );
    }
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("SELECT email FROM website_account WHERE email = '$email'");
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    if ($sql_result) {
        return array(
            'status' => 'failure',
            'response' => 'duplicate_email'
        );
    }
    $sth = $dbh_bandori_station->prepare("SELECT email_verification_flag FROM website_account WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    if ($sql_result['email_verification_flag']) {
        return array(
            'status' => 'failure',
            'response' => 'email_verified'
        );
    } else {
        $sth = $dbh_bandori_station->prepare("UPDATE website_account SET email = '$email' WHERE user_id = " . $_SESSION['user_id']);
        $sth->execute();
        return array(
            'status' => 'success',
            'response' => ''
        );
    }
}

function verify_email_send_verification_code($email = null)
{
    session_start();
    if (!isset($_SESSION['user_id'])) {
        return array(
            'status' => 'failure',
            'response' => 'user_check_failure'
        );
    }
    $dbh_bandori_station = db_select('bandori_station');
    if ($email) {
        $sth = $dbh_bandori_station->prepare("SELECT user_id FROM website_account WHERE email = '$email' AND email_verification_flag = 1");
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        if ($sql_result) {
            return array(
                'status' => 'failure',
                'response' => 'duplicate_email'
            );
        }
    }
    $sth = $dbh_bandori_station->prepare("SELECT email, email_verification_flag FROM website_account WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    if (!$email) {
        $email = $sql_result['email'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return array(
            'status' => 'failure',
            'response' => 'invalid_email'
        );
    } elseif ($sql_result['email_verification_flag']) {
        return array(
            'status' => 'failure',
            'response' => 'verified_email'
        );
    }
    $verification_code = generate_random_code(6, 0, 9);
    $sth = $dbh_bandori_station->prepare("UPDATE website_account SET email = '$email', email_verification_code = '$verification_code' WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();

    require ROOT_PATH . '/functions/send_email.php';
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
    if (send_email('verify@bandoristation.com', $email, '【Bandori车站】邮箱安全验证', $email_content)) {
        return array(
            'status' => 'success',
            'response' => array(
                'email' => email_mask($email)
            )
        );
    } else {
        return array(
            'status' => 'failure',
            'response' => 'send_failed'
        );
    }
}

function verify_email_verify_verification_code($verification_code)
{
    session_start();
    if (!isset($_SESSION['user_id'])) {
        return array(
            'status' => 'failure',
            'response' => 'user_check_failure'
        );
    }
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("SELECT email, email_verification_code, email_verification_flag FROM website_account WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    if (!filter_var($sql_result['email'], FILTER_VALIDATE_EMAIL)) {
        return array(
            'status' => 'failure',
            'response' => 'invalid_email'
        );
    } elseif ($sql_result['email_verification_flag']) {
        return array(
            'status' => 'failure',
            'response' => 'verified_email'
        );
    } elseif ($verification_code != $sql_result['email_verification_code']) {
        return array(
            'status' => 'failure',
            'response' => 'wrong_verification_code'
        );
    }
    $sth = $dbh_bandori_station->prepare("UPDATE website_account SET email_verification_code = NULL, email_verification_flag = 1 WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();
    return array(
        'status' => 'success',
        'response' => array(
            'email' => email_mask($sql_result['email'])
        )
    );
}

function account_upload_user_avatar_image()
{
    session_start();
    if (!isset($_SESSION['login'])) {
        return array(
            'status' => 'failure',
            'response' => 'login_check_failure'
        );
    } elseif (!isset($_FILES['image'])) {
        return array(
            'status' => 'failure',
            'response' => 'file_not_found'
        );
    } elseif ($_FILES['image']['error'] > 0) {
        return array(
            'status' => 'failure',
            'response' => 'file_error'
        );
    } elseif ($_FILES['image']['type'] != 'image/png') {
        return array(
            'status' => 'failure',
            'response' => 'illegal_format'
        );
    } elseif ($_FILES['image']['size'] > 1048576) {
        return array(
            'status' => 'failure',
            'response' => 'file_oversize'
        );
    } else {
        $dbh_bandori_station = db_select('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT avatar FROM website_account WHERE user_id = " . $_SESSION['user_id']);
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        if ($sql_result['avatar'] and file_exists(ROOT_PATH . '/assets/user_avatar/' . $sql_result['avatar'])) {
            unlink(ROOT_PATH . '/assets/user_avatar/' . $sql_result['avatar']);
        }

        $file_name = sha1_file($_FILES['image']['tmp_name']) . '.png';
        $move_status = move_uploaded_file($_FILES['image']['tmp_name'], ROOT_PATH . '/assets/user_avatar/' . $file_name);
        if (!$move_status) {
            return array(
                'status' => 'failure',
                'response' => 'image_upload_failure'
            );
        }

        $sth = $dbh_bandori_station->prepare("UPDATE website_account SET avatar = '$file_name' WHERE user_id = " . $_SESSION['user_id']);
        $sth->execute();

        return array(
            'status' => 'success',
            'response' => array(
                'file_name' => $file_name
            )
        );
    }
}

function account_set_username($username)
{
    session_start();
    if (!isset($_SESSION['login'])) {
        return array(
            'status' => 'failure',
            'response' => 'login_check_failure'
        );
    }
    if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
        return array(
            'status' => 'failure',
            'response' => 'username_is_email'
        );
    }
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("SELECT user_id, username FROM website_account WHERE username = '$username' AND user_id != " . $_SESSION['user_id']);
    $sth->execute();
    $sql_result = $sth->fetchAll(PDO::FETCH_ASSOC);
    if ($sql_result) {
        return array(
            'status' => 'failure',
            'response' => 'duplicate_username'
        );
    } else {
        $sth = $dbh_bandori_station->prepare("UPDATE website_account SET username = '$username' WHERE user_id = " . $_SESSION['user_id']);
        $sth->execute();
        return array(
            'status' => 'success',
            'response' => array(
                'username' => $username
            )
        );
    }
}

function account_set_password($old_password, $new_password)
{
    session_start();
    if (!isset($_SESSION['login'])) {
        return array(
            'status' => 'failure',
            'response' => 'login_check_failure'
        );
    }
    $password_hash = generate_password_cipher($_SESSION['user_id'], $old_password);
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("SELECT password FROM website_account WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    if ($sql_result) {
        if ($sql_result['password'] == $password_hash) {
            $password_hash = generate_password_cipher($_SESSION['user_id'], $new_password);
            $sth = $dbh_bandori_station->prepare("UPDATE website_account SET password = '$password_hash' WHERE user_id = " . $_SESSION['user_id']);
            $sth->execute();
            $timestamp = time();
            return array(
                'status' => 'success',
                'response' => array(
                    'login_time' => $timestamp,
                    'token' => generate_token($_SESSION['user_id'], $password_hash, $timestamp)
                )
            );
        } else {
            return array(
                'status' => 'failure',
                'response' => 'wrong_password'
            );
        }
    } else {
        return array(
            'status' => 'failure',
            'response' => 'undefined_user_id'
        );
    }
}

function account_unbind_email_send_verification_code()
{
    session_start();
    if (!isset($_SESSION['login'])) {
        return array(
            'status' => 'failure',
            'response' => 'login_check_failure'
        );
    }
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("SELECT email, email_verification_flag FROM website_account WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    if (!$sql_result['email_verification_flag']) {
        return array(
            'status' => 'failure',
            'response' => 'unverified_email'
        );
    }
    $verification_code = generate_random_code(6, 0, 9);
    $sth = $dbh_bandori_station->prepare("UPDATE website_account SET email_verification_code = '$verification_code' WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();

    require ROOT_PATH . '/functions/send_email.php';
    $email_content = '<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>【Bandori车站】解绑邮箱验证</title>
</head>
<body>
<p><b>亲爱的用户：</b></p>
<p>您正在Bandori车站进行解绑邮箱操作，本次请求的验证码为：<span style="font-size: 20px; color: orange"><b>' . $verification_code . '</b></span></p>
<p></p>
</body>
</html>';
    if (send_email('verify@bandoristation.com', $sql_result['email'], '【Bandori车站】解绑邮箱验证', $email_content)) {
        return array(
            'status' => 'success',
            'response' => array(
                'email' => email_mask($sql_result['email'])
            )
        );
    } else {
        return array(
            'status' => 'failure',
            'response' => 'send_failed'
        );
    }
}

function account_unbind_email_verify_verification_code($verification_code)
{
    session_start();
    if (!isset($_SESSION['login'])) {
        return array(
            'status' => 'failure',
            'response' => 'login_check_failure'
        );
    }
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("SELECT email, email_verification_code, email_verification_flag FROM website_account WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    if (!$sql_result['email_verification_flag']) {
        return array(
            'status' => 'failure',
            'response' => 'unverified_email'
        );
    } elseif ($verification_code != $sql_result['email_verification_code']) {
        return array(
            'status' => 'failure',
            'response' => 'wrong_verification_code'
        );
    }
    $sth = $dbh_bandori_station->prepare("UPDATE website_account SET email_verification_code = NULL, email_verification_flag = 0 WHERE user_id = " . $_SESSION['user_id']);
    $sth->execute();
    return array(
        'status' => 'success',
        'response' => ''
    );
}

function account_bind_qq($qq)
{
    session_start();
    if (!isset($_SESSION['login'])) {
        return array(
            'status' => 'failure',
            'response' => 'login_check_failure'
        );
    }
    $dbh_bandori_station = db_select('bandori_station');
    if ($qq) {
        if (preg_match('/^[1-9][0-9]{4,}$/', $qq)) {
            $sth = $dbh_bandori_station->prepare("SELECT user_id FROM website_account WHERE qq = $qq AND user_id != " . $_SESSION['user_id']);
            $sth->execute();
            $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
            if ($sql_result) {
                return array(
                    'status' => 'failure',
                    'response' => 'duplicate_qq_number'
                );
            }

            $sth = $dbh_bandori_station->prepare("UPDATE website_account SET qq = $qq WHERE user_id = " . $_SESSION['user_id']);
            $sth->execute();
            return array(
                'status' => 'success',
                'response' => array(
                    'qq' => qq_mask($qq)
                )
            );
        } else {
            return array(
                'status' => 'failure',
                'response' => 'wrong_qq_number'
            );
        }
    } else {
        $sth = $dbh_bandori_station->prepare("UPDATE website_account SET qq = NULL WHERE user_id = " . $_SESSION['user_id']);
        $sth->execute();
        return array(
            'status' => 'success',
            'response' => ''
        );
    }
}

function account_reset_password_input_email($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return array(
            'status' => 'failure',
            'response' => 'invalid_email'
        );
    }
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("SELECT user_id FROM website_account WHERE email = '$email' AND email_verification_flag = 1");
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    if (!$sql_result) {
        return array(
            'status' => 'failure',
            'response' => 'unregistered_email'
        );
    }
    session_start();
    $_SESSION['reset_password_verification'] = array(
        'user_id' => $sql_result['user_id'],
        'email' => $email,
        'verification_code' => generate_random_code(6, 0, 9)
    );

    return array(
        'status' => 'success',
        'response' => ''
    );
}

function account_reset_password_send_verification_code()
{
    session_start();
    if (empty($_SESSION['reset_password_verification'])) {
        return array(
            'status' => 'failure',
            'response' => 'empty_verification_request'
        );
    }
    require ROOT_PATH . '/functions/send_email.php';
    $email_content = '<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>【Bandori车站】重置密码验证</title>
</head>
<body>
<p><b>亲爱的用户：</b></p>
<p>您正在重置Bandori车站账号密码，本次请求的验证码为：<span style="font-size: 20px; color: orange"><b>' . $_SESSION['reset_password_verification']['verification_code'] . '</b></span></p>
<p></p>
</body>
</html>';
    if (send_email('verify@bandoristation.com', $_SESSION['reset_password_verification']['email'], '【Bandori车站】重置密码验证', $email_content)) {
        return array(
            'status' => 'success',
            'response' => array(
                'email' => email_mask($_SESSION['reset_password_verification']['email'])
            )
        );
    } else {
        return array(
            'status' => 'failure',
            'response' => 'send_failed'
        );
    }
}

function account_reset_password($new_password, $verification_code)
{
    session_start();
    if (empty($_SESSION['reset_password_verification'])) {
        return array(
            'status' => 'failure',
            'response' => 'empty_verification_request'
        );
    } elseif ($verification_code != $_SESSION['reset_password_verification']['verification_code']) {
        return array(
            'status' => 'failure',
            'response' => 'wrong_verification_code'
        );
    }
    $dbh_bandori_station = db_select('bandori_station');
    $password_hash = generate_password_cipher($_SESSION['reset_password_verification']['user_id'], $new_password);
    $sth = $dbh_bandori_station->prepare("UPDATE website_account SET password = '$password_hash' WHERE user_id = " . $_SESSION['reset_password_verification']['user_id']);
    $sth->execute();
    return array(
        'status' => 'success',
        'response' => ''
    );
}
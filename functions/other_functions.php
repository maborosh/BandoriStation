<?php

function auto_login()
{
    if (isset($_COOKIE['user_id']) and isset($_COOKIE['login_time']) and isset($_COOKIE['token'])) {
        $dbh_bandori_station = db_select('bandori_station');
        $sth = $dbh_bandori_station->prepare("SELECT user_id, password, avatar FROM website_account WHERE user_id = " . $_COOKIE['user_id']);
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        if ($sql_result and binding_check($sql_result['user_id'])) {
            if ($_COOKIE['token'] == generate_token($sql_result['user_id'], $sql_result['password'], $_COOKIE['login_time'])) {
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $sql_result['user_id'];
                update_user_login_data($sql_result['user_id'], $sql_result['password'], $sql_result['avatar']);
                return true;
            }
        }
    }
    clear_cookie();
    return false;
}

function clear_cookie()
{
    $timestamp = time() - 1;
    setcookie('user_id', '', $timestamp);
    setcookie('login_time', '', $timestamp);
    setcookie('token', '', $timestamp);
    setcookie('user_avatar', '', $timestamp);
}

function generate_password_cipher($user_id, $password)
{
    // Encrypt
    $plain_text = '';
    return hash('sha256', $plain_text);
}

function generate_token($user_id, $password, $time)
{
    // Encrypt
    $plain_text = '';
    return hash('sha256', $plain_text);
}

function binding_check($user_id)
{
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("SELECT email, email_verification_flag FROM website_account WHERE user_id = " . $user_id);
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    if ($sql_result and $sql_result['email'] and $sql_result['email_verification_flag']) {
        return true;
    } else {
        return false;
    }
}

function update_user_login_data($user_id, $password, $avatar)
{
    $dbh_bandori_station = db_select('bandori_station');
    if (!$password or !$avatar) {
        $sth = $dbh_bandori_station->prepare("SELECT password, avatar FROM website_account WHERE user_id = " . $user_id);
        $sth->execute();
        $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
        if ($sql_result) {
            $password = $sql_result['password'];
            $avatar = $sql_result['avatar'];
        } else {
            return;
        }
    }
    $timestamp = time();
    $cookie_expiration_time = $timestamp + 2592000;
    $sth = $dbh_bandori_station->prepare("UPDATE website_account SET login_time = $timestamp WHERE user_id = $user_id");
    $sth->execute();
    setcookie('user_id', $user_id, $cookie_expiration_time);
    setcookie('login_time', $timestamp, $cookie_expiration_time);
    setcookie('token', generate_token($user_id, $password, $timestamp), $cookie_expiration_time);
    setcookie('user_avatar', $avatar, $cookie_expiration_time);
}

function generate_random_code($length, $range_from = 0, $range_to = 61)
{
    $char_array = array(
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
    );
    $response = '';
    for ($i = 0; $i < $length; $i++) {
        $response .= $char_array[rand($range_from, $range_to)];
    }
    return $response;
}

function qq_mask($qq)
{
    if (strlen($qq) == 5) {
        return substr($qq, 0, 1) . '***' . substr($qq, 4);
    } elseif (strlen($qq) == 6) {
        return substr($qq, 0, 1) . '****' . substr($qq, 5);
    } elseif (strlen($qq) == 7) {
        return substr($qq, 0, 2) . '****' . substr($qq, 6);
    } else {
        return substr($qq, 0, 3) . '****' . substr($qq, 7);
    }
}

function email_mask($email_address)
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

function classify_room_number_type($description)
{
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
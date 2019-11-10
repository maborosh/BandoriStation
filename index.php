<?php
require 'config.php';
require ROOT_PATH . '/functions/error_handler.php';
require ROOT_PATH . '/functions/db_select.php';
require ROOT_PATH . '/functions/other_functions.php';

session_start();

if (!isset($_SESSION['login'])) {
    auto_login();
}

$query_string = $_SERVER['QUERY_STRING'];
$query_array = explode('/', $query_string);
if ($query_array[0] == '') {
    include ROOT_PATH . '/pages/home.php';
} elseif ($query_array[0] == 'login') {
    include ROOT_PATH . '/pages/login.php';
} elseif ($query_array[0] == 'login_check') {
    include ROOT_PATH . '/pages/login_check.php';
} elseif ($query_array[0] == 'logout') {
    session_destroy();
    clear_cookie();
    header('location: /');
} elseif ($query_array[0] == 'sign_up') {
    include ROOT_PATH . '/pages/sign_up.php';
} elseif ($query_array[0] == 'sign_up_check') {
    include ROOT_PATH . '/pages/sign_up_check.php';
} elseif ($query_array[0] == 'verify_email') {
    include ROOT_PATH . '/pages/verify_email.php';
} elseif ($query_array[0] == 'account') {
    include ROOT_PATH . '/pages/account.php';
} elseif ($query_array[0] == 'reset_password') {
    include ROOT_PATH . '/pages/reset_password.php';
} else {
    include ROOT_PATH . '/pages/error_hint.php';
}
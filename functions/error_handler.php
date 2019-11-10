<?php

function _error_handler($err_number, $err_str ,$err_file, $err_line) //自定义的错误处理方法
{
    $error_message = "Error Number: $err_number, Error Message: $err_str, Error File: $err_file, Error Line: $err_line";
    record_error($error_message);
    exit();
}

set_error_handler('_error_handler', E_ALL | E_STRICT);  // 注册错误处理方法来处理所有错误

function _exception_handler(Throwable $e)
{
    if ($e instanceof Error) {
        $error_message = "Catch Error: " . $e->getCode() . ', Error Message: ' . $e->getMessage() . ', Error File: ' . $e->getFile() . ', Error Line: ' . $e->getLine() . ', Error Trace: ' . $e->getTraceAsString();
    } else {
        $error_message = "Catch Exception: " . $e->getCode() . ', Error Message: ' . $e->getMessage() . ', Error File: ' . $e->getFile() . ', Error Line: ' . $e->getLine() . ', Error Trace: ' . $e->getTraceAsString();
    }
    record_error($error_message);
    exit();
}

set_exception_handler('_exception_handler');    // 注册异常处理方法来捕获异常

function record_error($error_message)
{
    $error_message = addslashes($error_message);
    $dbh_bandori_station = db_select('bandori_station');
    $sth = $dbh_bandori_station->prepare("SELECT MAX(id) FROM error_log");
    $sth->execute();
    $sql_result = $sth->fetch(PDO::FETCH_ASSOC);
    if (!$sql_result['MAX(id)']) {
        $id = 1;
    } else {
        $id =  $sql_result['MAX(id)'] + 1;
    }
    $sth = $dbh_bandori_station->prepare("INSERT INTO `error_log` (`id`, `detail`) VALUES ('$id', '$error_message')");
    $sth->execute();
}
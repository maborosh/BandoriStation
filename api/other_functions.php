<?php

function micro_second_time()
{
    $time_array = explode(' ', microtime());
    return $time_array[1] * 1000  + intval($time_array[0] * 1000);
}

function data_source_check($source, $type, $token = '')
{
    $data_source_array = array();
    if ($type == 1) {
        if (array_key_exists($source, $data_source_array)) {
            return true;
        } else {
            return false;
        }
    } else {
        if ($data_source_array[$source] == $token) {
            return true;
        } else {
            return false;
        }
    }
}
<?php
require 'other_functions.php';

if (isset($_GET['function'])) {
    $function = $_GET['function'];
    if ($function == 'query_room_number') {
        require 'query_room_number.php';
        $response = array(
            'status' => 'success',
            'data' => query_room_number()
        );
    } elseif ($function == 'submit_room_number') {
        require 'submit_room_number.php';
        $response = submit_room_number();
    } else {
        $response = array(
            'status' => 'undefined_function'
        );
    }
} else {
    $response = array(
        'status' => 'access_denied'
    );
}

echo json_encode($response);
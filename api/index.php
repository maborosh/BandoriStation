<?php
require 'other_functions.php';

if (isset($_GET['function'])) {
    $function = $_GET['function'];
    if ($function == 'query_room_number') {
        require 'query_room_number.php';
        $response = query_room_number();
    } elseif ($function == 'submit_room_number') {
        require 'submit_room_number.php';
        $response = submit_room_number();
    } else {
        $response = array(
            'status' => 'failure',
            'response' => 'undefined_function'
        );
    }
} else {
    $response = array(
        'status' => 'failure',
        'response' => 'lack_function_parameter'
    );
}

echo json_encode($response);

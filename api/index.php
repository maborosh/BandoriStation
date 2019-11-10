<?php
require '../config.php';
require ROOT_PATH . '/functions/error_handler.php';
require ROOT_PATH . '/functions/db_select.php';
require ROOT_PATH . '/functions/other_functions.php';
require ROOT_PATH . '/api/other_functions.php';

$post_request = file_get_contents('php://input');
if ($post_request) {
    $request = json_decode($post_request, true);
    if (!$request) {
        echo json_encode(array(
            'status' => 'failure',
            'response' => 'error_post_format'
        ));
        exit();
    }
    $request['method'] = 'POST';
} else {
    $request = $_GET;
    $request['method'] = 'GET';
}

if (isset($request['function'])) {
    if ($request['function'] == 'query_room_number') {
        require ROOT_PATH . '/api/query_room_number.php';
        if (isset($request['latest_time'])) {
            $latest_time = $request['latest_time'];
        } else {
            $latest_time = null;
        }
        $response = query_room_number($latest_time);
    } elseif ($request['function'] == 'submit_room_number') {
        require ROOT_PATH . '/api/submit_room_number.php';
        $response = submit_room_number($request);
    } elseif ($request['function'] == 'get_online_number') {
        $response = get_online_number();
    } else {
        $response = array(
            'status' => 'failure',
            'response' => 'undefined_function'
        );
    }
} else {
    $response = array(
        'status' => 'failure',
        'response' => 'miss_function_parameter'
    );
}

echo json_encode($response);
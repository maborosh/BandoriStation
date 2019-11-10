<?php
require '../config.php';
require ROOT_PATH . '/functions/error_handler.php';
require ROOT_PATH . '/functions/db_select.php';
require ROOT_PATH . '/functions/other_functions.php';
require ROOT_PATH . '/api/other_functions.php';

if (isset($_POST['request'])) {
    $raw_request = $_POST['request'];
} else {
    $raw_request = file_get_contents('php://input');
}
$request = json_decode($raw_request, true);
$response = array();
if (
    isset($request['page']) and
    isset($request['function'])
) {
    if ($request['page'] == 'home') {
        require ROOT_PATH . '/backend/function_home.php';
        if ($request['function'] == 'initialize') {
            $response = home_initialize();
        } elseif ($request['function'] == 'send_room_number') {
            if (isset($request['room_number']) and isset($request['description'])) {
                $response = home_send_room_number($request['room_number'], $request['description']);
            } else {
                $response = array(
                    'status' => 'failure',
                    'response' => 'undefined_parameter'
                );
            }
        } elseif ($request['function'] == 'update_room_number_filter') {
            if (isset($request['room_number_filter'])) {
                $response = home_update_room_number_filter($request['room_number_filter']);
            } else {
                $response = array(
                    'status' => 'failure',
                    'response' => 'undefined_parameter'
                );
            }
        } elseif ($request['function'] == 'inform_user') {
            if (isset($request['type']) and isset($request['user_id']) and isset($request['reason'])) {
                $response = home_inform_user($request['type'], $request['user_id'], $request['reason']);
            } else {
                $response = array(
                    'status' => 'failure',
                    'response' => 'undefined_parameter'
                );
            }
        } else {
            $response = array(
                'status' => 'failure',
                'response' => 'undefined_function'
            );
        }
    }

    elseif ($request['page'] == 'account') {
        require ROOT_PATH . '/backend/function_account.php';
        if ($request['function'] == 'initialize') {
            $response = account_initialize();
        } elseif ($request['function'] == 'verify_email_change_email') {
            if (isset($request['email'])) {
                $response = verify_email_change_email($request['email']);
            } else {
                $response = array(
                    'status' => 'failure',
                    'response' => 'undefined_parameter'
                );
            }
        } elseif ($request['function'] == 'verify_email_send_verification_code') {
            $response = verify_email_send_verification_code();
        } elseif ($request['function'] == 'verify_email_verify_verification_code') {
            if (isset($request['verification_code'])) {
                $response = verify_email_verify_verification_code($request['verification_code']);
            } else {
                $response = array(
                    'status' => 'failure',
                    'response' => 'undefined_parameter'
                );
            }
        } elseif ($request['function'] == 'set_user_avatar') {
            $response = account_upload_user_avatar_image();
        } elseif ($request['function'] == 'set_username') {
            if (isset($request['username'])) {
                $response = account_set_username($request['username']);
            } else {
                $response = array(
                    'status' => false,
                    'response' => 'undefined_parameter'
                );
            }
        } elseif ($request['function'] == 'set_password') {
            if (isset($request['old_password']) and isset($request['new_password'])) {
                $response = account_set_password($request['old_password'], $request['new_password']);
            } else {
                $response = array(
                    'status' => false,
                    'response' => 'undefined_parameter'
                );
            }
        } elseif ($request['function'] == 'unbinding_email_send_verification_code') {
            $response = account_unbind_email_send_verification_code();
        } elseif ($request['function'] == 'unbinding_email_verify_verification_code') {
            if (isset($request['verification_code'])) {
                $response = account_unbind_email_verify_verification_code($request['verification_code']);
            } else {
                $response = array(
                    'status' => 'failure',
                    'response' => 'undefined_parameter'
                );
            }
        } elseif ($request['function'] == 'binding_email_send_verification_code') {
            if (isset($request['email'])) {
                $response = verify_email_send_verification_code($request['email']);
            } else {
                $response = array(
                    'status' => 'failure',
                    'response' => 'undefined_parameter'
                );
            }
        } elseif ($request['function'] == 'binding_email_verify_verification_code') {
            if (isset($request['verification_code'])) {
                $response = verify_email_verify_verification_code($request['verification_code']);
            } else {
                $response = array(
                    'status' => 'failure',
                    'response' => 'undefined_parameter'
                );
            }
        } elseif ($request['function'] == 'bind_qq') {
            if (isset($request['qq'])) {
                $response = account_bind_qq($request['qq']);
            } else {
                $response = array(
                    'status' => 'failure',
                    'response' => 'undefined_parameter'
                );
            }
        } elseif ($request['function'] == 'unbind_qq') {
            $response = account_bind_qq(null);
        } elseif ($request['function'] == 'reset_password_input_email') {
            if (isset($request['email'])) {
                $response = account_reset_password_input_email($request['email']);
            } else {
                $response = array(
                    'status' => 'failure',
                    'response' => 'undefined_parameter'
                );
            }
        } elseif ($request['function'] == 'reset_password_send_verification_code') {
            $response = account_reset_password_send_verification_code();
        } elseif ($request['function'] == 'reset_password') {
            if (isset($request['new_password']) and isset($request['verification_code'])) {
                $response = account_reset_password($request['new_password'], $request['verification_code']);
            } else {
                $response = array(
                    'status' => 'failure',
                    'response' => 'undefined_parameter'
                );
            }
        } else {
            $response = array(
                'status' => 'failure',
                'response' => 'undefined_function'
            );
        }
    }

    else {
        $response = array(
            'status' => 'failure',
            'response' => 'undefined_page'
        );
    }
} else {
    $response = array(
        'status' => 'failure',
        'response' => 'access_denied'
    );
}

echo json_encode($response);
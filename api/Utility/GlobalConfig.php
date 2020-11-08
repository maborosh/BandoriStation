<?php


namespace BS_API\Utility;


class GlobalConfig
{
    const ALLOW_ORIGIN = 'https://bandoristation.com';

    const NOT_ALLOWED = 'Not allowed';
    const NO_PERMISSION = 'No permission';
    const UNPARSABLE_FORMAT = 'Unparsable format';
    const MISSING_PARAMETERS = 'Missing parameters';
    const MISSING_PARAMETER_FUNCTION = 'Missing parameter "function"';
    const UNDEFINED_FUNCTION_GROUP = 'Undefined function group';
    const UNDEFINED_FUNCTION = 'Undefined function';
    const FORBIDDEN_METHOD = 'Forbidden method';
    const UNDEFINED_ACCESS_TOKEN = 'Undefined access token';
    const TOKEN_VALIDATION_FAILURE = 'Token validation failure';
    const NONEXISTENT_USER = 'Nonexistent user';
    const UNDEFINED_EMAIL = 'Undefined email';
    const DUPLICATE_EMAIL = 'Duplicate email';
    const INVALID_EMAIL = 'Invalid email';
    const VERIFIED_EMAIL = 'Verified email';
    const UNDEFINED_VERIFICATION_CODE = 'Undefined verification code';
    const INVALID_VERIFICATION_CODE = 'Invalid verification code';
    const UNDEFINED_VERIFICATION_REQUEST = 'Undefined verification request';

    const FUNCTION_CONFIG = array(
        'Common' => array(
            'submitRoomNumber',
            'queryRoomNumber',
            'getOnlineNumber',
            'getRoomNumberStat'
        ),
        'UserLogin' => array(
            'login',
            'logout',
            'signup',
            'getCurrentEmail',
            'changeEmail',
            'sendEmailVerificationCode',
            'verifyEmail',
            'resetPasswordSendEmailVerificationCode',
            'resetPasswordVerifyEmail',
            'resetPassword'
        ),
        'MainAction' => array(
            'initializeAccountSetting',
            'getRoomNumberFilter',
            'updateRoomNumberFilter',
            'informUser'
        ),
        'AccountManage' => array(
            'getInitialData',
            'updateAvatar',
            'updateUsername',
            'updatePassword',
            'updateEmailSendVerificationCode',
            'updateEmailVerifyEmail',
            'bindQQ'
        )
    );

    const ACTION_LIST = array(
        'setAccessPermission',
        'getServerTime',
        'setClient',
        'getRoomNumberList',
        'sendRoomNumber',
        'initializeChatRoom',
        'sendChat',
        'loadChatLog'
    );

    const CHECK_DATA_SOURCE = 1;
    const GET_SOURCE_TYPE = 2;
    const GET_SOURCE_TOKEN = 3;

    // ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓ SECRET ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

    ###########################################

    // ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑ SECRET ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
}
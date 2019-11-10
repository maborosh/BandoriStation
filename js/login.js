let send_email_verify_flag = true;

function loginSubmitCheck() {
    if (document.getElementById('username').value.length === 0) {
        alert('用户名不能为空');
        return false;
    }
    return true;
}

function signUpSubmitCheck() {
    if (document.getElementById('username').value.length === 0) {
        alert('用户名不能为空');
        return false;
    } else if (document.getElementById('password').value.length < 6) {
        alert('密码不得小于6位');
        return false;
    } else if (document.getElementById('password').value !== document.getElementById('password-repeat').value) {
        alert('两次输入的密码不一致，请重新输入密码');
        return false;
    } else if (!/^(\w-*\.*)+@(\w-?)+(\.[a-z]{2,})+$/.test(document.getElementById('email').value)) {
        alert('请填入正确的电子邮件地址');
        return false;
    } else {
        return true;
    }
}

function loginVerifyEmailChangeEmail() {
    let current_email = document.getElementById('current-email').value;
    if (!/^(\w-*\.*)+@(\w-?)+(\.[a-z]{2,})+$/.test(current_email)) {
        alert('请填入正确的电子邮件地址');
    } else {
        new Ajax(
            {
                'page': 'account',
                'function' : 'verify_email_change_email',
                'email': current_email
            },
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    alert('修改成功');
                } else {
                    if (response_dict['response'] === 'duplicate_email') {
                        alert('该邮箱已被注册')
                    } else {
                        alert('修改失败');
                    }
                }
            }
        );
    }
}

function verifyEmailSendEmailVerificationCode() {
    if (send_email_verify_flag) {
        send_email_verify_flag = false;

        new Ajax(
            {
                'page': 'account',
                'function': 'verify_email_send_verification_code'
            },
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    alert('已发送验证码至邮箱' + response_dict['response']['email']);
                } else {
                    alert('发送失败');
                }
            }
        );

        buttonHold(
            '#send-verification-code-button',
            60,
            '重新发送',
            function (button) {
                button.text('发送验证码');
                send_email_verify_flag = true;
            }
        );
    }
}

function verifyEmailVerifyVerificationCode() {
    let verification_code = document.getElementById('verification-code').value;
    if (/^[0-9]{6}$/.test(verification_code)) {
        new Ajax(
            {
                'page': 'account',
                'function': 'verify_email_verify_verification_code',
                'verification_code': verification_code
            },
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    window.location.href = '?verify_email';
                } else {
                    if (response_dict['response'] === 'wrong_verification_code') {
                        alert('验证码错误，请重新输入');
                    } else {
                        alert('验证失败');
                    }
                }
            }
        );
    } else {
        alert('验证码必须为6位数字');
    }
}

function resetPasswordInputEmail() {
    let email = document.getElementById('email').value;
    if (!/^(\w-*\.*)+@(\w-?)+(\.[a-z]{2,})+$/.test(email)) {
        alert('请填入正确的电子邮件地址');
    } else {
        new Ajax(
            {
                'page': 'account',
                'function': 'reset_password_input_email',
                'email': email
            },
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    new Ajax(
                        null,
                        'components/login_reset_password.html',
                        'GET',
                        false,
                        function (response) {
                            document.getElementById('login-reset-password-container').innerHTML = response;
                        }
                    );
                } else {
                    if (response_dict['response'] === 'unregistered_email') {
                        alert('该邮箱尚未注册');
                    } else {
                        alert('邮箱查询失败');
                    }
                }
            }
        )
    }
}

function resetPasswordSendVerificationCode() {
    if (send_email_verify_flag) {
        send_email_verify_flag = false;

        new Ajax(
            {
                'page': 'account',
                'function': 'reset_password_send_verification_code'
            },
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    alert('已发送验证码至邮箱' + response_dict['response']['email']);
                } else {
                    alert('发送失败');
                }
            }
        );

        buttonHold(
            '#send-verification-code-button',
            60,
            '重新发送',
            function (button) {
                button.text('发送验证码');
                send_email_verify_flag = true;
            }
        );
    }
}

function resetPassword() {
    let new_password = document.getElementById('password');
    let new_password_repeat = document.getElementById('password-repeat');
    let verification_code = document.getElementById('verification-code');
    if (new_password.value.length < 6) {
        alert('密码不得小于6位');
        new_password.value = '';
        new_password_repeat.value = '';
    } else if (new_password.value !== new_password_repeat.value) {
        alert('两次输入的密码不一致，请重新输入');
        new_password.value = '';
        new_password_repeat.value = '';
    } else if (verification_code.value.length !== 6) {
        alert('验证码必须为6位数字');
    } else {
        new Ajax(
            {
                'page': 'account',
                'function': 'reset_password',
                'new_password': new_password.value,
                'verification_code': verification_code.value
            },
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    window.location.href = '?login';
                } else {
                    if (response_dict['response'] === 'wrong_verification_code') {
                        alert('验证码错误，请重新输入');
                        verification_code.value = '';
                    } else {
                        alert('重置失败');
                    }
                }
            }
        );
    }
}
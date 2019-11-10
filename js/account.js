let account_info = {
    'email': undefined,
    'qq': undefined
};
let send_email_verify_flag = true;

window.onload = function() {
    generalInitialize();
    if ($.cookie('user_avatar')) {
        document.getElementById('account-user-avatar').style.backgroundImage = 'url("assets/user_avatar/' + $.cookie('user_avatar') + '")';
    } else {
        document.getElementById('account-user-avatar').style.backgroundImage = 'url("assets/user_avatar/user_icon.png")';
    }
    new Ajax(
        {
            'page': 'account',
            'function': 'initialize'
        },
        BACKEND_ROOT,
        'POST',
        true,
        function (response) {
            document.getElementById('account-username').innerText = response['username'];
            if (response['email']) {
                document.getElementById('account-email').innerText = response['email'];
                account_info['email'] = response['email'];
            } else {
                document.getElementById('account-email').innerText = '未绑定';
            }
            if (response['qq']) {
                document.getElementById('account-qq').innerText = response['qq'];
                account_info['qq'] = response['qq'];
            } else {
                document.getElementById('account-qq').innerText = '未绑定';
            }
        }
    );
};

function openChangeAvatarDialog() {
    new Ajax(
        null,
        'components/account_change_avatar.html',
        'GET',
        false,
        function (response) {
            openDialog(response);
            initializeCropper();
        }
    );
}

function initializeCropper () {
    let image = $('#avatar-selected-image');
    let input_image = $('#avatar-image-select');
    let options = {
        aspectRatio: 1, // 纵横比
        viewMode: 2,
        preview: '#avatar-preview' // 预览图的class名
    };
    image.cropper(options);
    let uploaded_image_url;
    if (URL) {
        // 给input添加监听
        input_image.change(function () {
            let files = this.files;
            let file;
            if (!image.data('cropper')) {
                return;
            }
            if (files && files.length) {
                file = files[0];
                // 判断是否是图像文件
                if (/^image\/\w+$/.test(file.type)) {
                    // 如果URL已存在就先释放
                    if (uploaded_image_url) {
                        URL.revokeObjectURL(uploaded_image_url);
                    }
                    uploaded_image_url = URL.createObjectURL(file);
                    // 销毁cropper后更改src属性再重新创建cropper
                    image.cropper('destroy').attr('src', uploaded_image_url).cropper(options);
                    input_image.val('');
                } else {
                    alert('请选择一个图片文件');
                }
            }
        });
    } else {
        input_image.prop('disabled', true).addClass('disabled');
    }
}

function uploadUserAvatarImage() {
    try {
        $('#avatar-selected-image').cropper('getCroppedCanvas',{
            width:200, // 裁剪后的长宽
            height:200
        }).toBlob(function(blob) {
            let request_form = new FormData();
            request_form.append('request', JSON.stringify({
                'page': 'account',
                'function': 'set_user_avatar'
            }));
            request_form.append('image', blob);
            $.ajax({
                url: BACKEND_ROOT,
                type: 'POST',
                data: request_form,
                contentType: false,
                processData: false,
                success: function (response) {
                    let response_dict = JSON.parse(response);
                    if (response_dict['status'] === 'success') {
                        $.cookie('user_avatar', response_dict['response']['file_name'], {expires: 30});
                        document.getElementById('user-avatar').style.backgroundImage = 'url("assets/user_avatar/' + response_dict['response']['file_name'] + '")';
                        document.getElementById('account-user-avatar').style.backgroundImage = 'url("assets/user_avatar/' + response_dict['response']['file_name'] + '")';
                        closeDialog();
                    } else {
                        alert('设置失败');
                    }
                }
            });
        });
    } catch (e) {
        alert('请先选择图片');
    }
}

function accountOpenSettingDialog(setting) {
    new Ajax(
        null,
        'components/account_' + setting + '.html',
        'GET',
        false,
        function (response) {
            openDialog(response);
        }
    );
}

function accountSetUsername() {
    new Ajax(
        {
            'page': 'account',
            'function': 'set_username',
            'username': document.getElementById('account-setting-username').value
        },
        BACKEND_ROOT,
        'POST',
        false,
        function (response) {
            let response_dict = JSON.parse(response);
            if (response_dict['status'] === 'success') {
                document.getElementById('account-username').innerText = response_dict['response']['username'];
                alert('设置成功');
                closeDialog();
            } else {
                if (response_dict['response'] === 'duplicate_username') {
                    alert('该用户名已被使用，请重新设置');
                } else if (response_dict['response'] === 'username_is_email') {
                    alert('用户名不能是邮箱地址')
                } else {
                    alert('设置失败');
                }
            }
        }
    );
}

function accountSetPassword() {
    let old_password = document.getElementById('account-setting-old-password');
    let new_password = document.getElementById('account-setting-password');
    let new_password_confirm = document.getElementById('account-setting-confirm-password');
    if (new_password.value !== new_password_confirm.value) {
        alert('两次输入的密码不一致，请重新输入');
        old_password.value = '';
        new_password.value = '';
        new_password_confirm.value = '';
    } else if (new_password.value.length < 6) {
        alert('密码不得少于6位');
        old_password.value = '';
        new_password.value = '';
        new_password_confirm.value = '';
    } else {
        new Ajax(
            {
                'page': 'account',
                'function': 'set_password',
                'old_password': old_password.value,
                'new_password': new_password.value
            },
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    alert('设置成功');
                    $.cookie('login_time', response_dict['response']['login_time'], {expires: 30});
                    $.cookie('token', response_dict['response']['token'], {expires: 30});
                    closeDialog();
                } else {
                    if (response_dict['response'] === 'wrong_password') {
                        alert('原密码错误，请重新输入');
                    } else {
                        alert('设置失败');
                    }
                }
                old_password.value = '';
                new_password.value = '';
                new_password_confirm.value = '';
            }
        );
    }
}

function openAccountEmailBindingDialog() {
    new Ajax(
        null,
        'components/account_bind_email.html',
        'GET',
        false,
        function (response) {
            openDialog(response, function () {
                if (account_info['email']) {
                    document.getElementById('account-bind-email-title').innerHTML = '解绑邮箱';
                    let banding_email_input = document.getElementById('account-setting-email');
                    banding_email_input.value = account_info['email'];
                    banding_email_input.disabled = 'disabled';
                    document.getElementById('account-send-email-verification-code-button').onclick = function () {
                        accountChangeEmailSendVerificationCode('unbinding_email_send_verification_code');
                    };
                    let binding_email_execute = document.getElementById('account-bind-email-execute');
                    binding_email_execute.innerHTML = '下一步';
                    binding_email_execute.onclick = accountUnbindEmail;
                } else {
                    document.getElementById('account-bind-email-title').innerHTML = '绑定邮箱';
                    document.getElementById('account-send-email-verification-code-button').onclick = function () {
                        accountChangeEmailSendVerificationCode(
                            'binding_email_send_verification_code',
                            document.getElementById('account-setting-email').value
                        );
                    };
                    let binding_email_execute = document.getElementById('account-bind-email-execute');
                    binding_email_execute.innerHTML = '绑定';
                    binding_email_execute.onclick = accountBindEmail;
                }
            });
        }
    );
}

function accountChangeEmailSendVerificationCode(func, email = undefined) {
    if (send_email_verify_flag) {
        send_email_verify_flag = false;

        let request = {
            'page': 'account',
            'function': func
        };
        if (email) {
            request['email'] = email
        }
        new Ajax(
            request,
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    alert('已发送验证码至邮箱' + response_dict['response']['email']);
                } else {
                    if (response_dict['response'] === 'duplicate_email') {
                        alert('该邮箱已被注册');
                    } else {
                        alert('发送失败');
                    }
                }
            }
        );

        buttonHold(
            '#account-send-email-verification-code-button',
            10,
            '重新发送',
            function (button) {
                button.text('发送验证码');
                send_email_verify_flag = true;
            }
        );
    }
}

function accountUnbindEmail() {
    let verification_code = document.getElementById('account-setting-email-verification-code');
    if (/^[0-9]{6}$/.test(verification_code.value)) {
        new Ajax(
            {
                'page': 'account',
                'function': 'unbinding_email_verify_verification_code',
                'verification_code': verification_code.value
            },
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    account_info['email'] = undefined;
                    document.getElementById('account-email').innerText = '未绑定';
                    document.getElementById('account-bind-email-title').innerHTML = '绑定邮箱';
                    let banding_email_input = document.getElementById('account-setting-email');
                    banding_email_input.value = '';
                    banding_email_input.disabled = false;
                    document.getElementById('account-send-email-verification-code-button').onclick = function () {
                        accountChangeEmailSendVerificationCode(
                            'binding_email_send_verification_code',
                            banding_email_input.value
                        );
                    };
                    verification_code.value = '';
                    let binding_email_execute = document.getElementById('account-bind-email-execute');
                    binding_email_execute.innerHTML = '绑定';
                    binding_email_execute.onclick = accountBindEmail;
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

function accountBindEmail() {
    let verification_code = document.getElementById('account-setting-email-verification-code').value;
    if (/^[0-9]{6}$/.test(verification_code)) {
        new Ajax(
            {
                'page': 'account',
                'function': 'binding_email_verify_verification_code',
                'verification_code': verification_code
            },
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    account_info['email'] = response_dict['response']['email'];
                    document.getElementById('account-email').innerText = response_dict['response']['email'];
                    alert('绑定成功');
                    closeDialog();
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

function accountBindQQ() {
    let qq = document.getElementById('account-qq-number').value;
    if (/^[1-9][0-9]{4,}$/.test(qq)) {
        new Ajax(
            {
                'page': 'account',
                'function': 'bind_qq',
                'qq': qq
            },
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    account_info['qq'] = response_dict['response']['qq'];
                    document.getElementById('account-qq').innerText = response_dict['response']['qq'];
                    alert('绑定成功');
                    closeDialog();
                } else {
                    if (response_dict['response'] === 'duplicate_qq_number') {
                        alert('该QQ号已被绑定，请尝试绑定其他的QQ号')
                    } else {
                        alert('绑定失败');
                    }
                }
            }
        );
    } else {
        alert('QQ号不合法，请重新输入');
    }
}

function accountUnbindQQ() {
    if (account_info['qq']) {
        new Ajax(
            {
                'page': 'account',
                'function': 'unbind_qq'
            },
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    account_info['qq'] = undefined;
                    document.getElementById('account-qq').innerText = '未绑定';
                    alert('解绑成功');
                    closeDialog();
                } else {
                    alert('解绑失败');
                }
            }
        );
    } else {
        alert('您尚未绑定QQ');
    }
}
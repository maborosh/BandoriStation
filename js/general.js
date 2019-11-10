const API_ROOT = "api/index.php";
const BACKEND_ROOT = "backend/index.php";

window.alert = function (message) {
    document.getElementById('information-text').innerHTML = message;
    document.getElementById('information-button-container').innerHTML =
        '<div class="button button-theme-color information-alert-button" onclick="closeInformationDialog()">确定</div>';
    openInformationDialog();
};

window.confirm = function (message, function_confirm, function_cancel = 'closeInformationDialog()') {
    document.getElementById('information-text').innerHTML = message;
    document.getElementById('information-button-container').innerHTML =
        '<div class="button button-theme-color-reverse information-confirm-button" ' +
        'onclick="' + function_confirm + '">确定</div>' +
        '<div class="button button-theme-color information-confirm-button" ' +
        'onclick="' + function_cancel + '">取消</div>';
    openInformationDialog();
};

function generalInitialize() {
    if (document.getElementById('user-avatar')) {
        if ($.cookie('user_avatar')) {
            document.getElementById('user-avatar').style.backgroundImage = 'url("assets/user_avatar/' + $.cookie('user_avatar') + '")';
        } else {
            document.getElementById('user-avatar').style.backgroundImage = 'url("assets/user_avatar/user_icon.png")';
        }
    }
}

function inputFocusTriggerBorder(border_id, class_name, type) {
    if (type === 0) {
        $('#' + border_id).removeClass(class_name);
    } else {
        $('#' + border_id).addClass(class_name);
    }
}

function inputBorderTriggerInput(input_id) {
    document.getElementById(input_id).focus();
}

function openInformationDialog() {
    document.getElementById('information-hint').innerText = '';
    $('#information-container').fadeIn(400);
    $('#information').addClass('module-cut-in');
}

function closeInformationDialog() {
    $('.information').removeClass('module-cut-in');
    $('.information-container').fadeOut(400);
}

function openDialog(content, callback = function () {}) {
    document.getElementById('dialog-content').innerHTML = content;
    callback();
    $('.dialog-container').fadeIn(400);
    $('.dialog').addClass('module-cut-in');
}

function closeDialog() {
    $('.dialog').removeClass('module-cut-in');
    $('.dialog-container').fadeOut(400);
}

function Ajax(request, url, method, json_parse, callback) {
    let xml_http;
    if (window.XMLHttpRequest) {
        xml_http = new XMLHttpRequest();
    } else {
        xml_http = new ActiveXObject("Microsoft.XMLHTTP");
    }
    if (method === 'GET') {
        let count = 0;
        for (let key in request) {
            if (request.hasOwnProperty(key)) {
                if (count === 0) {
                    url += '?' + key + '=' + request[key];
                } else {
                    url += '&' + key + '=' + request[key];
                }
            }
        }
        xml_http.open(method, url, true);
        xml_http.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        xml_http.send();
    } else {
        xml_http.open(method, url, true);
        xml_http.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        xml_http.send(JSON.stringify(request));
    }
    xml_http.onreadystatechange = function () {
        if (xml_http.readyState === 4 && xml_http.status === 200) {
            try {
                if (json_parse) {
                    let response = JSON.parse(xml_http.responseText);
                    if (response['status'] === 'success') {
                        callback(response['response']);
                    } else {
                        alert('数据获取失败');
                    }
                } else {
                    callback(xml_http.responseText)
                }
            } catch (e) {
                console.log(e);
                alert('数据解析失败');
            }
        }
    };
}

function settingMenuToggle() {
    let menu = $('#setting-menu');
    if (menu.is(':hidden')) {
        $('#user-avatar').addClass('dropdown-setting-menu-mask');
    } else {
        $('#user-avatar').removeClass('dropdown-setting-menu-mask');
    }
    menu.fadeToggle(200);
}

function buttonHold(selector, interval, hold_text, callback = function () {}) {
    let send_verification_button = $(selector);
    send_verification_button.removeClass('button-theme-color');
    send_verification_button.text(hold_text + '(' + interval + ')');
    let timer_id = setInterval(function () {
        interval -= 1;
        send_verification_button.text(hold_text + '(' + interval + ')');
        if (interval <= 0) {
            clearInterval(timer_id);
            send_verification_button.addClass('button-theme-color');
            callback(send_verification_button);
        }
    }, 1000);
}

function openAboutDialog() {
    new Ajax(
        null,
        'components/about.html',
        'GET',
        false,
        function (response) {
            openDialog(response, function () {
                new Ajax(
                    {
                        'function': 'get_online_number'
                    },
                    API_ROOT,
                    'POST',
                    true,
                    function (online_number_response) {
                        document.getElementById('online-number').innerText = online_number_response['online_number'];
                    }
                );
            });
        }
    );
}

function textInputEnter(button_id) {
    if (event.key !== undefined) {
        if (event.key === 'Enter') {
            document.getElementById(button_id).click();
        }
    } else if (event.keyCode !== undefined) {
        if (event.keyCode === 13) {
            document.getElementById(button_id).click();
        }
    }
}

let fetch_data_timer_id;
let index = 0;
let buffer;
let room_number_filter = {};
let block_user_list_temp = [];

window.onload = function() {
    generalInitialize();
    homeInitialize();
};

function homeInitialize() {
    new Ajax(
        {
            'page': 'home',
            'function': 'initialize'
        },
        BACKEND_ROOT,
        'POST',
        false,
        function (response) {
            let response_dict = JSON.parse(response);
            if (response_dict['status'] === 'success') {
                buffer = response_dict['response'];
                if ($.cookie('room_number_filter')) {
                    confirm('检测到有本地保存的筛选设置，是否同步到账号？', "homeSyncFilter(true)", "homeSyncFilter(false)");
                } else {
                    if (buffer) {
                        room_number_filter = buffer;
                    }
                }
            } else {
                if (response_dict['response'] === 'login_check_failure' && $.cookie('room_number_filter')) {
                    room_number_filter = JSON.parse($.cookie('room_number_filter'));
                }
            }

            let data_list = new Vue({
                el: '#room-number-data-container',
                data: {
                    room_number_list: []
                },
                methods: {
                    copyRoomNumber: function (number) {
                        let dummy_button = document.getElementById('dummy');
                        dummy_button.setAttribute('data-clipboard-text', number);
                        dummy_button.click();
                    },
                    blockUser: function (type, user_id, username) {
                        confirm('确定屏蔽用户' + username + '?', "homeBlockUser('" + type + "', " + user_id + ", '" + username + "')");
                    },
                    informUser: function (room_number_data) {
                        buffer = room_number_data;
                        confirm(
                            '确定举报用户' + room_number_data['user_info']['username'] + '?<textarea class="input-text information-textarea" id="inform-reason" placeholder="举报原因"></textarea>',
                            "homeInformUser()"
                        );
                    }
                }
            });
            setInterval(function () {
                updateIntervalTime(data_list.$data.room_number_list)
            }, 200);
            new ClipboardJS("#dummy");

            if (window.WebSocket) {
                let websocket_worker = new Worker('js/websocket_worker.js');
                websocket_worker.onmessage = function (event) {
                    let response = JSON.parse(event.data);
                    if (response['status'] === 'success') {
                        appendRoomNumberData(data_list.$data.room_number_list, response['response']);
                    } else {
                        alert(response['response'])
                    }
                }
            } else {
                fetch_data_timer_id = setInterval(function () {
                    fetchDataFromAPI(data_list.$data.room_number_list);
                }, 1000);
            }
        }
    );
}

function fetchDataFromAPI(data_list) {
    let latest_time = 0;
    if (data_list.length > 0) {
        latest_time = data_list[0]['time'];
    }
    new Ajax(
        {
            'function': 'query_room_number',
            'latest_time': latest_time
        },
        API_ROOT,
        'POST',
        true,
        function (response) {
            appendRoomNumberData(data_list, response);
        }
    );
}

function appendRoomNumberData(data_list, new_data) {
    let current_time = new Date().getTime();
    let latest_time;
    if (data_list.length > 0) {
        latest_time = data_list[0]['time']
    } else {
        latest_time = 0;
    }
    for (let i = 0; i < new_data.length; i++) {
        if (new_data[i]['time'] > latest_time) {
            let filter_check = true;
            if (room_number_filter.hasOwnProperty('type')) {
                for (let j = 0; j < room_number_filter['type'].length; j++) {
                    if (new_data[i]['type'] === room_number_filter['type'][j]) {
                        filter_check = false;
                        break;
                    }
                }
            }
            if (filter_check && room_number_filter.hasOwnProperty('keyword')) {
                for (let j = 0; j < room_number_filter['keyword'].length; j++) {
                    if (
                        room_number_filter['keyword'][j] !== '' &&
                        new_data[i]['raw_message'].indexOf(room_number_filter['keyword'][j]) > -1
                    ) {
                        filter_check = false;
                        break;
                    }
                }
            }
            if (filter_check && room_number_filter.hasOwnProperty('user')) {
                for (let j = 0; j < room_number_filter['user'].length; j++) {
                    if (
                        new_data[i]['user_info']['type'] === room_number_filter['user'][j]['type'] &&
                        new_data[i]['user_info']['user_id'] === room_number_filter['user'][j]['user_id']
                    ) {
                        filter_check = false;
                        break;
                    }
                }
            }

            if (filter_check) {
                let interval_time = current_time - new_data[i]['time'];
                new_data[i]['time_interval'] = translateIntervalTime(interval_time);
                new_data[i]['index'] = index;
                index += 1;
                if (new_data[i]['source_info']['name'] === 'Bandori Station') {
                    new_data[i]['source_info']['name'] = '本站';
                }
                if (!new_data[i]['user_info']['avatar']) {
                    new_data[i]['user_info']['avatar'] = 'user_icon.png';
                }
                new_data[i]['user_info']['avatar_style'] = {
                    backgroundImage: 'url("assets/user_avatar/' + new_data[i]['user_info']['avatar'] + '")'
                };
                if (new_data[i]['type'] === '25') {
                    new_data[i]['type'] = '25万房'
                } else if (new_data[i]['type'] === '18') {
                    new_data[i]['type'] = '18万大师房'
                } else if (new_data[i]['type'] === '12') {
                    new_data[i]['type'] = '12万高手房'
                } else if (new_data[i]['type'] === '7') {
                    new_data[i]['type'] = '7万常规房'
                } else {
                    new_data[i]['type'] = ''
                }
                data_list.unshift(new_data[i]);
            }
        }
    }
}

function updateIntervalTime(data_list) {
    let current_time = new Date().getTime();
    for (let i = data_list.length - 1; i >= 0; i--) {
        let interval_time = current_time - data_list[i]['time'];
        if (interval_time > 600000) {
            data_list.pop();
        } else {
            data_list[i]['time_interval'] = translateIntervalTime(interval_time);
        }
    }
}

function translateIntervalTime(interval_time) {
    interval_time = Math.round(interval_time / 1000);
    let minute = Math.floor(interval_time / 60);
    if (minute > 0) {
        return minute + '分钟前';
    } else {
        return interval_time + '秒前';
    }
}

function homeOpenDialog(func) {
    new Ajax(
        null,
        'components/home_' + func + '.html',
        'GET',
        false,
        function (response) {
            openDialog(response);
        }
    );
}

function homeSendRoomNumber() {
    let room_number = document.getElementById('send-room-number-number').value;
    if (room_number === '') {
        alert('房间号不得为空');
        return;
    } else if (!/^[0-9]{5,6}$/.test(room_number)) {
        alert('输入正确的房间号');
        return;
    }
    let description = document.getElementById('send-room-number-description').value;
    if (description === '') {
        alert('房间描述不得为空');
        return;
    }
    new Ajax(
        {
            'page': 'home',
            'function': 'send_room_number',
            'room_number': room_number,
            'description': description
        },
        BACKEND_ROOT,
        'POST',
        false,
        function (response) {
            let response_dict = JSON.parse(response);
            if (response_dict['status'] === 'success') {
                closeDialog();
            } else {
                if (response_dict['response'] === 'banned_user') {
                    alert('由于违反相关规则，您已被禁止发送');
                } else if (response_dict['response'] === 'duplicate_number_submit') {
                    alert('请勿在短时间内发送相同的房间号')
                } else if (response_dict['response'] === 'login_check_failure') {
                    window.open('?login', '_blank');
                } else {
                    alert('发送失败');
                }
            }
        }
    );
}

function homeOpenFilterDialog() {
    new Ajax(
        null,
        'components/home_filter_room_number.html',
        'GET',
        false,
        function (response) {
            openDialog(response, function () {
                if (room_number_filter.hasOwnProperty('type')) {
                    room_number_filter['type'].forEach(function (value) {
                        if (value === '7' || value === '12' || value === '18' || value === '25' || value === 'other') {
                            document.getElementById('room-number-filter-type-' + value + '-label').click();
                        }
                    });
                }
                if (room_number_filter.hasOwnProperty('keyword')) {
                    document.getElementById('room-number-filter-keywords').value = room_number_filter['keyword'].join('|');
                }
                if (room_number_filter.hasOwnProperty('user')) {
                    room_number_filter['user'].forEach(function (value) {
                        let block_user = document.createElement('div');
                        block_user.setAttribute('class', 'room-number-filter-block-user');
                        block_user.setAttribute('id', 'room-number-filter-block-user-' + value['type'] + '-' + value['user_id']);
                        let block_username = document.createElement('span');
                        block_username.setAttribute('class', 'room-number-filter-block-username');
                        block_username.innerText = value['username'];
                        let block_user_delete = document.createElement('i');
                        block_user_delete.setAttribute('class', 'fas fa-times room-number-filter-block-user-delete');
                        block_user_delete.setAttribute('title', '删除');
                        block_user_delete.setAttribute('onclick', "homeRemoveBlockUser('" + value['type'] + "', " + value['user_id'] + ")");
                        block_user.append(block_username, block_user_delete);
                        document.getElementById('room-number-filter-user-container').append(block_user);
                    });
                    block_user_list_temp = room_number_filter['user'];
                }
            });
        }
    );
}

function homeUpdateFilter(collect_options) {
    if (collect_options) {
        let type_array = [];
        let type_options_array = document.getElementsByClassName('room-number-filter-type');
        for (let i = 0; i < type_options_array.length; i++) {
            if (!type_options_array[i].checked) {
                type_array.push(type_options_array[i].value);
            }
        }
        room_number_filter['type'] = type_array;
        let keywords_value = document.getElementById('room-number-filter-keywords').value;
        if (keywords_value) {
            room_number_filter['keyword'] = keywords_value.split('|');
        } else {
            room_number_filter['keyword'] = [];
        }
        room_number_filter['user'] = block_user_list_temp;
    }
    new Ajax(
        {
            'page': 'home',
            'function': 'update_room_number_filter',
            'room_number_filter': room_number_filter
        },
        BACKEND_ROOT,
        'POST',
        false,
        function (response) {
            let response_dict = JSON.parse(response);
            if (response_dict['status'] === 'success') {
                if (collect_options) {
                    closeDialog();
                }
            } else {
                if (response_dict['response'] === 'login_check_failure') {
                    $.cookie('room_number_filter', JSON.stringify(room_number_filter), {expires: 30});
                    closeDialog();
                } else {
                    alert('设置失败');
                }
            }
        }
    );
}

function homeSyncFilter(is_confirm) {
    if (is_confirm) {
        room_number_filter = JSON.parse($.cookie('room_number_filter'));
        homeUpdateFilter(false);
    } else {
        if (buffer) {
            room_number_filter = buffer;
        }
    }
    $.removeCookie('room_number_filter');
    closeInformationDialog();
}

function homeBlockUser(type, user_id, username) {
    let block_flag = true;
    if (room_number_filter.hasOwnProperty('user')) {
        room_number_filter['user'].forEach(function (value) {
            if (value['type'] === type && value['user_id'] === user_id) {
                block_flag = false;
            }
        });
    } else {
        room_number_filter['user'] = [];
    }
    if (block_flag) {
        room_number_filter['user'].push(
            {
                'type': type,
                'user_id': user_id,
                'username': username
            }
        );
        homeUpdateFilter(false);
    }
    closeInformationDialog();
}

function homeRemoveBlockUser(type, user_id) {
    let new_block_user_list = [];
    for (let i = 0; i < block_user_list_temp.length; i++) {
        if (block_user_list_temp[i]['type'] === type && block_user_list_temp[i]['user_id'] === user_id) {
            let block_user_div = document.getElementById('room-number-filter-block-user-' + block_user_list_temp[i]['type'] + '-' + block_user_list_temp[i]['user_id']);
            block_user_div.parentNode.removeChild(block_user_div);
        } else {
            new_block_user_list.push(block_user_list_temp[i]);
        }
    }
    block_user_list_temp = new_block_user_list;
}

function homeInformUser() {
    let inform_reason = document.getElementById('inform-reason').value;
    if (inform_reason) {
        new Ajax(
            {
                'page': 'home',
                'function': 'inform_user',
                'type': buffer['user_info']['type'],
                'user_id': buffer['user_info']['user_id'],
                'reason': inform_reason + ' ' + JSON.stringify(buffer)
            },
            BACKEND_ROOT,
            'POST',
            false,
            function (response) {
                let response_dict = JSON.parse(response);
                if (response_dict['status'] === 'success') {
                    closeInformationDialog();
                } else {
                    if (response_dict['response'] === 'login_check_failure') {
                        window.open('?login', '_blank');
                    } else {
                        closeInformationDialog();
                        alert('举报失败');
                    }
                }
            }
        );
    } else {
        document.getElementById('information-hint').innerText = '举报原因不得为空'
    }
}
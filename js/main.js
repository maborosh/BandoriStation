const API_ROOT = 'api/index.php';
let timer_id;
let index = 0;

window.onload = function() {
    let data_list = new Vue({
        el: "#room-number-data-container",
        data: {
            room_number_list: []
        },
        methods: {
            copyRoomNumber: function (number) {
                let dummy_button = document.getElementById("dummy");
                dummy_button.setAttribute("data-clipboard-text", number);
                dummy_button.click();
            }
        }
    });
    setInterval(function () {
        updateIntervalTime(data_list.$data.room_number_list)
    }, 200);
    new ClipboardJS("#dummy");

    if (window.WebSocket) {
        let ws = new WebSocket("wss://api.bandoristation.com:50443");
        ws.onopen = function() {
            console.log("Connected to WebSocket server.");
        };
        ws.onmessage = function (event) {
            let data = JSON.parse(event.data);
            if (data['status'] === 'success') {
                appendData(data_list.$data.room_number_list, data['response']);
            } else {
                ws.close();
                alert('数据获取失败');
            }
        };
        ws.onerror = function () {
            ws.close();
            alert('连接异常');
        }
    } else {
        timer_id = setInterval(function () {
            fetchDataFromAPI(data_list.$data.room_number_list);
        }, 1000);
    }
};

function fetchDataFromAPI(data_list) {
    let xml_http;
    if (window.XMLHttpRequest) {
        xml_http = new XMLHttpRequest();
    } else {
        xml_http = new ActiveXObject("Microsoft.XMLHTTP");
    }
    let latest_time = 0;
    if (data_list.length > 0) {
        latest_time = data_list[0]['time'];
    }
    xml_http.open("GET", API_ROOT + "?function=query_room_number&latest_time=" + latest_time, true);
    xml_http.send();
    xml_http.onreadystatechange = function () {
        if (xml_http.readyState === 4 && xml_http.status === 200) {
            try {
                let data = JSON.parse(xml_http.responseText);
                if (data['status'] === 'true') {
                    appendData(data_list, data['response']);
                } else {
                    clearInterval(timer_id);
                    alert('数据获取失败');
                }
            } catch (e) {
                clearInterval(timer_id);
                alert('数据解析失败');
            }
        }
    };
}

function appendData(data_list, new_data) {
    let current_time = new Date().getTime();
    let latest_time;
    if (data_list.length > 0) {
        latest_time = data_list[0]['time']
    } else {
        latest_time = 0;
    }
    for (let i = 0; i < new_data.length; i++) {
        if (new_data[i]['time'] > latest_time) {
            let interval_time = current_time - new_data[i]['time'];
            new_data[i]['time_interval'] = translateIntervalTime(interval_time);
            new_data[i]['index'] = index;
            index += 1;
            data_list.unshift(new_data[i]);
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
        return minute + "分钟前";
    } else {
        return interval_time + "秒前";
    }
}


let ws = new WebSocket("wss://api.bandoristation.com:50443");
let heartbeat_timer_id;
ws.onopen = function() {
    console.log('Connected to WebSocket server.');
    ws.send('heartbeat');
    heartbeat_timer_id = setInterval(function () {
        ws.send('heartbeat');
    }, 30000);
};
ws.onmessage = function (event) {
    let data = JSON.parse(event.data);
    if (data['status'] === 'success') {
        postMessage(event.data);
    } else {
        ws.close();
        postMessage(JSON.stringify({'status': 'failure', 'response': '数据获取失败'}));
    }
};
ws.onclose = function () {
    clearInterval(heartbeat_timer_id);
    ws.close();
    postMessage(JSON.stringify({'status': 'failure', 'response': '服务器连接已关闭'}));
};
ws.onerror = function () {
    ws.close();
    postMessage(JSON.stringify({'status': 'failure', 'response': '服务器连接异常'}));
};
import { WEBSOCKET_API_URL } from '@/utilities/constants'

const ws = new WebSocket(WEBSOCKET_API_URL)
let heartbeatTimerId

ws.onopen = () => {
  postMessage({
    status: 'success',
    action: 'initializeClient',
    response: null
  })
  heartbeatTimerId = setInterval(() => {
    ws.send(JSON.stringify({
      action: 'heartbeat',
      data: {
        client: 'BandoriStation'
      }
    }))
  }, 30000)
}

ws.onmessage = event => {
  postMessage(JSON.parse(event.data))
}

ws.onclose = () => {
  clearInterval(heartbeatTimerId)
  ws.close()
  postMessage({
    status: 'failure',
    response: '服务器连接已断开'
  })
}

ws.onerror = event => {
  ws.close()
  postMessage({
    status: 'failure',
    response: '服务器连接异常'
  })
}

self.onmessage = event => {
  ws.send(JSON.stringify(event.data))
}

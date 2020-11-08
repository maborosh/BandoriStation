<template>
  <div>
    <RoomNumberList
      ref="roomNumberList"
      :websocket-worker="websocketWorker"
    />
    <ChatRoom
      ref="chatRoom"
      :display-modal="$store.state.modal.dialog.Home.openChatRoom"
      :websocket-worker="websocketWorker"
      @hideModalEvent="switchDialog('openChatRoom', false)"
    />
  </div>
</template>

<script>
import WebSocketWorker from '@/network/websocket.worker.js'
import RoomNumberList from '@/components/project/RoomNumber/RoomNumberList'
import ChatRoom from '@/components/project/ChatRoom/ChatRoom'

export default {
  name: 'Home',
  components: {
    ChatRoom,
    RoomNumberList
  },
  data () {
    return {
      websocketWorker: null
    }
  },
  created () {
    this.$store.commit('navbar/setMenuDisplay', true)
    if (window.WebSocket) {
      this.websocketWorker = new WebSocketWorker()
      this.websocketWorker.onmessage = event => {
        if (event.data.status === 'success') {
          if (event.data.action) {
            switch (event.data.action) {
              case 'sendRoomNumberList':
                this.$refs.roomNumberList.updateRoomNumberList(event.data.response)
                break
              case 'sendChat':
                this.$refs.chatRoom.updateMessageList(event.data.response)
                break
              case 'loadChatLog':
                this.$refs.chatRoom.loadChatLog(event.data.response)
                break
              case 'initializeClient':
                this.initializeWebSocketClient()
                break
              case 'sendServerTime':
                this.$refs.roomNumberList.setTimeDifference(event.data.response.time)
                break
              case 'initializeChatRoom':
                this.$refs.chatRoom.initializeChatRoom(event.data.response)
                break
            }
          } else {
            this.$globalFunctions.notify({ content: '返回数据格式错误' })
          }
        } else {
          this.$globalFunctions.notify({ content: event.data.response })
        }
      }
    } else {
      this.$globalFunctions.notify({
        content: '您当前的浏览器不支持WebSocket。推荐使用Google Chrome浏览器访问本站'
      })
    }
  },
  destroyed () {
    this.websocketWorker.terminate()
  },
  methods: {
    initializeWebSocketClient () {
      const actionList = [
        {
          action: 'setClient',
          data: {
            client: 'BandoriStation',
            send_room_number: true
          }
        },
        {
          action: 'getRoomNumberList',
          data: null
        }
      ]
      if (this.$store.state.account.loginStatus) {
        actionList.push({
          action: 'setAccessPermission',
          data: {
            token: this.$store.state.account.token
          }
        })
      }
      this.websocketWorker.postMessage(actionList)
    },
    switchDialog (name, status) {
      this.$store.commit(
        'modal/dialog/setDisplay',
        {
          view: 'Home',
          function: name,
          isDisplay: status
        }
      )
    }
  }
}
</script>

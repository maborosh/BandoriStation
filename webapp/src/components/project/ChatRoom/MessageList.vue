<template>
  <div
    ref="messageList"
    class="message-list"
    :class="{ 'message-list-scroll-smooth': isScrollSmooth }"
    @scroll="checkLoadChatLog"
  >
    <template v-for="message in messageList">
      <UserMessage
        v-if="message.user_info.user_id > 0"
        :key="message.index"
        :message="message"
        :is-self-message="message.user_info.user_id === selfID"
        :display-message="true"
      />
      <SystemMessage
        v-else
        :key="message.index"
        :message="message"
      />
    </template>
  </div>
</template>

<script>
import UserMessage from '@/components/project/ChatRoom/Message/UserMessage'
import SystemMessage from '@/components/project/ChatRoom/Message/SystemMessage'

export default {
  name: 'MessageList',
  components: {
    UserMessage,
    SystemMessage
  },
  data () {
    return {
      messageList: [],
      selfID: 0,
      chatLogAppendFlag: false,
      earliestTimestamp: 0,
      isScrollSmooth: false
    }
  },
  methods: {
    initializeMessageList (data) {
      this.selfID = data.self_id
      this.chatLogAppendFlag = !data.is_end
      this.processNewMessage(data.message_list)
      this.setEarliestTime()
      this.scrollToBottom(true)
    },
    updateMessage (messageList) {
      this.processNewMessage(messageList)
      if (
        this.$refs.messageList.scrollHeight -
        this.$refs.messageList.clientHeight -
        this.$refs.messageList.scrollTop < 300
      ) {
        this.scrollToBottom()
      }
    },
    processNewMessage (newMessageList) {
      for (const message of newMessageList) {
        let latestTimestamp
        if (this.messageList.length > 0) {
          latestTimestamp = this.messageList[this.messageList.length - 1].timestamp
        } else {
          latestTimestamp = 0
        }
        if (message.timestamp - latestTimestamp > 300000) {
          this.pushMessage({
            timestamp: message.timestamp,
            content: new Date(message.timestamp).toLocaleString(),
            user_info: {
              user_id: 0
            }
          })
        }
        this.pushMessage(message)
      }
    },
    checkLoadChatLog () {
      if (this.$refs.messageList.scrollTop < 50 && this.chatLogAppendFlag) {
        this.chatLogAppendFlag = false
        this.$emit('getChatLog', this.earliestTimestamp)
      }
    },
    loadChatLog (data) {
      this.chatLogAppendFlag = !data.is_end
      this.processLogMessage(data.message_list)
      this.setEarliestTime()
    },
    processLogMessage (logMessageList) {
      const processedMessageList = []
      for (const message of logMessageList) {
        let latestTimestamp
        if (processedMessageList.length > 0) {
          latestTimestamp = processedMessageList[processedMessageList.length - 1].timestamp
        } else {
          latestTimestamp = 0
        }
        if (message.timestamp - latestTimestamp > 300000) {
          processedMessageList.push({
            timestamp: message.timestamp,
            content: new Date(message.timestamp).toLocaleString(),
            user_info: {
              user_id: 0
            }
          })
        }
        processedMessageList.push(message)
      }
      for (let i = processedMessageList.length - 1; i >= 0; i--) {
        this.pushMessage(processedMessageList[i], true)
      }
    },
    pushMessage (message, isChatLog = false) {
      message.index = message.user_info.user_id.toString() + message.timestamp.toString() + message.content
      if (isChatLog) {
        this.messageList.unshift(message)
      } else {
        this.messageList.push(message)
      }
    },
    clearData () {
      this.messageList = []
      this.isScrollSmooth = false
      this.chatLogAppendFlag = false
    },
    scrollToBottom (isSetSmooth = false) {
      this.$nextTick(() => {
        this.$refs.messageList.scrollTop = this.$refs.messageList.scrollHeight - this.$refs.messageList.clientHeight
        if (isSetSmooth) {
          this.isScrollSmooth = true
          this.chatLogAppendFlag = true
        }
      })
    },
    setEarliestTime () {
      if (this.messageList.length > 0) {
        this.earliestTimestamp = this.messageList[0].timestamp
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.message-list {
  overflow: {
    x: hidden;
    y: auto;
  }
  padding: {
    right: 1rem;
    left: 1rem;
  };
  position: relative;
}

.message-list-scroll-smooth {
  scroll-behavior: smooth;
}
</style>

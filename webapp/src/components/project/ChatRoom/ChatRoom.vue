<template>
  <transition name="modal-display">
    <div
      v-show="displayModal"
      class="modal-container"
    >
      <div class="modal-mask" />
      <div class="modal-dialog-container">
        <div
          class="modal-dialog-sub-container"
          :class="{ 'modal-dialog-sub-container-expand': isExpandDialog }"
        >
          <transition
            name="modal-dialog-display"
            @before-enter="beforeEnter"
            @after-leave="afterLeave"
          >
            <div
              v-show="displayModal"
              class="modal-dialog"
              :class="'modal-dialog-' + styleType"
              @click.stop=""
            >
              <div class="modal-header">
                <transition name="button-switch">
                  <font-awesome-icon
                    v-if="isExpandDialog === false"
                    id="modal-expand-button"
                    key="expand"
                    icon="expand"
                    class="modal-header-button"
                    @click="isExpandDialog = !isExpandDialog"
                  />
                  <font-awesome-icon
                    v-else
                    id="modal-compress-button"
                    key="compress"
                    icon="compress"
                    class="modal-header-button"
                    @click="isExpandDialog = !isExpandDialog"
                  />
                </transition>
                <font-awesome-icon
                  id="modal-close-button"
                  icon="times"
                  class="modal-header-button"
                  @click="hideModal"
                />
              </div>
              <div class="modal-content">
                <MessageList
                  id="message-list"
                  ref="messageList"
                  @getChatLog="getChatLog($event)"
                />
                <div
                  id="chat-room-input-area"
                  class="line-container"
                >
                  <div id="chat-room-input-text">
                    <Textarea1
                      v-model="message"
                      :area-style="{
                        height: '4rem'
                      }"
                      placeholder="按Ctrl+Enter键发送消息"
                      @keydown.enter.native="quickSendMessage"
                    />
                  </div>
                  <div id="chat-room-input-button">
                    <Button1
                      id="send-message-button"
                      color="3"
                      :config="{ type: 'button' }"
                      :disabled="message === ''"
                      @click="sendMessage"
                    >
                      发送
                    </Button1>
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import MessageList from '@/components/project/ChatRoom/MessageList'
import Textarea1 from '@/components/common/Textarea1'
import Button1 from '@/components/common/Button1'

export default {
  name: 'ChatRoom',
  components: {
    Button1,
    Textarea1,
    MessageList
  },
  props: {
    displayModal: {
      type: Boolean,
      default: false
    },
    styleType: {
      type: String,
      default: 'normal'
    },
    websocketWorker: Worker
  },
  data () {
    return {
      message: '',
      isExpandDialog: false
    }
  },
  methods: {
    beforeEnter () {
      this.websocketWorker.postMessage([
        {
          action: 'setClient',
          data: {
            send_chat: true
          }
        },
        {
          action: 'initializeChatRoom',
          data: null
        }
      ])
    },
    hideModal () {
      this.$emit('hideModalEvent')
    },
    afterLeave () {
      this.$emit('modalAfterHide')
      this.websocketWorker.postMessage({
        action: 'setClient',
        data: {
          send_chat: false
        }
      })
      this.$refs.messageList.clearData()
    },
    quickSendMessage (event) {
      if (event.ctrlKey && event.key === 'Enter' && this.message !== '') {
        this.sendMessage()
      }
    },
    sendMessage () {
      this.websocketWorker.postMessage({
        action: 'sendChat',
        data: {
          message: this.message
        }
      })
      this.message = ''
      this.$refs.messageList.scrollToBottom()
    },
    initializeChatRoom (data) {
      this.$refs.messageList.initializeMessageList(data)
    },
    updateMessageList (messageList) {
      this.$refs.messageList.updateMessage(messageList)
    },
    getChatLog (timestamp) {
      this.websocketWorker.postMessage({
        action: 'loadChatLog',
        data: {
          timestamp
        }
      })
    },
    loadChatLog (data) {
      this.$refs.messageList.loadChatLog(data)
    }
  }
}
</script>

<style lang="scss" scoped>
@import "../../../assets/styles/constants";

@keyframes fade-enter {
  0% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}

@keyframes bounce-in {
  0% {
    transform: translate(0, 100%);
  }
  100% {
    transform: translate(0, 0);
  }
}

.modal-display-enter-active {
  animation: fade-enter 0.3s;
}

.modal-display-leave-active {
  animation: fade-enter 0.3s reverse;
}

.modal-dialog-display-enter-active {
  animation: bounce-in 0.3s;
}

.modal-dialog-display-leave-active {
  animation: bounce-in 0.3s reverse;
}

.modal-container, .modal-mask, .modal-dialog-container {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}

.modal-container {
  position: fixed;
  z-index: 800;
}

.modal-mask {
  position: absolute;
  background-color: rgba(0, 0, 0, 0.2);
}

.modal-dialog-container {
  position: absolute;
}

.modal-dialog-sub-container {
  position: absolute;
  overflow: hidden;
  transition: 0.3s;
  right: 0;
  bottom: 0;
  left: 0;
  height: 65%;
}

.modal-dialog-sub-container-expand {
  height: calc(100% - 3rem);
  @media screen and (max-width: 500px) {
    height: calc(100% - 1rem);
  }
}

.modal-dialog {
  position: relative;
  margin: {
    left: auto;
    right: auto;
  };
  height: calc(100% - 3rem);
  background-color: #eaedf4;
  border-radius: 0.6rem;
  box-sizing: border-box;
  border: 0.1rem solid rgb(200, 200, 200);
  transition: 0.25s;
}

.modal-dialog-normal {
  width: 90rem;

  @media screen and (max-width: 930px) {
    width: calc(100% - 1.2rem);
  }

  @media screen and (max-width: 500px) {
    width: calc(100% - 1.2rem);
    height: calc(100% - 1rem);
  }
}

.modal-dialog-lite {
  width: 40rem;

  @media screen and (max-width: 411px) {
    margin-top: 1rem;
    width: calc(100% - 1.2rem);
  }
}

.modal-header {
  position: relative;
  height: 4rem;
  box-sizing: border-box;
  border-bottom: 0.1rem solid rgb(200, 200, 200);
}

.modal-header-button {
  position: absolute;
  cursor: pointer;
  top: 1.2rem;
}

#modal-expand-button {
  right: 4rem;
}

#modal-compress-button {
  right: 4rem;
}

#modal-close-button {
  right: 1.4rem;
}

.button-switch-enter-active, .button-switch-leave-active {
  transition: opacity 0.3s;
}
.button-switch-enter, .button-switch-leave-to {
  opacity: 0;
}

.modal-content {
  height: calc(100% - 4rem);
  box-sizing: border-box;
  padding: {
    right: 1rem;
    bottom: 2rem;
    left: 1rem;
  };

  @media screen and (max-width: 500px) {
    padding: {
      right: 0.4rem;
      bottom: 1.6rem;
      left: 0.4rem;
    };
  }
}

#message-list {
  height: calc(100% - 7rem);
  //background-color: #7cd2ff;
}

#chat-room-input-area {
  height: 6rem;
  padding: {
    left: 1rem;
    right: 1rem;
  };
}

#chat-room-input-text {
  display: inline-block;
  width: calc(100% - 6rem);
}

#chat-room-input-button {
  display: inline-block;
  vertical-align: bottom;
}

#send-message-button {
  height: 3rem;
  line-height: 3rem;
  width: 5rem;
  margin-left: 1rem;
}
</style>

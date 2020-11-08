<template>
  <div id="room-number-list-container">
    <transition-group
      name="flip-list"
      tag="div"
    >
      <RoomNumberMessage
        v-for="message in roomNumberList"
        :key="message.index"
        :message="message"
        @addBlockUser="addBlockUser($event)"
        @informUser="openInformUserDialog($event)"
      />
    </transition-group>
    <RoomNumberControlPanel />
    <div>
      <Modal2
        :display-modal="$store.state.modal.dialog.Home.sendRoomNumber"
        title="发送房间号"
        style-type="lite"
        @hideModalEvent="switchDialog('sendRoomNumber', false)"
        @modalAfterHide="initializeDialog('sendRoomNumber')"
      >
        <SendRoomNumber
          ref="sendRoomNumber"
          :websocket-worker="websocketWorker"
        />
      </Modal2>
      <Modal2
        :display-modal="$store.state.modal.dialog.Home.setRoomNumberFilter"
        title="筛选房间号"
        @hideModalEvent="switchDialog('setRoomNumberFilter', false)"
        @modalAfterHide="initializeDialog('roomNumberFilter')"
      >
        <RoomNumberFilter
          ref="roomNumberFilter"
          :filter="roomNumberFilter"
          @updateFilter="updateRoomNumberFilter()"
        />
      </Modal2>
      <Modal2
        :display-modal="$store.state.modal.dialog.Home.informUser"
        title="举报用户"
        style-type="lite"
        @hideModalEvent="switchDialog('informUser', false)"
        @modalAfterHide="initializeDialog('informUser')"
      >
        <InformUser ref="informUser" />
      </Modal2>
    </div>
  </div>
</template>

<script>
import RoomNumberMessage from '@/components/project/RoomNumber/RoomNumberMessage'
import RoomNumberControlPanel from '@/components/project/RoomNumber/RoomNumberControlPanel'
import Modal2 from '@/components/common/Modal2'
import SendRoomNumber from '@/components/project/RoomNumber/SendRoomNumber'
import RoomNumberFilter from '@/components/project/RoomNumber/RoomNumberFilter'
import InformUser from '@/components/project/RoomNumber/InformUser'
import { getRoomNumberFilter } from '@/network/mainAction'

export default {
  name: 'RoomNumberList',
  components: {
    RoomNumberMessage,
    RoomNumberControlPanel,
    Modal2,
    RoomNumberFilter,
    SendRoomNumber,
    InformUser
  },
  props: {
    websocketWorker: Worker
  },
  data () {
    return {
      roomNumberList: [],
      roomNumberIndex: 0,
      roomNumberFilter: {
        type: [],
        keyword: [],
        user: []
      },
      timeOffset: 0
    }
  },
  mounted () {
    this.updateRoomNumberFilter()
    setInterval(() => {
      this.updateIntervalTime()
    }, 200)
  },
  methods: {
    updateRoomNumberFilter () {
      if (this.$store.state.account.loginStatus) {
        getRoomNumberFilter(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token)
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            responseData => {
              if (responseData.room_number_filter) {
                if (responseData.room_number_filter.type &&
                  Array.isArray(responseData.room_number_filter.type)) {
                  this.roomNumberFilter.type = responseData.room_number_filter.type
                }
                if (responseData.room_number_filter.keyword &&
                  Array.isArray(responseData.room_number_filter.keyword)) {
                  this.roomNumberFilter.keyword = responseData.room_number_filter.keyword
                }
                if (responseData.room_number_filter.user &&
                  Array.isArray(responseData.room_number_filter.user)) {
                  this.roomNumberFilter.user = responseData.room_number_filter.user
                }
              }
              this.$refs.roomNumberFilter.initializeData()
            }
          )
        }).catch(
          error => {
            this.$globalFunctions.notify({ content: '请求失败' })
            console.log(error)
          }
        )
      } else {
        if (this.$cookies.isKey('roomNumberFilter')) {
          const filter = this.$cookies.get('roomNumberFilter')
          this.$cookies.set('roomNumberFilter', filter, 259200)
          if (filter.type &&
            Array.isArray(filter.type)) {
            this.roomNumberFilter.type = filter.type
          }
          if (filter.keyword &&
            Array.isArray(filter.keyword)) {
            this.roomNumberFilter.keyword = filter.keyword
          }
          if (filter.user &&
            Array.isArray(filter.user)) {
            this.roomNumberFilter.user = filter.user
          }
        }
        this.$refs.roomNumberFilter.initializeData()
      }
    },
    updateRoomNumberList (newData) {
      const currentTime = new Date().getTime()
      let latestTime
      if (this.roomNumberList.length > 0) {
        latestTime = this.roomNumberList[0].time
      } else {
        latestTime = 0
      }
      for (let i = 0; i < newData.length; i++) {
        if (newData[i].time > latestTime) {
          let filterCheck = true
          for (let j = 0; j < this.roomNumberFilter.type.length; j++) {
            if (newData[i].type === this.roomNumberFilter.type[j]) {
              filterCheck = false
              break
            }
          }
          for (let j = 0; j < this.roomNumberFilter.keyword.length; j++) {
            if (
              this.roomNumberFilter.keyword[j] !== '' &&
              newData[i].raw_message.indexOf(this.roomNumberFilter.keyword[j]) > -1
            ) {
              filterCheck = false
              break
            }
          }
          for (let j = 0; j < this.roomNumberFilter.user.length; j++) {
            if (
              newData[i].user_info.type === this.roomNumberFilter.user[j].type &&
              newData[i].user_info.user_id === this.roomNumberFilter.user[j].user_id
            ) {
              filterCheck = false
              break
            }
          }

          if (filterCheck) {
            newData[i].time_interval = this.translateIntervalTime(
              currentTime + this.timeOffset - newData[i].time
            )
            newData[i].index = this.roomNumberIndex++
            if (newData[i].source_info.name === 'BandoriStation') {
              newData[i].source_info.name = '本站'
            }
            if (newData[i].type === '25') {
              newData[i].type = '25万房'
            } else if (newData[i].type === '18') {
              newData[i].type = '18万大师房'
            } else if (newData[i].type === '12') {
              newData[i].type = '12万高手房'
            } else if (newData[i].type === '7') {
              newData[i].type = '7万常规房'
            } else {
              newData[i].type = ''
            }
            this.roomNumberList.unshift(newData[i])
          }
        }
      }
    },
    translateIntervalTime (intervalTime) {
      const second = Math.round(intervalTime / 1000)
      const minute = Math.floor(second / 60)
      if (minute > 0) {
        return minute + '分钟前'
      } else {
        return second + '秒前'
      }
    },
    updateIntervalTime () {
      const currentTime = new Date().getTime()
      for (let i = this.roomNumberList.length - 1; i >= 0; i--) {
        const intervalTime = currentTime + this.timeOffset - this.roomNumberList[i].time
        if (intervalTime <= 600000) {
          this.roomNumberList[i].time_interval = this.translateIntervalTime(intervalTime)
        } else {
          this.roomNumberList.pop()
        }
      }
    },
    setTimeDifference (serverTime) {
      const localTime = new Date().getTime()
      this.timeOffset = serverTime - localTime
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
    },
    initializeDialog (ref) {
      this.$refs[ref].initializeData()
    },
    addBlockUser (userInfo) {
      this.$refs.roomNumberFilter.addUser(userInfo)
    },
    openInformUserDialog (message) {
      this.$refs.informUser.updateMessage(message)
      this.switchDialog('informUser', true)
    }
  }
}
</script>

<style lang="scss" scoped>
.flip-list-enter {
  opacity: 0;
  transform: translateX(-60px);
}

.flip-list-leave-to {
  opacity: 0;
  transform: translateX(60px);
}

.flip-list-leave-active {
  //position: absolute;
}

#room-number-list-container {
  width: 80rem;
  margin: 6rem auto 1rem auto;
  transition: 0.25s;
  overflow-x: hidden;
  @media (max-width: 830px) {
    width: calc(100% - 1rem);
  }
  @media (max-width: 600px) {
    margin-top: 5rem;
    width: calc(100% - 1rem);
  }
}
</style>

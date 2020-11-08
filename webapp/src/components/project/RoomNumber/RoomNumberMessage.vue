<template>
  <div class="container">
    <div class="column">
      <div
        class="user-avatar"
        :style="avatarStyle"
      />
    </div>
    <div class="column column-follow">
      <div>
        <span class="user-info-username">{{ message.user_info.username }}</span>
        <span class="message-info text-follow">来自{{ message.source_info.name }}</span>
        <span class="message-info text-follow">{{ message.time_interval }}</span>
      </div>
      <div class="line-container">
        <span
          class="room-number-container"
          title="复制房间号"
          @click="copyRoomNumber"
        >
          <span class="room-number">{{ message.number }}</span>
          <font-awesome-icon icon="copy" />
        </span>
        <span class="room-number-type text-follow">{{ message.type }}</span>
      </div>
      <div class="line-container">
        <span class="raw-message">{{ message.raw_message }}</span>
      </div>
      <div class="line-container operation-button-container">
        <span
          class="operation-button"
          @click="blockUser"
        >屏蔽</span>
        <span
          class="operation-button operation-button-colored"
          @click="informUser"
        >举报</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ASSETS_URL } from '@/utilities/constants'
import userAvatarImage from '@/assets/images/user_avatar.png'
import qqUserAvatarImage from '@/assets/images/qq_user_avatar.png'

export default {
  name: 'RoomNumberMessage',
  props: {
    message: {
      type: Object,
      default () {
        return {}
      }
    }
  },
  computed: {
    avatarStyle: function () {
      if (this.message.user_info.avatar) {
        return {
          backgroundImage: 'url("' + ASSETS_URL + '/images/user-avatar/' + this.message.user_info.avatar + '")'
        }
      } else {
        if (this.message.user_info.type === 'qq') {
          return {
            backgroundImage: 'url("' + qqUserAvatarImage + '")'
          }
        } else {
          return {
            backgroundImage: 'url("' + userAvatarImage + '")'
          }
        }
      }
    }
  },
  methods: {
    copyRoomNumber () {
      this.$copyText(
        this.message.number
      ).then().catch(error => {
        this.$globalFunctions.notify({ content: '复制失败' })
        console.log(error)
      })
    },
    blockUser () {
      this.$emit('addBlockUser', this.message.user_info)
    },
    informUser () {
      if (this.$store.state.account.loginStatus) {
        this.$emit('informUser', this.message)
      } else {
        this.$router.push('/login')
      }
    }
  }
}
</script>

<style lang="scss" scoped>
@import "../../../assets/styles/constants";

.container {
  background-color: white;
  border: 0.1rem solid rgba(200, 200, 200, 0.5);
  border-radius: 0.6rem;
  margin-top: 1rem;
  padding: 1rem 1.2rem;
  transition: all 0.5s;
}

.column {
  display: inline-block;
  vertical-align: top;
}

.column-follow {
  margin-left: 1rem;
  width: calc(100% - 5.2rem);
}

.text-follow {
  margin-left: 0.6rem;
}

.user-avatar {
  height: 4rem;
  width: 4rem;
  border-radius: 2rem;
  background: {
    repeat: no-repeat;
    size: contain;
  };
}

.user-info-username {
  font-weight: bold;
}

.message-info {
  color: rgb(150, 150, 150);
  font-size: 1.5rem;
  user-select: none;
}

.room-number-container {
  cursor: pointer;
  .room-number {
    font-weight: bold;
    margin-right: 0.4rem;
  }
}

.operation-button-container {
  text-align: right;
  padding-right: 3.5rem;
  .operation-button {
    user-select: none;
    font-size: 14px;
    cursor: pointer;
    margin: 0 5px;
  }
  .operation-button-colored {
    color: $theme-color;
  }
}
</style>

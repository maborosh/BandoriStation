<template>
  <transition
    :name="isSelfMessage ? 'message-display-right' : 'message-display-left'"
    appear
  >
    <div
      v-show="displayMessage"
      class="container"
      :class="{ 'container-self-message': isSelfMessage }"
    >
      <div
        class="user-avatar"
        :class="{ 'user-avatar-self-message': isSelfMessage }"
        :style="avatarStyle"
      />
      <div
        class="content"
        :class="{ 'content-self-message': isSelfMessage }"
      >
        <div
          class="user-info"
          :class="{ 'user-info-self-message': isSelfMessage }"
        >
          <div
            v-if="message.user_info.title"
            class="user-title"
          />
          <div class="user-name">
            {{ message.user_info.username }}
          </div>
        </div>
        <div
          class="message-content"
          :class="{ 'message-content-self-message': isSelfMessage }"
        >
          {{ message.content }}
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import { ASSETS_URL } from '@/utilities/constants'
import userAvatarImage from '@/assets/images/user_avatar.png'

export default {
  name: 'UserMessage',
  props: {
    message: {
      type: Object,
      default () {
        return {}
      }
    },
    isSelfMessage: {
      type: Boolean,
      default: false
    },
    displayMessage: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    avatarStyle: function () {
      if (this.message.user_info.avatar) {
        return {
          backgroundImage: 'url("' + ASSETS_URL + '/images/user-avatar/' + this.message.user_info.avatar + '")'
        }
      } else {
        return {
          backgroundImage: 'url("' + userAvatarImage + '")'
        }
      }
    }
  }
}
</script>

<style lang="scss" scoped>
@keyframes cut-in-left {
  0% {
    opacity: 0;
    transform: translateX(-3rem);
  }
  100% {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes cut-in-right {
  0% {
    opacity: 0;
    transform: translateX(3rem);
  }
  100% {
    opacity: 1;
    transform: translateX(0);
  }
}

.message-display-left-enter-active {
  animation: cut-in-left 0.4s;
}

.message-display-right-enter-active {
  animation: cut-in-right 0.4s;
}

.container {
  position: relative;
  margin-top: 1.5rem;
  padding-right: 4.8rem;
}

.container-self-message {
  padding: {
    right: 0;
    left: 4.8rem;
  };
}

.content {
  margin-left: 4.8rem;
}

.content-self-message {
  margin: {
    left: 0;
    right: 4.8rem;
  };
  text-align: right;
}

.user-avatar {
  position: absolute;
  left: 0;
  height: 4rem;
  width: 4rem;
  border-radius: 2rem;
  background: {
    repeat: no-repeat;
    size: contain;
  };
}

.user-avatar-self-message {
  left: unset;
  right: 0;
}

.user-info {
  font-size: 1.2rem;
  padding-left: 1rem;
  white-space: nowrap;
}

.user-info-self-message {
  text-align: right;
  padding: {
    left: 0;
    right: 1rem;
  };
}

.user-title {
  display: inline-block;
  vertical-align: bottom;
  padding: 0.1rem 0.4rem;
  border-radius: 0.3rem;
  color: white;
}

.user-name {
  display: inline-block;
  overflow:hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  max-width: 20rem;
  vertical-align: bottom;
  padding: 0.1rem 0.2rem;
  color: rgb(150, 150, 150);
}

.message-content {
  position: relative;
  display: inline-block;
  background-color: white;
  margin-top: 0.3rem;
  padding: 0.5rem 0.8rem;
  border-radius: 0.6rem;
  white-space: pre-wrap;
  word-break: break-all;
  max-width: 50rem;
  line-height: normal;
  text-align: left;

  &:after {
    position: absolute;
    content: '';
    height: 0;
    width: 0;
    left: -1rem;
    top: 0.8rem;
    border: 0.5rem solid transparent;
    border-right-color: white;
  }
}

.message-content-self-message {
  background-color: #ffd1de;
  &:after {
    left: 100%;
    border-right-color: transparent;
    border-left-color: #ffd1de;
  }
}
</style>

<template>
  <transition name="modal-display">
    <div
      v-show="displayModal"
      class="modal-2-container"
    >
      <div class="modal-2-mask" />
      <div
        class="modal-2-dialog-container"
        @click="hideModal"
      >
        <transition
          name="modal-dialog-display"
          @after-leave="afterLeave"
        >
          <div
            v-show="displayModal"
            class="modal-2-dialog"
            :class="'modal-2-dialog-' + styleType"
            @click.stop=""
          >
            <div class="modal-2-header">
              <div class="modal-2-header-title">
                {{ title }}
              </div>
              <div
                class="modal-2-header-delete-button"
                @click="hideModal"
              >
                <font-awesome-icon icon="times" />
              </div>
            </div>
            <div class="modal-2-content">
              <slot />
            </div>
          </div>
        </transition>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'Modal2',
  props: {
    displayModal: {
      type: Boolean,
      default: false
    },
    styleType: {
      type: String,
      default: 'normal'
    },
    title: {
      type: String,
      default: ''
    }
  },
  methods: {
    hideModal () {
      this.$emit('hideModalEvent')
    },
    afterLeave () {
      this.$emit('modalAfterHide')
    }
  }
}
</script>

<style lang="scss" scoped>
@import "../../assets/styles/constants";

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
    transform: translate(0, -100%);
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

.modal-2-container, .modal-2-mask, .modal-2-dialog-container {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}

.modal-2-container {
  position: fixed;
  z-index: 1000;
}

.modal-2-mask {
  position: absolute;
  background-color: rgba(0, 0, 0, 0.2);
}

.modal-2-dialog-container {
  position: absolute;
  overflow: {
    x: hidden;
    y: auto;
  };
  -webkit-overflow-scrolling: touch;
}

.modal-2-dialog {
  position: relative;
  min-height: 10rem;
  background-color: white;
  margin: 3rem auto;
  border-radius: 0.6rem;
  box-sizing: border-box;
  border: 0.1rem solid rgb(200, 200, 200);
  transition: 0.25s;
}

.modal-2-dialog-normal {
  width: 90rem;

  @media screen and (max-width: 930px) {
    width: calc(100% - 1.2rem);
  }

  @media screen and (max-width: 500px) {
    margin: {
      top: 1rem;
      bottom: 1rem;
    }
    width: calc(100% - 1.2rem);
  }
}

.modal-2-dialog-lite {
  width: 40rem;

  @media screen and (max-width: 411px) {
    margin-top: 1rem;
    width: calc(100% - 1.2rem);
  }
}

.modal-2-header {
  .modal-2-header-title {
    color: rgb(100, 100, 100);
    font-size: 2.4rem;
    font-weight: bold;
    line-height: 3.6rem;
    padding: 1.6rem 3rem;
    position: relative;
    user-select: none;
    border-bottom: 0.1rem solid rgb(200, 200, 200);
  }
  .modal-2-header-delete-button {
    position: absolute;
    right: 3rem;
    top: 2.4rem;
    cursor: pointer;
  }
}

.modal-2-content {
  padding: {
    top: 2.5rem;
    right: 3rem;
    bottom: 2.5rem;
    left: 3rem;
  };
}
</style>

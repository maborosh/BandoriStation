<template>
  <transition
    name="modal-display"
    appear
    @after-leave="removeModal"
  >
    <div
      v-show="displayModal"
      class="modal-1-container"
    >
      <div class="modal-1-mask" />
      <div class="modal-1-dialog-container">
        <transition
          name="modal-dialog-display"
          appear
        >
          <div
            v-show="displayModal"
            class="modal-1-dialog"
          >
            <div class="modal-1-header">
              <div class="modal-1-header-icon" />
              <div class="modal-1-header-title">
                {{ title }}
              </div>
            </div>
            <div class="modal-1-content">
              <slot />
            </div>
            <div class="modal-1-footer">
              <div class="modal-1-footer-button-container">
                <Button1
                  ref="confirm"
                  class="button-1"
                  color="1"
                  :config="{
                    type: 'button'
                  }"
                  @click="hideModal('confirm')"
                >
                  确定
                </Button1>
              </div>
              <div
                v-if="displayCancel"
                class="modal-1-footer-button-container"
              >
                <Button1
                  class="button-1"
                  color="2"
                  :config="{
                    type: 'button'
                  }"
                  @click="hideModal('cancel')"
                >
                  取消
                </Button1>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </transition>
</template>

<script>
import Button1 from '@/components/common/Button1'

export default {
  name: 'Modal1',
  components: {
    Button1
  },
  props: {
    displayModal: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: '提示'
    },
    displayCancel: {
      type: Boolean,
      default: false
    }
  },
  mounted () {
    this.$refs.confirm.focus()
  },
  methods: {
    hideModal (type) {
      this.$emit('hideModalEvent', type)
    },
    removeModal () {
      this.$emit('removeModalEvent')
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
    transform: scale(0.6);
  }
  100% {
    transform: scale(1);
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

.modal-1-container, .modal-1-mask, .modal-1-dialog-container {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}

.modal-1-container {
  position: fixed;
  z-index: 2000;
}

.modal-1-mask {
  position: absolute;
  background-color: rgba(0, 0, 0, 0.2);
}

.modal-1-dialog-container {
   position: absolute;
   overflow: {
     x: hidden;
     y: auto;
   };
   -webkit-overflow-scrolling: touch;
 }

.modal-1-dialog {
  position: relative;
  width: 40rem;
  min-height: 10rem;
  background-color: white;
  margin: 9rem auto;
  border-radius: 0.6rem;
  padding: 2rem 3rem;
  box-sizing: border-box;
  border: 0.1rem solid rgb(200, 200, 200);
  transition: 0.25s;

  @media screen and (max-width: 411px) {
    width: calc(100% - 1.2rem);
  }
}

.modal-1-header {
  div {
    display: inline-block;
    vertical-align: top;
  }
  .modal-1-header-icon {
    height: 3.3rem;
    width: 2.1rem;
    background: {
      image: url("../../assets/images/exclamation_mark.png");
      repeat: no-repeat;
      size: auto;
    }
  }
  .modal-1-header-title {
    height: 3.3rem;
    width: calc(100% - 3.1rem);
    line-height: 3rem;
    padding-left: 1rem;
    font: {
      size: 2.2rem;
      weight: bold;
    }
    border-bottom: 0.2rem solid $theme_color;
    user-select: none;
  }
}

.modal-1-content {
  margin-top: 2rem;
}

.modal-1-footer {
  margin-top: 2rem;
  display: flex;
  justify-content: space-around;
  .modal-1-footer-button-container {
    width: 40%;
  }
}
</style>

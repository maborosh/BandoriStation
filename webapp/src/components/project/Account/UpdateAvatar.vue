<template>
  <div>
    <div>
      <Button1
        class="button-1"
        color="3"
        :config="{ type: 'button' }"
        @click.native="showFileChooser"
      >
        选择图片
      </Button1>
      <input
        ref="inputImage"
        type="file"
        name="image"
        accept="image/jpeg, image/png"
        style="display: none"
        @change="setImage"
      >
    </div>
    <div class="line-container">
      <div id="avatar-cropper-container">
        <font-awesome-icon
          v-if="src === ''"
          icon="image"
        />
        <vue-cropper
          v-show="src !== ''"
          ref="cropper"
          :aspect-ratio="1"
          :src="src"
          :view-mode="2"
          :container-style="{
            maxHeight: '40rem',
            width: '100%'
          }"
          :img-style="{
            maxHeight: '40rem',
            width: '100%'
          }"
          preview="#avatar-preview"
        />
      </div>
      <div
        id="avatar-preview-container"
        class="line-container"
      >
        <div
          id="avatar-preview"
          ref="avatarPreview"
        />
      </div>
    </div>
    <div class="line-container">
      <Button1
        class="button-1"
        color="1"
        :config="{ type: 'button' }"
        @click.native="uploadAvatar"
      >
        应用
      </Button1>
    </div>
  </div>
</template>

<script>
import VueCropper from 'vue-cropperjs'
import 'cropperjs/dist/cropper.css'
import Button1 from '@/components/common/Button1'
import scale from 'scale'
import { updateAvatar } from '@/network/accountManage'

export default {
  name: 'UpdateAvatar',
  components: {
    Button1,
    VueCropper
  },
  data () {
    return {
      src: ''
    }
  },
  methods: {
    initializeData () {
      this.src = ''
      this.$refs.avatarPreview.innerHTML = ''
    },
    showFileChooser () {
      this.$refs.inputImage.click()
    },
    setImage (e) {
      const file = e.target.files[0]
      if (!file) {
        return
      }
      if (file.type.indexOf('image/') === -1) {
        this.$globalFunctions.notify({ content: '请选择一个图片文件' })
      } else {
        if (typeof FileReader === 'function') {
          const reader = new FileReader()
          reader.onload = (event) => {
            this.src = event.target.result
            // rebuild cropperjs with the updated source
            this.$refs.cropper.replace(event.target.result)
          }
          reader.readAsDataURL(file)
        } else {
          this.$globalFunctions.notify({ content: '您的浏览器不支持文件读取' })
        }
      }
    },
    uploadAvatar () {
      if (this.src === '') {
        this.$globalFunctions.notify({ content: '请选择一个图片文件' })
      } else {
        const croppedImage = scale(
          { width: 200, height: 200 },
          this.$refs.cropper.getCroppedCanvas(),
          'png'
        ).src
        updateAvatar(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token),
          { image: croppedImage }
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            responseData => {
              this.$store.commit('account/setAvatar', responseData.avatar)
              this.$globalFunctions.notify({
                content: '设置成功',
                callback: () => {
                  this.$store.commit(
                    'modal/dialog/setDisplay',
                    {
                      view: 'Account',
                      function: 'updateAvatar',
                      isDisplay: false
                    }
                  )
                }
              })
            }
          )
        }).catch(error => {
          this.$globalFunctions.notify({ content: '请求失败' })
          console.log(error)
        })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
#avatar-cropper-container {
  width: calc(50% - 0.3rem);
  display: inline-block;
  vertical-align: top;
  min-height: 15.5rem;
  max-height: 40rem;
  line-height: 15.5rem;
  font-size: 4rem;
  text-align: center;
  color: #eeeeee;
  background-color: #d2d2d2;
  border-radius: 0.6rem;
  @media screen and (max-width: 600px) {
    width: 100%;
    display: block;
  }
}

#avatar-preview-container {
  width: calc(50% - 0.3rem);
  display: inline-block;
  @media screen and (max-width: 600px) {
    width: 100%;
    display: block;
  }
}

#avatar-preview {
  margin: 0 auto;
  height: 12rem;
  width: 12rem;
  border-radius: 6rem;
  overflow: hidden;
  background: {
    color: #d2d2d2;
    size: contain;
    repeat: no-repeat;
  };
}

.cropper-container,
.cropper-wrap-box,
.cropper-drag-box {
  border-radius: 0.6rem;
}
</style>

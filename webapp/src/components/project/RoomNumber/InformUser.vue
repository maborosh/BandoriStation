<template>
  <div>
    <div>确认举报用户“{{ username }}”？</div>
    <div class="line-container">
      <Textarea1
        v-model="reason"
        placeholder="举报原因"
        :area-style="{
          height: '8rem'
        }"
      />
    </div>
    <div class="line-container button-container">
      <div class="inner-button-container">
        <Button1
          class="button-1"
          color="1"
          :config="{ type: 'button' }"
          @click="confirmInformUser"
        >
          确定
        </Button1>
      </div>
      <div class="inner-button-container">
        <Button1
          class="button-1"
          color="2"
          :config="{ type: 'button' }"
          @click="cancelInformUser"
        >
          取消
        </Button1>
      </div>
    </div>
  </div>
</template>

<script>
import Textarea1 from '@/components/common/Textarea1'
import Button1 from '@/components/common/Button1'
import { informUser } from '@/network/mainAction'

export default {
  name: 'InformUser',
  components: { Button1, Textarea1 },
  data () {
    return {
      message: null,
      reason: ''
    }
  },
  computed: {
    username () {
      if (this.message) {
        return this.message.user_info.username
      } else {
        return ''
      }
    }
  },
  methods: {
    initializeData () {
      this.message = null
      this.reason = ''
    },
    updateMessage (newMessage) {
      this.message = newMessage
    },
    confirmInformUser () {
      if (!this.message) {
        this.$globalFunctions.notify({ content: '数据更新失败' })
      } else if (this.reason === '') {
        this.$globalFunctions.notify({ content: '请填写举报原因' })
      } else {
        informUser(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token),
          {
            type: this.message.user_info.type,
            user_id: this.message.user_info.user_id,
            raw_message: this.message,
            reason: this.reason
          }
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            () => {
              this.$globalFunctions.notify({
                content: '举报成功',
                callback: () => {
                  this.$store.commit(
                    'modal/dialog/setDisplay',
                    {
                      view: 'Home',
                      function: 'informUser',
                      isDisplay: false
                    }
                  )
                }
              })
            }
          )
        }).catch(
          error => {
            this.$globalFunctions.notify({ content: '请求失败' })
            console.log(error)
          }
        )
      }
    },
    cancelInformUser () {
      this.$store.commit(
        'modal/dialog/setDisplay',
        {
          view: 'Home',
          function: 'informUser',
          isDisplay: false
        }
      )
    }
  }
}
</script>

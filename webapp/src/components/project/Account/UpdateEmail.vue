<template>
  <div>
    <div>
      <Input1
        v-model="email"
        placeholder="新的邮箱地址"
        @keyup.enter.native="sendVerificationCode"
      />
    </div>
    <div class="line-container">
      <Button1
        class="button-1"
        color="3"
        :config="{ type: 'button' }"
        :disabled="sendVerificationCodeButton.disabled"
        @click.native="sendVerificationCode"
      >
        {{ sendVerificationCodeButton.text }}
      </Button1>
    </div>
    <div class="line-container">
      <Input1
        v-model="verificationCode"
        placeholder="验证码"
        @keyup.enter.native="confirmVerifyEmail"
      />
    </div>
    <div class="line-container">
      <Button1
        class="button-1"
        color="1"
        :config="{ type: 'button' }"
        @click.native="confirmVerifyEmail"
      >
        验证邮箱
      </Button1>
    </div>
  </div>
</template>

<script>
import Input1 from '@/components/common/Input1'
import Button1 from '@/components/common/Button1'
import { updateEmailSendVerificationCode, updateEmailVerifyEmail } from '@/network/accountManage'

export default {
  name: 'UpdateEmail',
  components: {
    Input1,
    Button1
  },
  data () {
    return {
      email: '',
      sendVerificationCodeButton: {
        text: '发送验证码',
        disabled: false
      },
      verificationCode: ''
    }
  },
  methods: {
    initializeData () {
      this.email = ''
      this.verificationCode = ''
    },
    sendVerificationCode () {
      if (!this.sendVerificationCodeButton.disabled) {
        updateEmailSendVerificationCode(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token),
          { email: this.email }
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            responseData => {
              this.sendVerificationCodeButton.disabled = true
              this.$store.commit(
                'modal/notice/pushNotice',
                { content: '已发送验证码至邮箱' + responseData.email }
              )
              this.$globalFunctions.buttonHold(
                60,
                countDownText => {
                  this.sendVerificationCodeButton.text = '重新发送' + countDownText
                },
                () => {
                  this.sendVerificationCodeButton.text = '发送验证码'
                  this.sendVerificationCodeButton.disabled = false
                }
              )
            }
          )
        }).catch(error => {
          this.$globalFunctions.notify({ content: '请求失败' })
          console.log(error)
        })
      }
    },
    confirmVerifyEmail () {
      if (!/^[0-9]{6}$/.test(this.verificationCode)) {
        this.$globalFunctions.notify({ content: '验证码必须为6位数字' })
      } else {
        updateEmailVerifyEmail(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token),
          { verification_code: this.verificationCode }
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            responseData => {
              this.$emit('updateEmail', responseData.email)
              this.$globalFunctions.notify({
                content: '设置成功',
                callback: () => {
                  this.$store.commit(
                    'modal/dialog/setDisplay',
                    {
                      view: 'Account',
                      function: 'updateEmail',
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

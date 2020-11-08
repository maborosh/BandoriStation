<template>
  <SectionContainer
    title="重置密码"
    :is-lite="true"
  >
    <div v-if="!isVerified">
      <div>
        <Input1
          v-model="email"
          placeholder="邮箱地址"
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
          下一步
        </Button1>
      </div>
    </div>
    <div v-else>
      <div class="line-container">
        <Input1
          ref="password"
          v-model="password"
          type="password"
          placeholder="密码"
          @keyup.enter.native="setFocus('repeatPassword')"
        />
      </div>
      <div class="line-container">
        <Input1
          ref="repeatPassword"
          v-model="repeatPassword"
          type="password"
          placeholder="再次输入密码"
          @keyup.enter.native="confirmResetPassword"
        />
      </div>
      <div class="line-container">
        <Button1
          class="button-1"
          color="1"
          :config="{ type: 'button' }"
          @click.native="confirmResetPassword"
        >
          重置密码
        </Button1>
      </div>
    </div>
  </SectionContainer>
</template>

<script>
import SectionContainer from '@/components/common/SectionContainer'
import Input1 from '@/components/common/Input1'
import Button1 from '@/components/common/Button1'
import {
  resetPasswordSendEmailVerificationCode,
  resetPasswordVerifyEmail,
  resetPassword
} from '@/network/userLogin'

export default {
  name: 'ResetPassword',
  components: {
    SectionContainer,
    Input1,
    Button1
  },
  data () {
    return {
      isVerified: false,
      email: '',
      verificationCode: '',
      sendVerificationCodeButton: {
        text: '发送验证码',
        disabled: false
      },
      password: '',
      repeatPassword: ''
    }
  },
  methods: {
    sendVerificationCode () {
      if (!this.sendVerificationCodeButton.disabled) {
        resetPasswordSendEmailVerificationCode(
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
        resetPasswordVerifyEmail(
          { email: this.email, verification_code: this.verificationCode }
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            responseData => {
              this.$store.commit('account/setToken', responseData.token)
              this.isVerified = true
            }
          )
        }).catch(error => {
          this.$globalFunctions.notify({ content: '请求失败' })
          console.log(error)
        })
      }
    },
    confirmResetPassword () {
      if (this.password.length < 6) {
        this.$globalFunctions.notify({ content: '密码不能小于6位' })
      } else if (this.password !== this.repeatPassword) {
        this.$globalFunctions.notify({ content: '两次输入的密码不一致，请重新输入密码' })
      } else {
        resetPassword(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token),
          { password: this.password }
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            () => {
              this.$globalFunctions.notify({
                content: '重置成功',
                callback: () => {
                  this.$router.push('login')
                }
              })
            }
          )
        }).catch(error => {
          this.$globalFunctions.notify({ content: '请求失败' })
          console.log(error)
        })
      }
    },
    setFocus (ref) {
      this.$refs[ref].focus()
    }
  }
}
</script>

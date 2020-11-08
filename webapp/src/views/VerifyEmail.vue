<template>
  <div>
    <SectionContainer
      title="邮箱验证"
      :is-lite="true"
    >
      <div>验证邮箱之后才能继续登录</div>
      <div>
        <Input1
          v-model="email"
          placeholder="邮箱地址"
          :disabled="true"
        />
      </div>
      <div class="line-container">
        <Button1
          class="button-1"
          color="3"
          :config="{ type: 'button' }"
          @click.native="switchChangeEmailDialog(true)"
        >
          修改邮箱
        </Button1>
      </div>
      <div class="line-container">
        <Button1
          class="button-1"
          color="1"
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
    </SectionContainer>
    <div>
      <Modal2
        :display-modal="$store.state.modal.dialog.VerifyEmail.changeEmail"
        :style-type="'lite'"
        title="修改邮箱"
        @hideModalEvent="switchChangeEmailDialog(false)"
      >
        <Input1
          v-model="newEmail"
          placeholder="新的邮箱地址"
        />
        <div class="line-container dialog-confirm-button-container">
          <div class="dialog-confirm-button-single-container">
            <Button1
              class="button-1"
              color="1"
              :config="{ type: 'button' }"
              @click.native="confirmChangeEmail"
            >
              修改邮箱
            </Button1>
          </div>
          <div class="dialog-confirm-button-single-container">
            <Button1
              class="button-1"
              color="2"
              :config="{ type: 'button' }"
              @click.native="switchChangeEmailDialog(false)"
            >
              取消
            </Button1>
          </div>
        </div>
      </Modal2>
    </div>
  </div>
</template>

<script>
import SectionContainer from '@/components/common/SectionContainer'
import Input1 from '@/components/common/Input1'
import Button1 from '@/components/common/Button1'
import Modal2 from '@/components/common/Modal2'
import {
  getCurrentEmail,
  changeEmail,
  sendEmailVerificationCode,
  verifyEmail
} from '@/network/userLogin'

export default {
  name: 'VerifyEmail',
  components: {
    SectionContainer,
    Input1,
    Button1,
    Modal2
  },
  data () {
    return {
      email: '',
      newEmail: '',
      sendVerificationCodeButton: {
        text: '发送验证码',
        disabled: false
      },
      verificationCode: ''
    }
  },
  created () {
    if (this.$store.state.account.loginStatus) {
      this.$router.push('/')
      return
    }
    this.$store.commit('navbar/setMenuDisplay', false)
    getCurrentEmail(
      this.$globalFunctions.generateRequestHeader(this.$store.state.account.token)
    ).then(response => {
      this.$globalFunctions.handleAPIResponse(
        response,
        responseData => {
          if (responseData.email !== undefined) {
            this.email = responseData.email
          } else {
            this.$globalFunctions.notify({ content: '未知错误' })
          }
        }
      )
    }).catch(error => {
      this.$globalFunctions.notify({ content: '请求失败' })
      console.log(error)
    })
  },
  methods: {
    switchChangeEmailDialog (status) {
      this.$store.commit(
        'modal/dialog/setDisplay',
        {
          view: 'VerifyEmail',
          function: 'changeEmail',
          isDisplay: status
        }
      )
      if (status) {
        this.newEmail = ''
      }
    },
    confirmChangeEmail () {
      if (!/^(\w-*\.*)+@(\w-?)+(\.[a-z]{2,})+$/.test(this.newEmail)) {
        this.$globalFunctions.notify({ content: '请填入正确的邮箱地址' })
      } else {
        changeEmail(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token),
          { email: this.newEmail }
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            responseData => {
              this.email = responseData.new_email
              this.$store.commit(
                'modal/notice/pushNotice',
                {
                  content: '修改成功',
                  callback: () => {
                    this.$store.commit(
                      'modal/dialog/setDisplay',
                      {
                        view: 'VerifyEmail',
                        function: 'changeEmail',
                        isDisplay: false
                      }
                    )
                  }
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
    sendVerificationCode () {
      if (!this.sendVerificationCodeButton.disabled) {
        sendEmailVerificationCode(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token)
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
        verifyEmail(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token),
          { verification_code: this.verificationCode }
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            responseData => {
              this.$store.commit('account/setToken', responseData.token)
              this.$cookies.set('token', responseData.token, 259200)
              this.$store.commit('account/setLoginStatus', true)
              this.$globalFunctions.notify({
                content: '验证成功',
                callback: () => {
                  if (
                    this.$store.state.route.latestPage.name === 'Login' ||
                    this.$store.state.route.latestPage.name === 'ResetPassword' ||
                    this.$store.state.route.latestPage.name === 'SignUp' ||
                    this.$store.state.route.latestPage.name === 'VerifyEmail'
                  ) {
                    this.$router.push('/')
                  } else {
                    this.$router.push({ path: this.$store.state.route.latestPage.path })
                  }
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

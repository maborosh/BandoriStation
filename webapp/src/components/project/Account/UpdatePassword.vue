<template>
  <div>
    <div>
      <Input1
        ref="password"
        v-model="password"
        type="password"
        placeholder="原密码"
        @keyup.enter.native="setFocus('newPassword')"
      />
    </div>
    <div class="line-container">
      <Input1
        ref="newPassword"
        v-model="newPassword"
        type="password"
        placeholder="新密码"
        @keyup.enter.native="setFocus('repeatPassword')"
      />
    </div>
    <div class="line-container">
      <Input1
        ref="repeatPassword"
        v-model="repeatPassword"
        type="password"
        placeholder="再次输入密码"
        @keyup.enter.native="confirmUpdatePassword"
      />
    </div>
    <div class="line-container">
      <Button1
        class="button-1"
        color="1"
        :config="{ type: 'button' }"
        @click="confirmUpdatePassword"
      >
        应用
      </Button1>
    </div>
  </div>
</template>

<script>
import Input1 from '@/components/common/Input1'
import Button1 from '@/components/common/Button1'
import { updatePassword } from '@/network/accountManage'

export default {
  name: 'UpdatePassword',
  components: {
    Input1,
    Button1
  },
  data () {
    return {
      password: '',
      newPassword: '',
      repeatPassword: ''
    }
  },
  methods: {
    initializeData () {
      this.password = ''
      this.newPassword = ''
      this.repeatPassword = ''
    },
    confirmUpdatePassword () {
      if (this.password === '') {
        this.$globalFunctions.notify({ content: '请输入原密码' })
      } else if (this.newPassword === '') {
        this.$globalFunctions.notify({ content: '请输入新密码' })
      } else if (this.newPassword.length < 6) {
        this.$globalFunctions.notify({ content: '密码不能小于6位' })
      } else if (this.newPassword !== this.repeatPassword) {
        this.$globalFunctions.notify({ content: '两次输入的密码不一致，请重新输入' })
      } else {
        updatePassword(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token),
          {
            password: this.password,
            new_password: this.newPassword
          }
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            () => {
              this.$globalFunctions.notify({
                content: '设置成功',
                callback: () => {
                  this.$store.commit(
                    'modal/dialog/setDisplay',
                    {
                      view: 'Account',
                      function: 'updatePassword',
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
    setFocus (ref) {
      this.$refs[ref].focus()
    }
  }
}
</script>

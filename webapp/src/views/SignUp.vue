<template>
  <SectionContainer
    title="注册"
    :is-lite="true"
  >
    <div>
      <Input1
        ref="username"
        v-model="username"
        placeholder="用户名"
        @keyup.enter.native="setFocus('password')"
      />
    </div>
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
        @keyup.enter.native="setFocus('email')"
      />
    </div>
    <div class="line-container">
      <Input1
        ref="email"
        v-model="email"
        placeholder="邮箱地址"
        @keyup.enter.native="signupAccount"
      />
    </div>
    <div class="line-container">
      <Button1
        class="button-1"
        color="1"
        :config="{
          type: 'button'
        }"
        @click.native="signupAccount"
      >
        注册
      </Button1>
    </div>
  </SectionContainer>
</template>

<script>
import SectionContainer from '@/components/common/SectionContainer'
import Input1 from '@/components/common/Input1'
import Button1 from '@/components/common/Button1'
import { signup } from '@/network/userLogin'

export default {
  name: 'SignUp',
  components: {
    SectionContainer,
    Input1,
    Button1
  },
  data () {
    return {
      username: '',
      password: '',
      repeatPassword: '',
      email: ''
    }
  },
  created () {
    if (this.$store.state.account.loginStatus) {
      this.$router.push('/')
      return
    }
    this.$store.commit('navbar/setMenuDisplay', false)
  },
  methods: {
    signupAccount () {
      if (this.username === '') {
        this.$globalFunctions.notify({ content: '用户名不能为空' })
      } else if (/^(\w-*\.*)+@(\w-?)+(\.[a-z]{2,})+$/.test(this.username)) {
        this.$globalFunctions.notify({ content: '用户名不能是邮箱地址' })
      } else if (this.password.length < 6) {
        this.$globalFunctions.notify({ content: '密码不能小于6位' })
      } else if (this.password !== this.repeatPassword) {
        this.$globalFunctions.notify({ content: '两次输入的密码不一致，请重新输入密码' })
      } else if (!/^(\w-*\.*)+@(\w-?)+(\.[a-z]{2,})+$/.test(this.email)) {
        this.$globalFunctions.notify({ content: '请填入正确的邮箱地址' })
      } else {
        signup({
          username: this.username,
          password: this.password,
          email: this.email
        }).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            responseData => {
              this.$store.commit('account/setToken', responseData.token)
              if (responseData.redirect_to !== undefined) {
                this.$router.push(responseData.redirect_to)
              } else {
                this.$globalFunctions.notify({ content: '未知错误' })
              }
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

<style scoped>

</style>

<template>
  <SectionContainer
    title="登陆"
    :is-lite="true"
  >
    <div>
      <Input1
        ref="username"
        v-model="username"
        placeholder="用户名或邮箱地址"
        @keyup.enter.native="setFocus('password')"
      />
    </div>
    <div class="line-container">
      <Input1
        ref="password"
        v-model="password"
        type="password"
        placeholder="密码"
        @keyup.enter.native="loginAccount"
      />
    </div>
    <div class="line-container">
      <Button1
        class="button-1"
        color="1"
        :config="{
          type: 'button'
        }"
        @click.native="loginAccount"
      >
        登陆
      </Button1>
    </div>
    <div class="line-container">
      <Button1
        class="button-1"
        color="2"
        :config="{
          type: 'link',
          path: '/sign-up'
        }"
      >
        注册
      </Button1>
    </div>
    <div
      class="line-container"
      style="text-align: right"
    >
      <router-link
        to="/reset-password"
        class="hyperlink"
      >
        忘记密码？
      </router-link>
    </div>
  </SectionContainer>
</template>

<script>
import SectionContainer from '@/components/common/SectionContainer'
import Input1 from '@/components/common/Input1'
import Button1 from '@/components/common/Button1'

import { login } from '@/network/userLogin.js'

export default {
  name: 'Login',
  components: {
    SectionContainer,
    Input1,
    Button1
  },
  data () {
    return {
      username: '',
      password: ''
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
    loginAccount () {
      if (this.username === '') {
        this.$globalFunctions.notify({ content: '用户名不能为空' })
      } else if (this.password === '') {
        this.$globalFunctions.notify({ content: '请输入密码' })
      } else {
        login({
          username: this.username,
          password: this.password
        }).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            responseData => {
              this.$store.commit('account/setToken', responseData.token)
              if (responseData.redirect_to !== undefined) {
                this.$router.push(responseData.redirect_to)
              } else {
                this.$cookies.set('token', responseData.token, 259200)
                this.$store.commit('account/setAvatar', responseData.avatar)
                this.$store.commit('account/setLoginStatus', true)
                this.$store.commit('account/setInitializeStatus', true)
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

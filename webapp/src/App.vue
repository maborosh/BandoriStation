<template>
  <div id="app">
    <Navbar />
    <router-view v-if="$store.state.account.initialized" />
    <NoticeContainer />
    <Background />
  </div>
</template>

<script>
import Navbar from '@/components/project/Navbar/Navbar'
import NoticeContainer from '@/components/project/NoticeContainer'
import { initializeAccountSetting } from '@/network/mainAction'
import Background from '@/components/project/Background'

export default {
  components: {
    Background,
    Navbar,
    NoticeContainer
  },
  created () {
    if (this.$cookies.isKey('token')) {
      const token = this.$cookies.get('token')
      initializeAccountSetting(
        this.$globalFunctions.generateRequestHeader(token)
      ).then(response => {
        this.$globalFunctions.handleAPIResponse(
          response,
          responseData => {
            this.$store.commit('account/setToken', token)
            this.$store.commit('account/setAvatar', responseData.avatar)
            this.$store.commit('account/setLoginStatus', true)
            this.$store.commit('account/setInitializeStatus', true)
          },
          () => {
            this.$cookies.remove('token')
            this.$store.commit('account/setInitializeStatus', true)
          }
        )
      }).catch(error => {
        this.$globalFunctions.notify({ content: '请求失败' })
        console.log(error)
      })
    } else {
      this.$store.commit('account/setAvatar', null)
      this.$store.commit('account/setInitializeStatus', true)
    }
  }
}
</script>

<style lang="scss">
@import "assets/styles/index";
</style>

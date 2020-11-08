<template>
  <div>
    <div>
      <Input1
        v-model="username"
        placeholder="新的用户名"
        @keyup.enter.native="confirmUpdateUsername"
      />
    </div>
    <div class="line-container">
      <Button1
        class="button-1"
        color="1"
        :config="{ type: 'button' }"
        @click="confirmUpdateUsername"
      >
        应用
      </Button1>
    </div>
  </div>
</template>

<script>
import Input1 from '@/components/common/Input1'
import Button1 from '@/components/common/Button1'
import { updateUsername } from '@/network/accountManage'

export default {
  name: 'UpdateUsername',
  components: {
    Input1,
    Button1
  },
  data () {
    return {
      username: ''
    }
  },
  methods: {
    initializeData () {
      this.username = ''
    },
    confirmUpdateUsername () {
      if (this.username === '') {
        this.$globalFunctions.notify({ content: '请输入新的用户名' })
      } else {
        updateUsername(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token),
          { username: this.username }
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            responseData => {
              this.$emit('updateUsername', responseData.username)
              this.$globalFunctions.notify({
                content: '设置成功',
                callback: () => {
                  this.$store.commit(
                    'modal/dialog/setDisplay',
                    {
                      view: 'Account',
                      function: 'updateUsername',
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

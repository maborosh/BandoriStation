<template>
  <div>
    <div>
      <Input1
        v-model="qq"
        placeholder="QQ号"
        @keyup.enter.native="confirmBindQQ"
      />
    </div>
    <div class="line-container">
      <Button1
        class="button-1"
        color="1"
        :config="{ type: 'button' }"
        @click="confirmBindQQ"
      >
        绑定
      </Button1>
    </div>
  </div>
</template>

<script>
import Input1 from '@/components/common/Input1'
import Button1 from '@/components/common/Button1'
import { bindQQ } from '@/network/accountManage'

export default {
  name: 'BindQQ',
  components: {
    Input1,
    Button1
  },
  data () {
    return {
      qq: ''
    }
  },
  methods: {
    initializeData () {
      this.username = ''
    },
    confirmBindQQ () {
      if (this.qq === '') {
        this.$globalFunctions.notify({ content: '请输入QQ号' })
      } else {
        bindQQ(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token),
          { qq: parseInt(this.qq) }
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            responseData => {
              this.$emit('updateQQ', responseData.qq)
              this.$globalFunctions.notify({
                content: '绑定成功',
                callback: () => {
                  this.$store.commit(
                    'modal/dialog/setDisplay',
                    {
                      view: 'Account',
                      function: 'bindQQ',
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

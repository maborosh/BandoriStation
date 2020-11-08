<template>
  <div id="control-panel">
    <div>
      <Button1
        class="button-2"
        color="3"
        shape="circle"
        :config="{ type: 'button' }"
        @click.native="switchDialog('openChatRoom', true, true)"
      >
        <font-awesome-icon icon="comment" />
      </Button1>
    </div>
    <div class="line-container-lite">
      <Button1
        class="button-2"
        color="1"
        shape="circle"
        :config="{ type: 'button' }"
        @click.native="switchDialog('setRoomNumberFilter', true)"
      >
        <font-awesome-icon icon="filter" />
      </Button1>
    </div>
    <div class="line-container-lite">
      <Button1
        class="button-2"
        color="1"
        shape="circle"
        :config="{ type: 'button' }"
        @click.native="switchDialog('sendRoomNumber', true, true)"
      >
        <font-awesome-icon icon="plus" />
      </Button1>
    </div>
  </div>
</template>

<script>
import Button1 from '@/components/common/Button1'

export default {
  name: 'RoomNumberControlPanel',
  components: {
    Button1
  },
  methods: {
    switchDialog (name, status, checkLogin = false) {
      if (checkLogin) {
        if (!this.$store.state.account.loginStatus) {
          this.$router.push('/login')
          return
        }
      }
      this.$store.commit(
        'modal/dialog/setDisplay',
        {
          view: 'Home',
          function: name,
          isDisplay: status
        }
      )
    }
  }
}
</script>

<style lang="scss" scoped>
#control-panel {
  position: fixed;
  right: calc(50% - 46rem);
  bottom: 10rem;
  transition: 0.4s;
  @media (max-width: 960px) {
    right: 10px;
    bottom: 20px;
  }
}
</style>

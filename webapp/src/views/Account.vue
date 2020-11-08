<template>
  <div>
    <SectionContainer title="个人中心">
      <section class="table">
        <div
          class="table-row"
          @click="switchDialog('updateAvatar', true)"
        >
          <div class="table-row-title">
            头像
          </div>
          <div class="table-row-content">
            <div id="account-user-avatar-container">
              <div
                v-if="$store.state.account.avatar"
                id="account-user-avatar"
                :style="{ backgroundImage: 'url(' + $store.state.account.avatar + ')' }"
              />
            </div>
          </div>
        </div>
        <div
          class="table-row"
          @click="switchDialog('updateUsername', true)"
        >
          <div class="table-row-title">
            用户名
          </div>
          <div class="table-row-content">
            {{ username }}
          </div>
        </div>
        <div
          class="table-row"
          @click="switchDialog('updatePassword', true)"
        >
          <div class="table-row-title">
            密码
          </div>
          <div class="table-row-content">
            ********
          </div>
        </div>
        <div
          class="table-row"
          @click="switchDialog('updateEmail', true)"
        >
          <div class="table-row-title">
            电子邮件
          </div>
          <div class="table-row-content">
            {{ email }}
          </div>
        </div>
        <div
          class="table-row"
          @click="switchDialog('bindQQ', true)"
        >
          <div class="table-row-title">
            QQ
          </div>
          <div class="table-row-content">
            {{ qq }}
          </div>
        </div>
      </section>
    </SectionContainer>
    <div>
      <Modal2
        :display-modal="$store.state.modal.dialog.Account.updateAvatar"
        title="更换头像"
        @hideModalEvent="switchDialog('updateAvatar', false)"
        @modalAfterHide="initializeDialog('updateAvatar')"
      >
        <UpdateAvatar ref="updateAvatar" />
      </Modal2>
      <Modal2
        :display-modal="$store.state.modal.dialog.Account.updateUsername"
        title="更改用户名"
        style-type="lite"
        @hideModalEvent="switchDialog('updateUsername', false)"
        @modalAfterHide="initializeDialog('updateUsername')"
      >
        <UpdateUsername
          ref="updateUsername"
          @updateUsername="updateUsername($event)"
        />
      </Modal2>
      <Modal2
        :display-modal="$store.state.modal.dialog.Account.updatePassword"
        title="更改密码"
        style-type="lite"
        @hideModalEvent="switchDialog('updatePassword', false)"
        @modalAfterHide="initializeDialog('updatePassword')"
      >
        <UpdatePassword ref="updatePassword" />
      </Modal2>
      <Modal2
        :display-modal="$store.state.modal.dialog.Account.updateEmail"
        title="更改邮箱"
        style-type="lite"
        @hideModalEvent="switchDialog('updateEmail', false)"
        @modalAfterHide="initializeDialog('updateEmail')"
      >
        <UpdateEmail
          ref="updateEmail"
          @updateEmail="updateEmail($event)"
        />
      </Modal2>
      <Modal2
        :display-modal="$store.state.modal.dialog.Account.bindQQ"
        title="绑定QQ"
        style-type="lite"
        @hideModalEvent="switchDialog('bindQQ', false)"
        @modalAfterHide="initializeDialog('bindQQ')"
      >
        <BindQQ
          ref="bindQQ"
          @updateQQ="updateQQ($event)"
        />
      </Modal2>
    </div>
  </div>
</template>

<script>
import SectionContainer from '@/components/common/SectionContainer'
import Modal2 from '@/components/common/Modal2'
import UpdateAvatar from '@/components/project/Account/UpdateAvatar'
import UpdateUsername from '@/components/project/Account/UpdateUsername'
import UpdatePassword from '@/components/project/Account/UpdatePassword'
import UpdateEmail from '@/components/project/Account/UpdateEmail'
import BindQQ from '@/components/project/Account/BindQQ'
import { getInitialData } from '@/network/accountManage'

export default {
  name: 'Account',
  components: {
    Modal2,
    SectionContainer,
    UpdateAvatar,
    UpdateUsername,
    UpdatePassword,
    UpdateEmail,
    BindQQ
  },
  data () {
    return {
      username: '',
      email: '',
      qq: ''
    }
  },
  created () {
    this.$store.commit('navbar/setMenuDisplay', true)
    this.initialize()
  },
  methods: {
    initialize () {
      if (!this.$store.state.account.loginStatus) {
        this.$router.push('/login')
      } else {
        getInitialData(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token)
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            responseData => {
              this.username = responseData.username
              this.email = responseData.email
              this.qq = responseData.qq
            }
          )
        }).catch(error => {
          this.$globalFunctions.notify({ content: '请求失败' })
          console.log(error)
        })
      }
    },
    switchDialog (name, status) {
      this.$store.commit(
        'modal/dialog/setDisplay',
        {
          view: 'Account',
          function: name,
          isDisplay: status
        }
      )
    },
    initializeDialog (ref) {
      this.$refs[ref].initializeData()
    },
    updateUsername (username) {
      this.username = username
    },
    updateEmail (email) {
      this.email = email
    },
    updateQQ (qq) {
      this.qq = qq
    }
  }
}
</script>

<style lang="scss" scoped>
#account-user-avatar-container {
  width: 12rem;
  height: 12rem;
  display: inline-block;
  border-radius: 6rem;
  background-size: contain;
  background-repeat: no-repeat;
  #account-user-avatar {
    width: 12rem;
    height: 12rem;
    border-radius: 6rem;
    background-size: contain;
    background-repeat: no-repeat;
  }
}

</style>

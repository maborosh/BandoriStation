<template>
  <div
    id="navbar-popup-menu-mask"
    class="mask"
    @click="hideMenu"
  >
    <div id="navbar-popup-menu">
      <template v-for="menuItem in $store.getters.menuList">
        <router-link
          v-if="menuItem.type === 'link'"
          :key="menuItem.title"
          :to="menuItem.path"
          class="link-block navbar-popup-menu-item"
          :class="menuItemClass(menuItem.style)"
        >
          {{ menuItem.title }}
        </router-link>
        <a
          v-else
          :key="menuItem.title"
          class="link-block navbar-popup-menu-item"
          :class="menuItemClass(menuItem.style)"
          @click="handleMenuItemClick(menuItem.function)"
        >
          {{ menuItem.title }}
        </a>
      </template>
    </div>
  </div>
</template>

<script>
import { logout } from '@/network/userLogin'

export default {
  name: 'NavbarPopupMenu',
  methods: {
    hideMenu () {
      this.$emit('hideMenuEvent')
    },
    menuItemClass (style) {
      if (style === 'top') {
        return 'navbar-popup-menu-item-top'
      } else if (style === 'bottom') {
        return 'navbar-popup-menu-item-bottom'
      } else {
        return ''
      }
    },
    handleMenuItemClick (func) {
      if (func === 'logoutAccount') {
        this.logoutAccount()
      }
    },
    logoutAccount () {
      if (this.$store.state.account.loginStatus) {
        logout(
          this.$globalFunctions.generateRequestHeader(this.$store.state.account.token)
        ).then(response => {
          this.$globalFunctions.handleAPIResponse(
            response,
            () => {
              this.$cookies.remove('token')
              this.$store.commit('account/setAvatar', null)
              this.$store.commit('account/setLoginStatus', false)
              if (this.$store.state.route.currentPage.name === 'Home') {
                location.reload()
              } else if (this.$store.state.route.currentPage.name === 'Account') {
                this.$router.push('/')
              }
            }
          )
        }).catch(error => {
          this.$globalFunctions.notify({ content: '请求失败' })
          console.log(error)
        })
      } else {
        this.$globalFunctions.notify({ content: '用户尚未登陆，无法退出' })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
#navbar-popup-menu-mask {
  z-index: 1000;
}

#navbar-popup-menu {
  position: absolute;
  top: 5.5rem;
  right: 1.8rem;
  text-align: left;
  color: #24292e;
  background-color: white;
  z-index: 1010;
  line-height: 4rem;
  border: 0.1rem solid rgba(0, 0, 0, 0.15);
  border-radius: 0.6rem;
  cursor: pointer;

  &:after, &:before {
    border: solid transparent;
    content: ' ';
    height: 0;
    bottom: 100%;
    position: absolute;
    width: 0;
  }

  &:after {
    border-width: 1rem;
    border-bottom-color: #fff;
    right: 1.1rem;
  }

  &:before {
    border-width: 1.1rem;
    border-bottom-color: rgba(0, 0, 0, 0.15);
    right: 1rem;
  }
}

.navbar-popup-menu-item {
  font-size: 1.4rem;
  padding: 0 1rem;
  transition: 0.25s;

  &:hover {
    background-color: rgba(0, 0, 0, 0.12);
  }
}

.navbar-popup-menu-item-top {
  &:hover {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
  }
}

.navbar-popup-menu-item-bottom {
  &:hover {
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
  }
}
</style>

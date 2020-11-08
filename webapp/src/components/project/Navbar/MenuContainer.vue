<template>
  <div id="menu-container">
    <div
      id="navbar-user-avatar-container"
      @click="displayMenu = true"
    >
      <div
        v-if="$store.state.account.avatar"
        id="navbar-user-avatar"
        :style="{ backgroundImage: 'url(' + $store.state.account.avatar + ')' }"
      />
    </div>
    <transition name="fade">
      <NavbarPopupMenu
        v-if="displayMenu"
        @hideMenuEvent="hideMenu"
      />
    </transition>
  </div>
</template>

<script>
import NavbarPopupMenu from '@/components/project/Navbar/NavbarPopupMenu'

export default {
  name: 'MenuContainer',
  components: {
    NavbarPopupMenu
  },
  data () {
    return {
      displayMenu: false
    }
  },
  methods: {
    hideMenu () {
      this.displayMenu = false
    }
  }
}
</script>

<style lang="scss" scoped>
#menu-container {
  height: 5rem;
  position: absolute;
  top: 0;
  right: 2rem;

  #navbar-user-avatar-container {
    width: 4rem;
    height: 4rem;
    background-repeat: no-repeat;
    background-size: contain;
    border-radius: 2rem;
    cursor: pointer;
    margin: {
      top: 0.5rem;
      bottom: 0.5rem;
    };

    #navbar-user-avatar {
      width: 4rem;
      height: 4rem;
      background-repeat: no-repeat;
      background-size: contain;
      border-radius: 2rem;
      cursor: pointer;
    }
  }
}
</style>

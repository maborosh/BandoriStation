<template>
  <div id="navbar">
    <NavbarBrand />
    <MenuContainer v-if="$store.state.navbar.displayMenu" />
  </div>
</template>

<script>
import NavbarBrand from '@/components/project/Navbar/NavbarBrand'
import MenuContainer from '@/components/project/Navbar/MenuContainer'

export default {
  name: 'Navbar',
  components: {
    NavbarBrand,
    MenuContainer
  }
}
</script>

<style lang="scss" scoped>
@import "../../../assets/styles/constants";

#navbar {
  position: fixed;
  width: 100%;
  height: 5rem;
  top: 0;
  left: 0;
  background-color: $theme_color;
  z-index: 100;
}
</style>

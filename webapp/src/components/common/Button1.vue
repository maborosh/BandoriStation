<template>
  <div
    v-if="config.type === 'button'"
    ref="button"
    class="button"
    :class="'button-shape-' + shape + ' button-type-' + buttonType"
    tabindex="100"
    @keyup.enter="click"
    @click="click"
  >
    <slot />
  </div>
  <router-link
    v-else
    ref="link"
    :to="config.path"
    tag="div"
    class="button"
    :class="'button-shape-' + shape + ' button-type-' + buttonType"
    tabindex="101"
  >
    <slot />
  </router-link>
</template>

<script>
export default {
  name: 'Button1',
  props: {
    color: {
      type: String,
      default: ''
    },
    shape: {
      type: String,
      default: 'round-rectangle'
    },
    config: {
      type: Object,
      default () {
        return {}
      }
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    buttonType () {
      if (this.disabled) {
        return 'disabled'
      } else {
        return this.color
      }
    }
  },
  methods: {
    focus () {
      if (this.config.type === 'button') {
        this.$refs.button.focus()
      } else {
        this.$refs.link.focus()
      }
    },
    click () {
      if (this.config.type === 'button') {
        this.$emit('click')
      } else {
        this.$refs.link.click()
      }
    }
  }
}
</script>

<style lang="scss" scoped>
@import "../../assets/styles/constants";

.button {
  text-align: center;
  color: white;
  cursor: pointer;
  user-select: none;
  transition: 0.25s;
  box-sizing: border-box;
  outline: 0;
}

.button-type-1 {
  background-color: $button_color_1;
  &:hover {
    background-color: $button_color_1_hover;
  }
}

.button-type-2 {
  background-color: $button_color_2;
  &:hover {
    background-color: $button_color_2_hover;
  }
}

.button-type-3 {
  background-color: $button_color_3;
  &:hover {
    background-color: $button_color_3_hover;
  }
}

.button-type-disabled {
  cursor: default;
  background-color: rgb(200, 200, 200);
}
</style>

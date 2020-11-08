<template>
  <div
    class="input-container"
    @click="focus"
  >
    <label class="input-label">
      <input
        ref="input"
        :type="type"
        class="input"
        :value="value"
        :placeholder="placeholder"
        :disabled="disabled"
        @input="$emit('input', $event.target.value)"
      >
      <font-awesome-icon icon="pen" />
    </label>
  </div>
</template>

<script>
export default {
  name: 'Input1',
  model: {
    prop: 'value',
    event: 'input'
  },
  props: {
    value: {
      type: String,
      default: ''
    },
    type: {
      type: String,
      default: 'text'
    },
    placeholder: {
      type: String,
      default: ''
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    focus () {
      this.$refs.input.focus()
    }
  }
}
</script>

<style lang="scss" scoped>
@import "../../assets/styles/constants";

.input-container {
  border-radius: 0.6rem;
  height: 4rem;
  line-height: 3.8rem;
  padding: 0 1rem;
  transition: 0.25s;
  box-sizing: border-box;
  background-color: rgb(220, 220, 220);
  cursor: text;
}

.input-label {
  text-align: left;
  font-size: 1.8rem;
  color: rgb(170, 170, 170);
  cursor: text;
}

.input {
  height: 2.4rem;
  width: calc(100% - 2.4rem);
  font-size: 1.6rem;
  border: none;
  padding: 0;
  margin-right: 0.6rem;
  -webkit-appearance: none;
  outline: none;
  background-color: transparent;
  color: $font_color;
}
</style>

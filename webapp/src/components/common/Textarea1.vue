<template>
  <div>
    <label class="input-label">
      <textarea
        ref="input"
        class="input"
        :value="value"
        :placeholder="placeholder"
        :style="areaStyle"
        @input="$emit('input', $event.target.value)"
      />
    </label>
  </div>
</template>

<script>
export default {
  name: 'Textarea1',
  model: {
    prop: 'value',
    event: 'input'
  },
  props: {
    value: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: ''
    },
    areaStyle: {
      type: Object,
      default () {
        return {}
      }
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

.input-label {
  display: inline-block;
  width: 100%;
  vertical-align: bottom;
  border-radius: 0.6rem;
  padding: 1rem;
  background-color: rgb(220, 220, 220);
  box-sizing: border-box;
}

.input {
  width: 100%;
  font-size: 1.6rem;
  border: none;
  resize: none;
  -webkit-appearance: none;
  outline: none;
  padding: 0;
  color: $font_color;
  background-color: rgb(220, 220, 220);
  box-sizing: border-box;
  vertical-align: bottom;
}
</style>

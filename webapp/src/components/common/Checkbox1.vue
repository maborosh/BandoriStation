<template>
  <div
    class="checkbox-container"
    @click="change"
  >
    <label class="checkbox-label">{{ text }}</label>
    <label
      class="checkbox"
      :class="{ checked: isChecked }"
    />
  </div>
</template>

<script>
export default {
  name: 'Checkbox1',
  model: {
    prop: 'checked',
    event: 'change'
  },
  props: {
    checked: {
      type: [Boolean, Array],
      default () {
        return true
      }
    },
    value: {
      type: String,
      default: ''
    },
    text: {
      type: String,
      default: ''
    }
  },
  computed: {
    isChecked: function () {
      if (Array.isArray(this.checked)) {
        return this.checked.includes(this.value)
      } else {
        return this.checked
      }
    }
  },
  methods: {
    change () {
      if (Array.isArray(this.checked)) {
        let changedArray = []
        if (this.isChecked) {
          for (const item of this.checked) {
            if (item !== this.value) {
              changedArray.push(item)
            }
          }
        } else {
          changedArray = [...this.checked, this.value]
        }
        this.$emit('change', changedArray)
      } else {
        this.$emit('change', !this.checked)
      }
    }
  }
}
</script>

<style lang="scss" scoped>
@import "../../assets/styles/constants";

.checkbox-container {
  cursor: pointer;
  position: relative;
  display: inline-block;
}

.checkbox-label {
  display: inline-block;
  user-select: none;
  cursor: inherit;
}

.checkbox {
  display: inline-block;
  position: absolute;
  top: 1.6rem;
  right: 1.8rem;
  width: 3rem;
  cursor: inherit;
  transition: 0.3s;
  background-color: rgb(230, 230, 230);
  height: 0.3rem;
  border-radius: 0.2rem;
  &:after {
    content: '';
    display: block;
    position: absolute;
    width: 2.1rem;
    height: 2.1rem;
    transition: 0.3s;
    background-color: rgb(220, 220, 220);
    top: -0.9rem;
    border-radius: 1.1rem;
    left: -1.05rem;
  }
}

.checked {
  background-color: $checkbox_color_1_after;
  &:after {
    background-color: $checkbox_color_1;
    left: calc(100% - 1.05rem);
  }
}
</style>

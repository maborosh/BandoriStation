<template>
  <transition-group
    name="item-list"
    class="pool"
    tag="div"
  >
    <div
      v-for="item in dataList"
      :key="item.id"
      class="item"
    >
      <label class="item-content">{{ item.content }}</label>
      <label
        class="item-close-button"
        @click="removeItem(item)"
      >
        <font-awesome-icon icon="times" />
      </label>
    </div>
  </transition-group>
</template>

<script>
export default {
  name: 'Pool',
  props: {
    title: {
      type: String,
      default: ''
    },
    dataList: {
      type: Array,
      default () {
        return []
      }
    }
  },
  methods: {
    removeItem (item) {
      this.$globalFunctions.notify({
        content: '确认删除' + this.title + '“' + item.content + '”？',
        displayCancel: true,
        callback: () => {
          this.$emit('removeItem', item.id)
        }
      })
    }
  }
}
</script>

<style lang="scss" scoped>
@import "../../assets/styles/constants";

.pool {
  min-height: 5rem;
  width: 100%;
  background-color: rgb(220, 220, 220);
  border-radius: 0.6rem;
  padding: {
    top: 1rem;
    left: 1rem;
  };
  box-sizing: border-box;
}

.item {
  display: inline-block;
  height: 3rem;
  border-radius: 0.6rem;
  background-color: white;
  margin: {
    right: 1rem;
    bottom: 1rem;
  }
  transition: all 0.3s;
}

.item-list-enter, .item-list-leave-to {
  opacity: 0;
}

.item-list-leave-active {
  position: absolute;
}

.item-content {
  display: inline-block;
  height: 3rem;
  line-height: 2.6rem;
  border-top-left-radius: 0.6rem;
  border-bottom-left-radius: 0.6rem;
  border: {
    top: 0.1rem solid #a0a0a0;
    left: 0.1rem solid #a0a0a0;
    bottom: 0.1rem solid #a0a0a0;
  }
  box-sizing: border-box;
  padding: 0 1rem;
  vertical-align: top;
}

.item-close-button {
  display: inline-block;
  font-size: 1.4rem;
  height: 3rem;
  padding: 0 1rem;
  box-sizing: border-box;
  cursor: pointer;
  vertical-align: top;
  line-height: 2.8rem;
  border: 0.1rem solid #a0a0a0;
  border-top-right-radius: 0.6rem;
  border-bottom-right-radius: 0.6rem;
}
</style>

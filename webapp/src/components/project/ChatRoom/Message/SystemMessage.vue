<template>
  <div class="container">
    <div class="message-content">
      {{ message.content }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'SystemMessage',
  props: {
    message: {
      type: Object,
      default () {
        return {}
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.container {
  position: relative;
  margin-top: 1.5rem;
}

.message-content {
  text-align: center;
  color: rgb(150, 150, 150);
  font-size: 1.2rem;
}
</style>

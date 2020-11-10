<template>
  <div class="container">
    <span class="message-content">{{ message.content }}</span>
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
  display: block;
  text-align: center;
  color: rgb(150, 150, 150);
  font-size: 1.2rem;
}
</style>

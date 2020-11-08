<template>
  <div
    class="section-container"
    :class="{ 'lite-section-container': isLite }"
  >
    <div
      class="section-title"
      :class="{ 'lite-section-title': isLite }"
    >
      {{ title }}
    </div>
    <div
      class="section-content"
      :class="{ 'lite-section-content': isLite }"
    >
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  name: 'SectionContainer',
  props: {
    title: {
      type: String,
      default: ''
    },
    isLite: Boolean
  }
}
</script>

<style lang="scss" scoped>
.section-container {
  margin: 7rem auto 2rem;
  min-height: 10rem;
  width: 90rem;
  text-align: left;
  background-color: white;
  border-radius: 0.6rem;
  border: 0.1rem solid rgb(200, 200, 200);
  transition: 0.25s;
  box-sizing: border-box;

  @media screen and (max-width: 930px) {
    margin: 6rem auto 1rem;
    width: calc(100% - 1.2rem);
  }
}

.lite-section-container {
  margin-top: 10rem;
  //padding: {
  //  left: 4rem;
  //  right: 4rem;
  //}
  width: 40rem;

  @media screen and (max-width: 411px) {
    width: calc(100% - 1.2rem);
  }
}

.section-title {
  color: rgb(100, 100, 100);
  font-size: 2.4rem;
  font-weight: bold;
  line-height: 3.6rem;
  padding: 1.6rem 3rem;
  position: relative;
  user-select: none;
  border-bottom: 0.1rem solid rgb(200, 200, 200);
}

.lite-section-title {
  //border: none;
  //padding: {
  //  bottom: 0.5rem;
  //  left: 1rem;
  //}
}

.section-content {
  padding: 2.5rem 3rem;
  font-size: 1.6rem;
  line-height: 2.5rem;
}

.lite-section-content {
  //padding: 0 0 2.5rem 0;
}
</style>

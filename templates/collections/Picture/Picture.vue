<template>
  <div :class="['stream-image', params.size].join(' ')">
    <img :src="domain + params.src" alt="" />
  </div>
</template>

<script>
export default {
  name: "Picture",
  props: {
    params: {
      type: Object,
    },
  },
  data() {
    return {
      domain: process.env.VUE_APP_CONSTRUCTOR_URL,
    };
  },
};
</script>

<style lang="scss" scoped>
.stream-image {
  position: relative;

  img {
    width: 100%;
    height: auto;
    display: block;
  }
}
</style>

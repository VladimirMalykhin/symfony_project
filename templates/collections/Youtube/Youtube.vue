<template>
  <div
    class="youtube__video"
    :style="{
      textAlign: params.textAlign,
      background: params.background,
      color: params.color,
      fontFamily: params.fontFamily,
      fontWeight: params.fontWeight,
    }"
  >
    <iframe :src="`https://www.youtube.com/embed/${params.youtubeID}?enablejsapi=1&rel=0&autoplay=0`" frameborder="0"></iframe>
  </div>
</template>

<script>
export default {
  name: "Youtube",
  props: {
    params: {
      type: Object,
    },
  },
};
</script>

<style lang="scss" scoped>
.youtube__video {
  position: relative;
  padding-bottom: 54%;
  overflow: hidden;

  &::after {
    content: "";
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    position: absolute;
    z-index: 1;
  }

  iframe {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
  }
}

</style>

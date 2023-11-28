<template>
  <div
    class="title"
    :style="{
      textAlign: params.textAlign,
      background: params.background,
      color: params.color,
      fontFamily: params.fontFamily,
      fontWeight: params.fontWeight,
    }"
  >
    {{params.content}}
  </div>
</template>

<script>
export default {
  name: "Title",
  props: {
    params: {
      type: Object,
    },
  },
};
</script>

<style lang="scss">
.title {
  font-size: 40px;
}

.small-pc.tablet.mobile .title {
  font-size: 24px;
}
</style>

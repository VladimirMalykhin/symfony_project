<template>
  <div
    class="stream-description"
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
  name: "Description",
  props: {
    params: {
      type: Object,
    },
  },
};
</script>

<style lang="scss">
.stream-description {
  font-size: 18px;
}
</style>

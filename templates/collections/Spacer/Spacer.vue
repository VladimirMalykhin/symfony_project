<template>
  <div
    :class="['spacer', params.size].join(' ')"
    :style="{
      background: params.background,
    }"
  ></div>
</template>

<script>
export default {
  name: "Spacer",
  props: {
    params: {
      type: Object,
    },
  },
};
</script>

<style lang="scss">
.spacer {
  &.large {
    padding-bottom: 80px;
  }

  &.medium {
    padding-bottom: 40px;
  }

  &.small {
    padding-bottom: 20px;
  }
}
</style>

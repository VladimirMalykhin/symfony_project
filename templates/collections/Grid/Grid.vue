<template>
  <div
    :class="['stream-grid', 'stream-'+params.direction].join(' ')"
    :style="{ background: params.background }"
  >
    <div class="stream-grid-img">
      <img :src="domain + params.src" alt="" />
    </div>
    <div class="stream-grid-content">
      <div class="stream-grid-content-wrap">
        <div
          class="stream-grid-title"
          :style="{
            textAlign: params.titleTextAlign,
            color: params.titleColor,
            fontFamily: params.titleFontFamily,
            fontWeight: params.titleFontWeight,
          }"
        >
          {{ params.titleContent }}
        </div>
        <div
          class="stream-grid-text"
          :style="{
            textAlign: params.descriptionTextAlign,
            color: params.descriptionColor,
            fontFamily: params.descriptionFontFamily,
            fontWeight: params.descriptionFontWeight,
          }"
        >
          {{ params.descriptionContent }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Grid",
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

<style lang="scss">
.stream-grid,
.stream-grid * {
  text-rendering: optimizeLegibility;
  -webkit-font-smoothing: subpixel-antialiased;
}

.stream-grid {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  background: #ffffff;

  &.stream-left {
    .stream-grid-img {
      order: -1;
    }
  }

  &.stream-right {
    .stream-grid-img {
      order: 2;
    }
  }

  .stream-grid-img {
    width: 50%;

    img {
      width: 100%;
      max-width: none;
      display: block;
    }
  }

  .stream-grid-content {
    padding: 0 40px;
    display: flex;
    -webkit-align-items: center;
    align-items: center;
    width: 50%;

    .stream-grid-title {
      font-size: 30px !important;
      margin-bottom: 20px;
      font-weight: 600;
      line-height: 1.02;
    }

    .stream-grid-text {
      font-size: 18px !important;
      line-height: 1.33;
    }
  }
}

.small-pc {
  &.tablet {
    .stream-grid {
      .stream-grid-content {
        padding: 0 40px;

        .stream-grid-title {
          font-size: 21px !important;
          margin-bottom: 8px;
        }

        .stream-grid-text {
          font-size: 14px !important;
        }
      }
    }

    &.mobile {
      .stream-grid {
        flex-direction: column;

        .stream-grid-img,
        .stream-grid-content {
          width: 100%;
        }

        .stream-grid-img {
          margin: auto;
          padding: 0;
          order: -1;
        }

        .stream-grid-content {
          -webkit-order: 2;
          -ms-order: 2;
          order: 2;
          padding: 40px;
        }
      }
    }
  }

  .stream-grid {
    .stream-grid-content .stream-grid-title {
      font-size: 24px !important;
    }
  }
}
</style>

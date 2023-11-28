<template>
  <div
    class="stream-cols-3"
    :style="{ background: params.background }"
  >
    <div
      v-for="(icon, elementIndex) in params.innerElements"
      :class="'stream-col' + ((activeElement === elementIndex && activeComponent === componentIndex) ? ' stream-col--active' : '')"
      :key="elementIndex"
      @click="setActiveElement({componentIndex, elementIndex})"
    >
      <div class="stream-col-inner">
        <img
          :src="domain + icon.props.src"
          alt=""
        />
        <div
          class="stream-col-title"
          :style="{
            textAlign: params.textAlign,
            color: params.color,
            fontFamily: params.titleFontFamily,
            fontWeight: params.titleFontWeight,
          }"
        >
          {{ icon.props.titleContent }}
        </div>
        <div
          class="stream-col-description"
          :style="{
            textAlign: params.textAlign,
            color: params.color,
            fontFamily: params.descriptionFontFamily,
            fontWeight: params.descriptionFontWeight,
          }"
        >
          {{ icon.props.descriptionContent }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
export default {
  name: "Col3",
  props: {
    componentIndex: {
      type: Number,
      default: null,
    },
    params: {
      type: Object,
    },
  },
  data() {
    return {
      domain: process.env.VUE_APP_CONSTRUCTOR_URL,
    };
  },
  computed: {
    ...mapState({
      activeComponent: (state) => state.constructorData.activeComponent,
      activeElement: (state) => state.constructorData.activeElement,
    }),
  },
  methods: {
    ...mapActions("constructorData", ["setActiveElement"]),
  },
};
</script>

<style lang="scss" scoped>
.stream-col--active {
  box-shadow: 0 0 1px 1px #f500ed;
}

.stream-cols-3 {
  padding: 32px 32px 16px;
  text-align: center;
  display: flex;
  align-items: stretch;
  flex-wrap: wrap;
  max-width: 1200px;
  margin: 0 auto;

  .stream-col {
    width: calc((100% - 16px * 2) / 3);
    margin-right: 16px;
    margin-bottom: 16px;

    &:nth-child(3n) {
      margin-right: 0;
    }
  }

  .stream-col-inner {
    height: 100%;
    padding: 16px;
    text-align: center;
    border: 1px solid #dddddd;
    display: flex;
    flex-direction: column;
    justify-content: center;

    img {
      display: block;
      margin: 0 auto 24px;
      max-width: 100%;
    }

    .stream-col-title {
      text-align: center;
      font-size: 24px !important;
      line-height: 1.2;
      padding: 0;
      margin: 0 auto 8px;
      width: 100%;
    }

    .stream-col-description {
      text-align: center;
      font-size: 16px !important;
      line-height: 1.2;
      padding: 0;
      margin: 0 auto;
      width: 100%;
    }
  }
}

.small-pc.tablet.mobile .stream-col {
  width: 100%;
  padding: 8px;
  margin-bottom: 8px;
  margin-right: 0;
  font-size: 14px !important;

  &:nth-child(3n) {
    margin-right: 0;
  }

  .stream-col-title {
    font-size: 18px !important;
  }

  .stream-col-description {
    font-size: 14px !important;
  }
}
</style>

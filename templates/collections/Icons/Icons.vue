<template>
  <div class="stream-icons" :style="{ background: params.background }">
    <div
      v-for="(icon, elementIndex) in params.innerElements"
      :class="'stream-icon' + ((activeElement === elementIndex && activeComponent === componentIndex) ? ' constructor-stream-icon--active' : '')"
      :key="elementIndex"
      @click="setActiveElement({componentIndex, elementIndex})"
    >
      <div class="stream-icon-inner">
        <img :src="domain + icon.props.src" alt="" />
        <div
          class="stream-icon-title"
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
          class="stream-icon-description"
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
import {mapState, mapActions} from 'vuex';
export default {
  name: "Icons",
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
  computed:{
    ...mapState({
      activeComponent: state =>state.constructorData.activeComponent,
      activeElement: state => state.constructorData.activeElement,
    }),
  },
  methods: {
    ...mapActions('constructorData',[
      'setActiveElement',
    ]),
  },
};
</script>

<style lang="scss" scoped>
.constructor-stream-icon--active {
  .stream-icon-inner {
    box-shadow: 0 0 1px 1px #f500ed;
  }
}

.stream-icons {
  padding: 10px 1px;
  text-align: center;
  display: flex;
  align-items: stretch;
  flex-wrap: wrap;
  max-width: 1200px;
  margin: 0 auto;

  .stream-icon {
    width: 25%;
    padding: 0 2px;
  }

  .stream-icon-inner {
    height: 100%;
    padding: 30px 15px 40px;
    text-align: center;
    border: 1px solid #dddddd;
    display: flex;
    flex-direction: column;
    justify-content: center;

    img {
      width: 60%;
      display: block;
      margin: 0 auto 20px;
      max-width: 168px;
    }

    .stream-icon-title {
      text-align: center;
      font-size: 14px !important;
      line-height: 1.2;
      padding: 0;
      margin: 0 auto;
      max-width: 195px;
      width: 100%;
    }

    .stream-icon-description {
      text-align: center;
      font-size: 12px !important;
      line-height: 1.2;
      padding: 0;
      margin: 0 auto;
      max-width: 195px;
      width: 100%;
    }
  }
}

.small-pc.tablet.mobile .stream-icon {
  width: 50%;
  margin-bottom: 4px;
  font-size: 14px !important;

  &:nth-child(3),
  &:nth-child(4) {
    margin-bottom: 0;
  }
}
</style>

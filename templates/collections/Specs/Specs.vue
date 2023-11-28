<template>
  <div class="specs-list"  :style="{ background: params.background }">
    <div
      v-for="(spec, elementIndex) in params.innerElements"
      :key="elementIndex"
      :class="['specs-list-item', ((activeElement === elementIndex && activeComponent === componentIndex) ? ' specs-list-item--active' : ''), spec.props.isMain === 'Yes' ? 'specs-list-item--header' : '']"
      @click="setActiveElement({componentIndex, elementIndex})"
    >
      <div
        v-if="spec.props.titleContent.trim() !== ''"
        class="specs-list-item-key"
        :style="{
          color: params.color,
          fontFamily: params.titleFontFamily,
          fontWeight: params.titleFontWeight,
        }"
      >
        {{ spec.props.titleContent }}
      </div>
      <div
        v-if="spec.props.descriptionContent.trim() !== ''"
        class="specs-list-item-value"
        :style="{
          color: params.color,
          fontFamily: params.descriptionFontFamily,
          fontWeight: params.descriptionFontWeight,
        }"
      >
        {{spec.props.descriptionContent}}
      </div>
    </div>

  </div>
</template>

<script>
import { mapState, mapActions } from "vuex";

export default {
  name: "Specs",
  props: {
    componentIndex: {
      type: Number,
      default: null,
    },
    params: {
      type: Object,
    },
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
.specs-list {
  padding: 8px;
}

.specs-list-item--active {
  box-shadow: 0 0 1px 1px #f500ed;
}

.specs-list-item {
  display: flex;
  align-items: stretch;
  font-size: 20px !important;
  line-height: 1.2;
  padding: 4px 0;

  &--header {
    font-size: 24px !important;
    font-weight: bold;
  }

  div {
    &:last-child {
      margin-right: 0;
    }
  }
}

.specs-list-item-key {
  margin-right: 8px;
  padding: 20px;
  width: calc((100% - 8px) / 2);
  border: 1px solid #dddddd;
  flex: 1 0 auto;
}

.specs-list-item-value {
  padding: 20px;
  width: calc((100% - 8px) / 2);
  border: 1px solid #dddddd;
  flex: 1 0 auto;
}
</style>

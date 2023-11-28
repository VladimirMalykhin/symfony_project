<template>
  <div class="table" :style="{ background: params.background }">
    <div
      v-for="(row, elementIndex) in params.innerElements"
      :key="elementIndex"
      :class="[
        'table__row',
        activeElement === elementIndex && activeComponent === componentIndex
          ? ' table__row--active'
          : '',
      ]"
      @click="setActiveElement({ componentIndex, elementIndex })"
    >
      <div
        v-for="(cell, cellIndex) in addLimitToCell(row.props.rowContent)"
        :key="cellIndex"
        class="table__cell"
        :style="{
          color: params.color,
          fontFamily: params.fontFamily,
          fontWeight: params.fontWeight,
        }"
      >
        <div
          class="table__cell__img"
          v-if="
            row.props.rowImages[cellIndex] &&
            row.props.rowImages[cellIndex] !== ''
          "
        >
          <img :src="domain + row.props.rowImages[cellIndex]" alt="" />
        </div>
        <div
          class="table__cell__text"
          :style="{
            color: params.titleColor,
            fontFamily: params.titleFontFamily,
            fontWeight: params.titleFontWeight,
          }"
          v-html="handleNewLine(cell)"
        ></div>
      </div>
    </div>
    <RemoveItem
      v-if="componentIndex === activeComponent"
      @click.native="removeComponent(componentIndex)"
    />
  </div>
</template>

<script>
import { mapState, mapActions } from "vuex";
import RemoveItem from "../../ui/RemoveItem.vue";

export default {
  name: "Table",
  props: {
    componentIndex: {
      type: Number,
      default: null,
    },
    handleNewLine: {
      type: Function,
    },
    params: {
      type: Object,
    },
  },
  components: {
    RemoveItem,
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
    ...mapActions("constructorData", [
      "setActiveElement",
      "removeComponent",
      "removeElement",
    ]),
    addLimitToCell(cells) {
      const CellsWithLimit = [];
      for (let i = 0; i < this.params.columns; i++) {
        const currentElem = cells[i] ? cells[i] : "";
        CellsWithLimit.push(currentElem);
      }
      return CellsWithLimit;
    },
  },
};
</script>

<style lang="scss" scoped>
.table {
  padding: 8px;
  width: 100%;
}

.table__row--active {
  box-shadow: 0 0 1px 1px #f500ed;
}

.table__row {
  display: flex;
  align-items: stretch;
  font-size: 20px !important;
  line-height: 1.2;
  // padding: 4px 0;
  position: relative;

}

.table__cell {
  // margin-right: 8px;
  padding: 20px;
  width: 100%;
  border: 1px solid #dddddd;
  // flex: 1 0 auto;
  min-width: 250px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;

  & > div {
    margin-bottom: 10px;

    &:last-child {
      margin-bottom: 0;
    }
  }

  &__img {
    img {
      max-width: 100%;
      height: auto;
      display: block;
      margin: 0 auto;
    }
  }
}
</style>

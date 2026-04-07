import { defineStore } from "pinia";
import { markRaw } from "vue";

export const useRightPanelStore = defineStore("rightPanel", {
  state: () => ({
    rightPanelOpen: false,
    rightPanelComponent: null,
    rightPanelProps: {},
  }),
  actions: {
    openRightPanel(component, props = {}) {
      this.rightPanelComponent = component ? markRaw(component) : null;
      this.rightPanelProps = props || {};
      this.rightPanelOpen = true;
    },
    closeRightPanel() {
      this.rightPanelOpen = false;
      this.rightPanelComponent = null;
      this.rightPanelProps = {};
    },
  },
});

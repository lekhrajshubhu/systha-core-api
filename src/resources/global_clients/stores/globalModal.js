import { defineStore } from "pinia";
import { markRaw } from "vue";

export const useGlobalModalStore = defineStore("globalModal", {
  state: () => ({
    isOpen: false,
    component: null,
    props: {},
    title: "",
    width: "70vw",
    disableTransition: false,
    persistent: true,
  }),
  actions: {
    open(component, props = {}, options = {}) {
      this.component = component ? markRaw(component) : null;
      this.props = props || {};
      this.title = options.title || "";
      this.width = normalizeWidth(options.width) || "70vw";
      this.persistent = options.persistent ?? true;
      this.disableTransition = false;
      this.isOpen = true;
    },
    close() {
      if (!this.isOpen) return;
      this.disableTransition = true;
      this.isOpen = false;
      this.component = null;
      this.props = {};
      this.title = "";
      this.width = "70vw";
      this.persistent = true;
    },
  },
});

const WIDTH_MAP = {
  sm: "30vw",
  md: "50vw",
  lg: "70vw",
  xl: "80vw",
  xxl: "90vw",
  full: "100vw",
};

function normalizeWidth(width) {
  if (!width) return null;
  if (typeof width === "string" && WIDTH_MAP[width]) return WIDTH_MAP[width];
  return width;
}

// GlobalModal.js
let modalInstance = null;

export default {
  register(instance) {
    modalInstance = instance;
  },

  open(options) {
    if (modalInstance) {
      modalInstance.open(options);
    } else {
      console.warn("GlobalModal instance not registered");
    }
  },

  close() {
    if (modalInstance) {
      modalInstance.close();
    }
  },
};

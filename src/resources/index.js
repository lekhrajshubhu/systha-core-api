import { createApp } from "vue";
import App from "./shared/App.vue";
import vuetify from "./shared/vuetify";
import router from "./routes";
import $axios from "./shared/axios.config";
import { createPinia } from "pinia";

import ModalTemplate from "@components/ModalTemplate.vue";

const app = createApp(App);

app.component("ModalTemplate", ModalTemplate);
app.config.globalProperties.$axios = $axios;

const pinia = createPinia();
app.use(pinia);   // <---- Pinia store plugin

app.use(vuetify);
app.use(router);

app.mount("#app");


// import Echo from "laravel-echo";
// import Pusher from "pusher-js";

// window.Pusher = Pusher;

// window.Echo = new Echo({
//   broadcaster: "pusher",
//   key: "local",
//   cluster: "mt1",
//   wsHost: "reverb.systha.net", // Important!
//   wsPort: 443,
//   wssPort: 443,
//   forceTLS: true,
//   disableStats: true,
//   enabledTransports: ["ws", "wss"],
// });

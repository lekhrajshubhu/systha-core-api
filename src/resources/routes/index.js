import { createRouter, createWebHistory } from "vue-router";

import { routes as userRoutes, registerGuards as registerUserGuards } from "../vendor_clients/router";
import {
  routes as accountRoutes,
  registerGuards as registerAccountGuards,
} from "../global_clients/router";

const routes = [...userRoutes, ...accountRoutes];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

registerUserGuards(router);
registerAccountGuards(router);

export default router;

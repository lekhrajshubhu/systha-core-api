import { defineStore } from "pinia";
import $axios from "@shared/axios.config";

export const useGlobalStore = defineStore("account", {
  state: () => ({
    profile: null,
    loading: false,
    error: null,
  }),

  getters: {
    hasProfile: (state) => Boolean(state.profile),
    profileName: (state) => {
      const profile = state.profile?.data ?? state.profile;
      if (!profile) return "";
      if (profile.name) return profile.name;
      const parts = [profile.fname, profile.lname].filter(Boolean);
      return parts.join(" ");
    },
    profileEmail: (state) =>
      (state.profile?.data ?? state.profile)?.email ?? "",
    profileAvatar: (state) =>
      (state.profile?.data ?? state.profile)?.vendor?.logo ?? null,
  },

  actions: {
    async fetchProfile() {
      this.loading = true;
      this.error = null;

      try {
        if (localStorage.getItem("token") === null) {
          return;
        }
        const resp = await $axios.get("/profile");
        if (resp?.token) {
          localStorage.setItem("token", resp.token);
        }
        this.profile = resp?.data?.data ?? resp?.data ?? null;
      } catch (err) {
        localStorage.removeItem("token");
        if (err.response?.status === 401 || err.response?.status === 419) {
          await this.logout();
        } else {
          this.error = err;
        }
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        await $axios.post("/logout");
      } catch (_) {
        // ignore network errors on logout
      } finally {
        this.profile = null;
        localStorage.removeItem("token");
      }
    },
  },
});

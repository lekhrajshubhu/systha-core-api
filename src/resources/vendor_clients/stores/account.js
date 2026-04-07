import { defineStore } from "pinia";
import $axios from "@shared/axios.config";

export const useVendorClientStore = defineStore("user", {
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
      if (profile.fname) return profile.fname;
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
        this.profile = resp?.data?.data ?? resp?.data ?? null;

        console.log({resp});
      } catch (err) {
        // ⬇️ If the token is invalid or expired, log out.
        localStorage.removeItem("token");
        if (err.response?.status === 401 || err.response?.status === 419) {
          await this.logout(); // reuse your own action
        } else {
          this.error = err; // network or other server errors
        }
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        await $axios.post("/logout"); // server-side logout (optional)
      } catch (_) {
        // swallow network errors – we're logging out anyway
      } finally {
        this.profile = null;
        localStorage.removeItem("token");
        // window.location.href = "/customers/login";
      }
    },
  },
});

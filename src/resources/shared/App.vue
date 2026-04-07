<template>
  <v-app>
    <router-view />
  </v-app>
</template>

<script>
import { useVendorClientStore } from '@/vendor_clients/stores/account'
import { useGlobalStore } from '@/global_clients/stores/account'

export default {
  name: 'App',
  mounted() {
    const isAccounts = window.location.pathname.startsWith('/global-clients')
    const hasToken = !!localStorage.getItem('token')
    const userStore = isAccounts ? useGlobalStore() : useVendorClientStore()

    if (!hasToken) {
      this.$router.replace({
        name: isAccounts ? 'globalLoginPage' : 'vendorClientLoginPage',
      })
      return
    }

    userStore.fetchProfile()

  },
}
</script>

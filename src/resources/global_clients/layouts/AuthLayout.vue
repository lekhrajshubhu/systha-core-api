<template>
    <v-app>
        <v-main class="login-bg d-flex align-center justify-center">
            <v-container class="d-flex align-center justify-center">
                <v-card elevation="0" class="pa-6 login-card" width="520">
                    <router-view />
                </v-card>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
export default {
    name: 'LoginLayout',
};
</script>

<style scoped>
.login-bg {
    background: linear-gradient(to right, #f0f2f5, #d9e4ec);
    min-height: 100vh;
}

.login-card {
    background-color: #fff;
}
</style>

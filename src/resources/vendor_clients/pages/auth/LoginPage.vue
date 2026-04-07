<template>
    <div>
        <div class="text-center mb-6">
            <v-icon size="56" color="primary">mdi-account-circle-outline</v-icon>
            <div class="font-weight-semibold mt-2">Welcome Back!</div>
            <div class="text-medium-emphasis">Sign in to manage your account</div>
        </div>
        <v-form ref="formRef" @submit.prevent="login" v-model="valid">
            <v-row>
                <v-col cols="12" class="py-1">
                    <v-text-field v-model="form.email" variant="outlined" label="Email" :rules="emailRules"
                        :error-messages="fieldErrors.email" prepend-inner-icon="mdi-email-outline" type="email"
                        required />
                </v-col>
                <v-col cols="12" class="py-1">
                    <v-text-field v-model="form.password" variant="outlined" label="Password" :rules="passwordRules"
                        :error-messages="fieldErrors.password" prepend-inner-icon="mdi-lock-outline" type="password"
                        required />
                </v-col>
                <v-col cols="12">
                    <v-alert v-if="error" type="error" class="mb-4" border="start" color="red-lighten-2" density="compact">
                        {{ error }}
                    </v-alert>
                    <v-btn color="primary" size="x-large" type="submit" block :loading="loading" :disabled="!valid">
                        Login
                    </v-btn>
                    <div class="text-center pt-4">
                         <p style="cursor: pointer;" @click="$router.push({ name: 'vendorClientResetPasswordPage' })">Forget Password ?</p>
                    </div>
                </v-col>
            </v-row>



        </v-form>

    </div>
</template>

<script setup>
import { getCurrentInstance, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const { proxy } = getCurrentInstance();
const formRef = ref(null);

const valid = ref(false);
const loading = ref(false);
const error = ref('');
const fieldErrors = reactive({
    email: [],
    password: [],
});

const form = reactive({
    email: '',
    password: '',
});

const emailRules = [
    (v) => !!v || 'Email is required',
    (v) => /.+@.+\..+/.test(v) || 'E-mail must be valid',
];

const passwordRules = [
    (v) => !!v || 'Password is required',
    (v) => v.length >= 6 || 'Min 6 characters',
];

const login = async () => {
    try {
        error.value = '';
        fieldErrors.email = [];
        fieldErrors.password = [];
        loading.value = true;

        const resp = await proxy.$axios.post('/login', form);

        if (resp.token) {
            localStorage.setItem('token', resp.token);
            // if (resp.vendor_code) {
            //     localStorage.setItem('vendor_code', resp.vendor_code);
            // }
            router.push({ name: 'vendorClientDashboardPage' });
        } else {
            error.value = resp?.message || 'Login failed.';
        }
    } catch (err) {
        const data = err.response?.data;
        if (data?.errors) {
            fieldErrors.email = data.errors.email || [];
            fieldErrors.password = data.errors.password || [];
        }
        error.value = data?.message || 'Something went wrong. Please try again.';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
});
</script>

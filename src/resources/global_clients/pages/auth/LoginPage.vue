<template>
    <div>
        <div class="text-center mb-6">
            <v-icon size="56" color="primary">mdi-account-circle-outline</v-icon>
            <div class="font-weight-semibold mt-2">Welcome Back!</div>
            <div class="text-medium-emphasis">Sign in to manage your account</div>
        </div>
        <v-form ref="form" @submit.prevent="login" v-model="valid">
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
                    <v-btn color="primary" size="x-large" type="submit" block :loading="loading" :disabled="loading">
                        Login
                    </v-btn>
                    <div class="text-center pt-4">
                        <p style="cursor: pointer;" @click="$router.push({ name: 'globalResetPasswordPage' })">Reset Password ?</p>
                    </div>
                </v-col>
            </v-row>



        </v-form>

    </div>
</template>

<script>
export default {
    name: 'LoginPage',
    data() {
        return {
            valid: false,
            loading: false,
            error: '',
            fieldErrors: {
                email: [],
                password: [],
            },
            form: {
                email: '',
                password: '',
            },
            emailRules: [
                v => !!v || 'Email is required',
                v => /.+@.+\..+/.test(v) || 'E-mail must be valid',
            ],
            passwordRules: [
                v => !!v || 'Password is required',
                v => v.length >= 6 || 'Min 6 characters',
            ],
        };
    },
    methods: {
        async login() {
            try {
                this.error = '';
                this.fieldErrors = { email: [], password: [] };
                this.loading = true;
                const resp = await this.$axios.post('/login', this.form)

                if (resp.token) {
                    localStorage.setItem('token', resp.token)
                    if (resp.vendor_code) {
                        localStorage.setItem('vendor_code', resp.vendor_code)
                    }
                    this.$router.push({ name: 'globalDashboardPage' })
                } else {
                    this.error = resp?.message || 'Login failed.'
                }
                this.loading = false;
            } catch (err) {
                this.loading = false;
                const data = err.response?.data;
                if (data?.errors) {
                    this.fieldErrors = {
                        email: data.errors.email || [],
                        password: data.errors.password || [],
                    };
                }
                this.error = data?.message || 'Something went wrong. Please try again.'
            }
        }
    },
};
</script>

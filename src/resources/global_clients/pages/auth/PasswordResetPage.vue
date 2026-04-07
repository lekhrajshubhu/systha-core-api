<template>
    <div>
        <div class="text-center mb-6">
            <v-icon size="56" color="primary">mdi-lock-reset</v-icon>
            <div class="font-weight-semibold mt-2">Reset your password</div>
            <div class="text-medium-emphasis">We’ll email you a reset link</div>
        </div>

        <v-form ref="form" v-model="valid" @submit.prevent="handleSendEmail">
            <v-row>
                <v-col cols="12" class="py-1">
                    <div>
                        <v-text-field variant="outlined" v-model="form.email" label="Email address" :rules="emailRules"
                            prepend-inner-icon="mdi-email-outline" type="email" required />
                    </div>
                </v-col>
                <v-col cols="12">


                    <div>
                        <v-btn color="primary" size="x-large" type="submit" block :loading="loading" :disabled="!valid">
                            submit
                        </v-btn>

                        <div class="text-center pt-4">
                             <p style="cursor: pointer;" @click="$router.push({ name: 'globalLoginPage' })"> <v-icon>mdi-arrow-left</v-icon> Back to Login</p>
                        </div>
                    </div>

                </v-col>
            </v-row>


        </v-form>



    </div>
</template>

<script>
export default {
    name: 'passwordResetPage',
    data() {
        return {
            valid: false,
            loading: false,
            error: '',
            success: '',
            form: {
                email: '',
            },
            emailRules: [
                v => !!v || 'Email is required',
                v => /.+@.+\..+/.test(v) || 'E-mail must be valid',
            ],
        };
    },
    methods: {
        async handleSendEmail() {
            const form = this.$refs.form;
            if (form?.validate) {
                const { valid } = await form.validate();
                if (!valid) return;
            }

            this.error = '';
            this.success = '';
            this.loading = true;
            try {
                this.loading = true;
                await this.$axios.post('/password-reset', this.form);
                this.success = 'If that email exists, a reset link has been sent.';
                this.$router.push({ name: 'globalLoginPage' });
            } catch (err) {
                this.error =
                    err.response?.data?.message || 'Something went wrong. Please try again.'
            } finally {
                this.loading = false;
            }
        }
    },
};
</script>

<template>
    <v-container fluid>
        <v-row>
            <v-col cols="12" md="8" offset-md="2" lg="6" offset-lg="3">
                <div>
                    <div class="my-6">
                        <h4 class="mb-1">Password Settings</h4>
                        <p class="text-medium-emphasis mb-0">Change your password to keep your account secure.</p>
                    </div>
                    <div class="pt-4">
                        <v-form ref="formRef" @submit.prevent="handleUpdate">
                        <v-row>
                            <v-col cols="12" md="12">
                                <v-text-field
                                    v-model="form.current_password"
                                    label="Current Password"
                                    :type="showCurrentPassword ? 'text' : 'password'"
                                    :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'"
                                    @click:append-inner="showCurrentPassword = !showCurrentPassword"
                                    :rules="[required]"
                                    :error-messages="errors.current_password || []"
                                    variant="outlined"
                                    density="comfortable" />
                            </v-col>

                            <v-col cols="12" md="12">
                                <v-text-field
                                    v-model="form.new_password"
                                    label="New Password"
                                    :type="showNewPassword ? 'text' : 'password'"
                                    :append-inner-icon="showNewPassword ? 'mdi-eye-off' : 'mdi-eye'"
                                    @click:append-inner="showNewPassword = !showNewPassword"
                                    :rules="[required]"
                                    :error-messages="errors.new_password || []"
                                    variant="outlined"
                                    density="comfortable" />
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-text-field
                                    v-model="form.new_password_confirmation"
                                    label="Confirm New Password"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    :append-inner-icon="showConfirmPassword ? 'mdi-eye-off' : 'mdi-eye'"
                                    @click:append-inner="showConfirmPassword = !showConfirmPassword"
                                    :rules="[required]"
                                    :error-messages="errors.new_password_confirmation || []"
                                    variant="outlined"
                                    density="comfortable" />
                            </v-col>
                        </v-row>
                        <div class="d-flex justify-center mt-4">
                            <v-btn
                                color="primary"
                                variant="flat"
                                prepend-icon="mdi-content-save"
                                type="submit"
                                :loading="loading"
                                :disabled="loading"
                            >
                                Update
                            </v-btn>
                        </div>
                        </v-form>
                    </div>
                </div>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { nextTick, ref, watch } from 'vue';

const emit = defineEmits(['update-password-info']);
const props = defineProps({
    errors: {
        type: Object,
        default: () => ({}),
    },
    loading: {
        type: Boolean,
        default: false,
    },
    resetKey: {
        type: Number,
        default: 0,
    },
});

const form = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});
const formRef = ref(null);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const required = (v) => !!v || 'This field is required.';

const resetForm = () => {
    form.value = {
        current_password: '',
        new_password: '',
        new_password_confirmation: '',
    };
    showCurrentPassword.value = false;
    showNewPassword.value = false;
    showConfirmPassword.value = false;

    nextTick(() => {
        formRef.value?.resetValidation?.();
    });
};

watch(
    () => props.resetKey,
    () => resetForm()
);

const handleUpdate = async () => {
    const result = await formRef.value?.validate?.();
    const isValid = typeof result === 'boolean' ? result : !!result?.valid;

    if (!isValid) {
        return;
    }

    emit('update-password-info', { ...form.value });
};
</script>

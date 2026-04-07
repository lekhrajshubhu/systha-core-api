<template>
    <v-container fluid>
        <v-row>
            <v-col cols="12" md="8" offset-md="2" lg="6" offset-lg="3">
                <div>
                    <div class="my-6">
                        <h4 class="mb-1">Address Information</h4>
                        <p class="text-medium-emphasis mb-0">Keep your address details accurate for communication and
                            records.</p>
                    </div>
                    <div class="pt-4">
                        <v-form @submit.prevent="handleUpdate">
                        <v-row>
                            <v-col cols="12" md="12">
                                <v-text-field v-model="form.add1" label="Address Line 1" variant="outlined"
                                    density="comfortable" />
                            </v-col>
                            <v-col cols="12" md="12">
                                <v-text-field v-model="form.add2" label="Address Line 2" variant="outlined"
                                    density="comfortable" />
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field v-model="form.city" label="City" variant="outlined"
                                    density="comfortable" />
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field v-model="form.state" label="State" variant="outlined"
                                    density="comfortable" />
                            </v-col>
                            <v-col cols="12" md="4">
                                <v-text-field v-model="form.zip" label="Zip Code" variant="outlined"
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
import { ref, watch } from 'vue';

const emit = defineEmits(['update-address-info']);

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const form = ref({
    id: null,
    add1: '',
    add2: '',
    city: '',
    state: '',
    zip: '',
});

const syncForm = () => {
    const address = props.profile?.address || {};

    form.value = {
        id: props.profile?.id ?? null,
        add1: address.line1 || address.add1 || '',
        add2: address.line2 || address.add2 || '',
        city: address.city || '',
        state: address.state || '',
        zip: address.zip || '',
    };
};

watch(
    () => props.profile,
    syncForm,
    { immediate: true, deep: true }
);

const handleUpdate = () => {
    emit('update-address-info', { ...form.value });
};
</script>

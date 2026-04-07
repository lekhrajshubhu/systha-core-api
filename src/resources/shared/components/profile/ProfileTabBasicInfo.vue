<template>
    <v-container fluid>
        <v-row>
            <v-col cols="12" md="8" offset-md="2" lg="6" offset-lg="3">
                <div>
                    <div class="my-6">
                        <h4 class="mb-1">Basic Information</h4>
                        <p class="text-medium-emphasis mb-0">Update your personal details shown on your profile.</p>
                    </div>
                    <div class="pt-4">
                        <v-form @submit.prevent="handleUpdate">
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-text-field v-model="form.fname" label="First Name" variant="outlined" density="comfortable" />
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field v-model="form.lname" label="Last Name" variant="outlined" density="comfortable" />
                            </v-col>
                             <v-col cols="12" md="12">
                                <v-text-field v-model="form.email" label="Email" variant="outlined" density="comfortable" />
                            </v-col>
                             <v-col cols="12" md="12">
                                <v-text-field v-model="form.phone" label="Phone" variant="outlined" density="comfortable" />
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

const emit = defineEmits(['update-basic-info']);

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
    fname: '',
    lname: '',
    email: '',
    phone: '',
});

const syncForm = () => {
    form.value = {
        id: props.profile?.id ?? null,
        fname: props.profile?.fname ?? '',
        lname: props.profile?.lname ?? '',
        email: props.profile?.email ?? '',
        phone: props.profile?.phone ?? '',
    };
};

watch(
    () => props.profile,
    syncForm,
    { immediate: true, deep: true }
);

const handleUpdate = () => {
    emit('update-basic-info', { ...form.value });
};
</script>

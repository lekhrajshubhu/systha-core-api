<template>
    <v-card class="elevation-0">
        <v-card-text class="pt-4">
            <v-row>
                <v-col cols="12">
                    <v-switch v-model="isDefault" color="primary" inset label="Make Default Card" hide-details />
                </v-col>
                <v-col cols="12">
                    <v-switch v-model="isActive" color="success" inset label="Active Card" hide-details />
                </v-col>
            </v-row>
        </v-card-text>
        <v-card-actions class="justify-center pt-6">

            <v-btn color="primary" variant="outlined" prepend-icon="mdi-content-save" :loading="loading"
                :disabled="loading" @click="handleConfirm">Save</v-btn>
        </v-card-actions>
    </v-card>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useGlobalModalStore } from '@shared/stores/globalModal';

const props = defineProps({
    card: {
        type: Object,
        default: null,
    },
    onConfirm: {
        type: Function,
        default: null,
    },
});

const globalModal = useGlobalModalStore();
const isDefault = ref(false);
const isActive = ref(true);
const loading = ref(false);

watch(
    () => props.card,
    (card) => {
        isDefault.value = !!(card?.is_default || card?.default);
        isActive.value = card?.is_active !== undefined ? !!card.is_active : true;
    },
    { immediate: true }
);


const handleConfirm = async () => {
    loading.value = true;
    try {
        await props.onConfirm?.({
            id: props.card?.id,
            is_default: isDefault.value,
            is_active: isActive.value,
        });
        globalModal.close();
    } catch (error) {
        console.error('Failed to update card', error);
    } finally {
        loading.value = false;
    }
};


</script>
<style scoped>
</style>

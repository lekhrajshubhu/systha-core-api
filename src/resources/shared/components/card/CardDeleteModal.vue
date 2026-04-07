<template>
    <v-card class="elevation-0">
        <v-card-text class="py-4 text-center">
            <p class="mb-0 text-error">
                Are you sure you want to delete this card?
            </p>
            <p class="mt-2 mb-0">
                <strong>{{ cardLabel }}</strong>
            </p>
        </v-card-text>
        <v-card-actions class="justify-center pt-10">
            <v-btn
                color="error"
                variant="outlined"
                prepend-icon="mdi-delete-outline"
                :loading="loading"
                :disabled="loading"
                @click="handleConfirm"
            >
                Confirm
            </v-btn>
        </v-card-actions>
    </v-card>
</template>

<script setup>
import { computed, ref } from 'vue';
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
const loading = ref(false);


const handleConfirm = async () => {
    loading.value = true;
    try {
        await props.onConfirm?.(props.card);
        globalModal.close();
    } catch (error) {
        console.error('Failed to delete card', error);
    } finally {
        loading.value = false;
    }
};

const cardLabel = computed(() => {
    const type = props.card?.card_brand || props.card?.brand || props.card?.type || 'Card';
    const cardType = String(type).replace(/_/g, ' ').toUpperCase();
    const last4 = props.card?.card_last4 || '----';
    return `${cardType} •••••••• ${last4}`;
});
</script>

<template>
    <div class="card-preview mb-4" :style="previewStyle">
        <div class="d-flex justify-space-between align-center mb-8">
            <span class="text-caption text-white">{{ cardBrand(card) }}</span>
            <v-icon color="white" size="18">mdi-contactless-payment</v-icon>
        </div>
        <p class="card-number mb-5">{{ maskedCard(card) }}</p>
        <div class="d-flex justify-space-between text-white">
            <div>
                <p class="label mb-1">Card Holder</p>
                <p class="value mb-0">{{ cardHolder(card) }}</p>
            </div>
            <div class="text-right">
                <p class="label mb-1">Expires</p>
                <p class="value mb-0">{{ cardExpiry(card) }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    card: {
        type: Object,
        required: true,
    },
    cardBrand: {
        type: Function,
        required: true,
    },
    maskedCard: {
        type: Function,
        required: true,
    },
    cardExpiry: {
        type: Function,
        required: true,
    },
    cardHolder: {
        type: Function,
        required: true,
    },
    width: {
        type: String,
        default: '',
    },
});

const previewStyle = computed(() => {
    if (!props.width) return {};
    return { width: props.width };
});
</script>

<style scoped>
.card-preview {
    background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 48%, #14b8a6 100%);
    border-radius: 14px;
    padding: 20px;
}

.card-number {
    color: #fff;
    font-size: 1.1rem;
    letter-spacing: 0.08em;
}

.label {
    color: rgba(255, 255, 255, 0.72);
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.value {
    color: #fff;
    font-weight: 600;
}
</style>

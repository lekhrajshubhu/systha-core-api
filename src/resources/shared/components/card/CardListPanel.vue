<template>
    <v-col cols="12" md="8">
        <v-card class="pa-5 h-100 panel-card" elevation="0">
            <div class="d-flex align-center justify-space-between mb-4 flex-wrap ga-3">
                <h3 class="mb-0">Saved Cards</h3>
                <div class="d-flex align-center ga-2 flex-wrap">
                    <v-text-field :model-value="searchQuery" prepend-inner-icon="mdi-magnify" placeholder="Search cards"
                        variant="outlined" density="compact" hide-details style="min-width: 420px;"
                        @update:model-value="$emit('update:searchQuery', $event)" />
                    <!-- <v-btn color="primary" variant="flat" prepend-icon="mdi-plus">Add Card</v-btn> -->
                </div>
            </div>
            <v-divider></v-divider>

            <div class="cards-table-scroll">
                <v-table>
                    <thead>
                        <tr>
                            <th class="text-left">Brand</th>
                            <th class="text-left">Card Holder</th>
                            <th class="text-left">Number</th>
                            <th class="text-left">Expiry</th>
                            <th class="text-left">Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="card in sortedCards" :key="card.id" :class="{ 'default-item-row': card.is_default }">
                            <td>
                                <div class="d-flex align-center ga-2">
                                    <span>{{ cardBrand(card) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-center ga-2">
                                    <v-avatar size="24" color="primary" variant="tonal">
                                        <v-icon size="14">mdi-account</v-icon>
                                    </v-avatar>
                                    <span>{{ cardHolder(card) }}</span>
                                </div>
                            </td>
                            <td>{{ maskedCard(card) }}</td>
                            <td>{{ cardExpiry(card) }}</td>
                            <td>
                                <v-chip size="x-small" :color="card.is_active ? 'success' : 'error'" variant="tonal">
                                    {{ card.is_active ? 'Active' : 'Inactive' }}
                                </v-chip>
                            </td>
                            <td class="text-right">
                                <span v-if="card.is_default" class="text-caption text-medium-emphasis font-weight-medium">
                                    Default
                                </span>
                                <template v-else>
                                    <v-btn icon variant="tonal" size="x-small" class="mr-2"
                                        color="primary" title="Edit" @click="openEditModal(card)">
                                        <v-icon size="18">mdi-pencil-outline</v-icon>
                                    </v-btn>
                                    <v-btn icon variant="tonal" size="x-small" color="error"
                                        title="Delete" @click="openDeleteModal(card)">
                                        <v-icon size="18">mdi-delete-outline</v-icon>
                                    </v-btn>
                                </template>
                            </td>
                        </tr>
                        <tr v-if="!sortedCards.length && !loadingCards">
                            <td colspan="6" class="text-center py-4 text-medium-emphasis">No saved cards found.</td>
                        </tr>
                        <tr v-if="loadingCards">
                            <td colspan="6" class="text-center py-4 text-medium-emphasis">Loading cards...</td>
                        </tr>
                    </tbody>
                </v-table>
            </div>
        </v-card>
    </v-col>

</template>

<script setup>
import { computed } from 'vue';
import { useGlobalModalStore } from '@shared/stores/globalModal';
import CardDeleteModal from './CardDeleteModal.vue';
import CardUpdateModel from './CardUpdateModel.vue';

const props = defineProps({
    filteredCards: {
        type: Array,
        default: () => [],
    },
    loadingCards: {
        type: Boolean,
        default: false,
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
    copyToClipboard: {
        type: Function,
        required: true,
    },
    searchQuery: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:searchQuery', 'edit-card', 'delete-card']);
const globalModal = useGlobalModalStore();
const sortedCards = computed(() => {
    const list = Array.isArray(props.filteredCards) ? [...props.filteredCards] : [];
    return list.sort((a, b) => Number(!!b?.is_default) - Number(!!a?.is_default));
});

const openEditModal = (card) => {
    globalModal.open(
        CardUpdateModel,
        {
            card,
            onConfirm: (payload) => emit('edit-card', payload || card),
        },
        {
            title: 'Edit Card',
            width: 'sm',
        }
    );
};

const openDeleteModal = (card) => {
    globalModal.open(
        CardDeleteModal,
        {
            card,
            onConfirm: () => emit('delete-card', card),
        },
        {
            title: 'Delete Card',
            width: 'sm',
        }
    );
};

</script>

<style scoped>
.panel-card {
    min-height: calc(100vh - 100px);
    max-height: calc(100vh - 100px);
    overflow: auto;
}

.cards-table-scroll {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.default-item-row td {
    background-color: rgba(var(--v-theme-primary), 0.08);
    color: rgb(var(--v-theme-primary));
}
</style>

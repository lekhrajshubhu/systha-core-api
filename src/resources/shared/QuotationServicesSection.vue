<template>
    <div v-if="services && services.length" class="service_list_wrapper mt-4">
        <h4 class="primary--text">Services</h4>
        <v-divider class="mb-2 mt-2"></v-divider>
        <div class="mb-4">
            <div v-for="(service, index) in services" :key="index" class="service_list">
                <div class="d-flex">
                    <div style="width: 20px;">
                        <p>{{ index + 1 }}.</p>
                    </div>
                    <div>
                        <p class="mb-0 primary--text">{{ service.name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    services: {
        type: Array,
        default: () => [],
    },
});
</script>

<style scoped>
.service_list_wrapper .service_list {
    padding-top: 10px;
    padding-bottom: 10px;
    text-transform: capitalize;
}

.service_list_wrapper .service_list:not(:last-child) {
    border-bottom: 1px dashed #dadada;
}
</style>

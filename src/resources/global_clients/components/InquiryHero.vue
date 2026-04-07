<template>
    <v-card class="elevation-0 pa-4 inquiry-hero">
        <div class="hero-left">
            <p class="hero-title">{{ title }}</p>
            <p class="hero-subtitle">{{ subtitle }}</p>
        </div>
        <div class="hero-right">
            <div class="vendor-card">
                <div class="vendor-info">
                    <p class="vendor-name">{{ vendorName }}</p>
                    <p class="vendor-meta">{{ vendorAddress }}</p>
                </div>
                <div class="vendor-logo">
                    <v-img :src="vendorLogo" alt="Vendor logo" cover />
                </div>
            </div>
        </div>
    </v-card>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, required: true },
    vendorName: { type: String, required: true },
    vendorAddress: { type: String, required: true },
    vendorLogo: { type: String, required: true },
});
</script>

<style scoped>
.inquiry-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.hero-title {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    color: #111827;
}

.hero-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin: 4px 0 0 0;
}

.hero-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.vendor-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 4px;
}

.vendor-logo {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    background: #fff;
}
.vendor-info{
    text-align: right;
}
.vendor-name {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.vendor-meta {
    font-size: 12px;
    color: #6b7280;
    margin: 2px 0 0 0;
}
</style>

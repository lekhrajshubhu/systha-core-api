<template>
    <v-card class="elevation-0 pa-4">
        <div class="section-title-bar">
            <p class="section-title">Customer & Vendor</p>
            <p class="section-subtitle">Primary contacts</p>
        </div>
        <div class="profile-stack">
            <div class="profile-card">
                <div class="profile-avatar">{{ customer.initials }}</div>
                <div>
                    <p class="profile-name">{{ customer.name }}</p>
                    <p class="profile-meta">{{ customer.email }}</p>
                    <p class="profile-meta">{{ customer.phone }}</p>
                </div>
            </div>
            <div class="profile-card vendor-profile">
                <div class="profile-logo">
                    <v-img :src="vendor.logo" alt="Vendor logo" cover />
                </div>
                <div>
                    <p class="profile-name">{{ vendor.name }}</p>
                    <p class="profile-meta">Vendor Code: {{ vendor.code }}</p>
                </div>
            </div>
        </div>
    </v-card>
</template>

<script setup>
defineProps({
    customer: {
        type: Object,
        required: true,
    },
    vendor: {
        type: Object,
        required: true,
    },
});
</script>

<style scoped>
.section-title-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.section-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
}

.profile-stack {
    display: grid;
    gap: 12px;
}

.profile-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    background: #f9fafb;
}

.vendor-profile {
    background: #fff7ed;
}

.profile-avatar {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #111827;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}

.profile-logo {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    background: #ffffff;
}

.profile-name {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.profile-meta {
    font-size: 12px;
    color: #6b7280;
    margin: 2px 0 0 0;
}
</style>

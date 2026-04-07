<template>
    <v-card class="elevation-0">
        <div class="notification-content">
            <div class="d-flex align-center justify-space-between">
                <div>
                    <p class="mb-0 notification-title">Notifications</p>
                    <p class="mb-0 notification-subtitle">Appointment alerts & updates</p>
                </div>
                <v-chip size="small" variant="flat" color="primary" class="notification-chip">3 New</v-chip>
            </div>
            <v-divider class="my-3"></v-divider>
            <div class="notification-list">
                <div class="notification-item d-flex">
                    <div class="notification-icon success">
                        <v-icon size="18">mdi-check-circle</v-icon>
                    </div>
                    <div>
                        <p class="mb-0 notification-item-title">Appointment confirmed</p>
                        <p class="mb-0 notification-item-meta">Today • 10:30 AM</p>
                    </div>
                </div>
                <div class="notification-item d-flex">
                    <div class="notification-icon warning">
                        <v-icon size="18">mdi-clock-alert</v-icon>
                    </div>
                    <div>
                        <p class="mb-0 notification-item-title">Technician arriving soon</p>
                        <p class="mb-0 notification-item-meta">Tomorrow • 9:00 AM</p>
                    </div>
                </div>
                <div class="notification-item d-flex">
                    <div class="notification-icon info">
                        <v-icon size="18">mdi-bell-ring</v-icon>
                    </div>
                    <div>
                        <p class="mb-0 notification-item-title">Payment pending</p>
                        <p class="mb-0 notification-item-meta">Due in 2 days</p>
                    </div>
                </div>
            </div>
        </div>
    </v-card>
</template>

<script setup>
defineProps({
    appointment: {
        type: Object,
        default: null,
    },
});
</script>

<style scoped>
.notification-content {
    padding: 16px;
}

.notification-title {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
}

.notification-subtitle {
    font-size: 12px;
    color: #6b7280;
}

.notification-chip {
    font-weight: 600;
}

.notification-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.notification-item {
    gap: 10px;
}

.notification-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    color: #374151;
}

.notification-icon.success {
    background: #dcfce7;
    color: #166534;
}

.notification-icon.warning {
    background: #fef3c7;
    color: #92400e;
}

.notification-icon.info {
    background: #dbeafe;
    color: #1e40af;
}

.notification-item-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.notification-item-meta {
    font-size: 12px;
    color: #6b7280;
}
</style>

<template>
    <v-card class="elevation-0 pa-4">
        <div class="appointment-wrap">
            <div class="section-header">
                <div class="section-title-group">
                    <p class="section-title">Appointment Summary</p>
                    <p class="section-subtitle">Overview of booking and client info</p>
                </div>
                <div class="section-vendor">
                    <div class="vendor-logo">
                        <v-img :src="vendorLogo" alt="Vendor logo" cover />
                    </div>
                    <div class="vendor-info">
                        <p class="vendor-name">{{ vendorName }}</p>
                        <p class="vendor-address">{{ vendorAddress || '—' }}</p>
                    </div>
                </div>
            </div>
            <v-divider class="my-3"></v-divider>
            <div class="summary-layout no-vendor">
                <div class="summary-left">
                    <div class="summary-item">
                        <p class="summary-label">Appointment #</p>
                        <div class="d-flex align-center appt-no-row">
                            <p class="summary-value">{{ appointment?.appointment_no || '—' }}</p>
                            <v-chip size="small" variant="flat" class="status-chip">{{ statusLabel }}</v-chip>
                        </div>
                    </div>
                    <div class="summary-item">
                        <p class="summary-label">Client</p>
                        <div class="d-flex align-center name-with-avatar">
                            <v-avatar size="24" class="me-2" color="secondary">
                                <span class="avatar-text">{{ clientInitials }}</span>
                            </v-avatar>
                            <p class="summary-value">{{ appointment?.client?.name || '—' }}</p>
                        </div>
                    </div>
                    <div class="summary-item">
                        <p class="summary-label">Date</p>
                        <p class="summary-value">{{ formatDate(appointment?.appointment_date) }}</p>
                    </div>
                    <div class="summary-item">
                       <p class="summary-label">Address</p>
                       <p class="summary-value">{{ appointmentAddress || '—' }}</p>
                   </div>
                   <div class="summary-item">
                       <p class="summary-label">Time</p>
                       <p class="summary-value">{{ formatTime(appointment?.appointment_time) }}</p>
                   </div>
                    <div class="summary-item">
                        <p class="summary-label">Assigned To</p>
                        <div class="d-flex align-center name-with-avatar">
                            <v-avatar size="24" class="me-2" color="primary">
                                <span class="avatar-text">{{ providerInitials }}</span>
                            </v-avatar>
                            <p class="summary-value">{{ appointment?.provider_name || 'n/a' }}</p>
                        </div>
                    </div>
                   
                </div>
                <div class="summary-right"></div>
            </div>

            <div class="section-header mt-8">
                <div>
                    <p class="section-title">Service List</p>
                    <p class="section-subtitle">Items included in the appointment</p>
                </div>
            </div>
            <v-divider class="my-2"></v-divider>
            <div v-if="quotationSections.length === 0" class="text-center text-grey py-4">
                No services found
            </div>
            <div v-else class="section-tables">
                <div v-for="section in quotationSections" :key="section.id" class="section-block mb-2">
                    <div class="section-block-title">{{ section.title }}</div>
                    <div v-if="hasSectionDescription(section)" class="section-block-description">
                        {{ stripHtml(section.description) }}
                    </div>
                    <v-table density="compact" class="service-table">
                        <thead>
                            <tr>
                                <th class="text-left">Service</th>
                                <th class="text-left">Qty</th>
                                <th class="text-left">Unit</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in sectionItems(section)" :key="item.id" :class="item.parent_id ? 'child-row' : ''">
                                <td>
                                    <span :class="item.parent_id ? 'child-title' : 'parent-title'">
                                        {{ item.title }}
                                    </span>
                                </td>
                                <td>{{ item.qty }}</td>
                                <td>{{ item.unit || '-' }}</td>
                                <td class="text-right">{{ formatCurrency(item.unit_price) }}</td>
                                <td class="text-right">{{ formatCurrency(item.line_total) }}</td>
                            </tr>
                        </tbody>
                    </v-table>
                </div>
            </div>
            <payment-section :appointment="appointment" @proceed="handlePaymentProceed" />
        </div>
    </v-card>
</template>

<script setup>
import { computed } from 'vue';
import PaymentSection from './PaymentSection.vue';

const props = defineProps({
    appointment: {
        type: Object,
        default: null,
    },
});
const emit = defineEmits(['payment-success']);

const vendorName = computed(() => props.appointment?.vendor?.name || '—');
const vendorLogo = computed(() => props.appointment?.vendor?.logo || '');

const vendorAddress = computed(() => {
    const address = props.appointment?.vendor?.address || {};
    const parts = [
        address.add1,
        address.add2,
        address.city,
        address.state,
        address.zip,
        address.country,
    ].filter((part) => part && String(part).trim().length);
    return parts.join(', ');
});

const appointmentAddress = computed(() => {
    const address = props.appointment?.address || {};
    const parts = [
        address.add1,
        address.add2,
        address.city,
        address.state,
        address.zip,
        address.country,
    ].filter((part) => part && String(part).trim().length);
    return parts.join(', ');
});

const statusLabel = computed(() => {
    const status = props.appointment?.status || '—';
    return String(status).replace('_', ' ');
});

const clientInitials = computed(() => {
    const name = props.appointment?.client?.name || '';
    return getInitials(name);
});

const providerInitials = computed(() => {
    const name = props.appointment?.provider_name || '';
    return getInitials(name);
});

const quotationSections = computed(() => props.appointment?.quotation?.sections || []);

function sectionItems(section) {
    const items = section?.items || [];
    const parents = items.filter((item) => !item.parent_id);
    const childrenByParent = items.reduce((acc, item) => {
        if (item.parent_id) {
            acc[item.parent_id] = acc[item.parent_id] || [];
            acc[item.parent_id].push(item);
        }
        return acc;
    }, {});

    const ordered = [];
    parents.forEach((parent) => {
        ordered.push(parent);
        const children = childrenByParent[parent.id] || [];
        ordered.push(...children);
    });
    return ordered.length ? ordered : items;
}

function formatDate(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
}

function formatTime(value) {
    if (!value) return '—';
    const timeString = String(value).trim();
    const date = new Date(`1970-01-01T${timeString}`);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
    }).format(date);
}

function formatCurrency(value) {
    const amount = Number(value);
    if (Number.isNaN(amount)) return '—';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    }).format(amount);
}

function stripHtml(value) {
    return String(value || '').replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
}

function hasSectionDescription(section) {
    return stripHtml(section?.description).length > 0;
}

function getInitials(name) {
    const parts = String(name).trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return '—';
    const first = parts[0][0] || '';
    const last = parts.length > 1 ? parts[parts.length - 1][0] : '';
    return (first + last).toUpperCase();
}

function handlePaymentProceed(param){
    emit('payment-success', param);
}
</script>

<style scoped>
.appointment-wrap {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.section-title-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.section-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
}

.status-chip {
    background: #dcfce7;
    color: #166534;
    width: max-content;
    font-weight: 600;
    text-transform: capitalize;
}

.summary-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 12px;
    align-items: start;
}

.summary-layout.no-vendor {
    grid-template-columns: 1fr;
}

.summary-left {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.summary-right {
    display: flex;
    justify-content: flex-end;
}

.summary-item {
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    background: #f9fafb;
}

.vendor-info {
    text-align: right;
}

.vendor-logo {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    background: #fff;
}

.vendor-name {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.vendor-address {
    font-size: 12px;
    color: #6b7280;
    margin: 2px 0 0 0;
    text-align: right;
}

.section-vendor {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-direction: row-reverse;
}

.section-vendor .vendor-info {
    text-align: right;
}

.summary-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
    margin: 0 0 4px 0;
}

.summary-value {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    text-transform: uppercase;
    margin: 0;
}

.appt-no-row {
    gap: 8px;
    flex-wrap: wrap;
}

.name-with-avatar .summary-value {
    margin: 0;
}

.avatar-text {
    font-size: 11px;
    font-weight: 600;
    color: #ffffff;
}

.service-table {
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.service-table thead th {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
}

.service-table tbody td {
    font-size: 14px;
    color: #111827;
}

.section-tables {
    display: grid;
    gap: 12px;
}

.section-block-title {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 4px;
}

.section-block-description {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 8px;
}

.parent-title {
    /* font-weight: 600; */
}

.child-row .child-title {
    padding-left: 16px;
    display: inline-block;
    color: #6b7280;
}

.summary-meta {
    font-size: 12px;
    color: #6b7280;
    margin: 2px 0 0 0;
}
</style>

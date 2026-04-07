<template>
    <v-container fluid>
        <!-- Info Cards -->
        <v-row class="mb-6">
            <v-col cols="12" sm="6" md="4">
                <v-card color="primary" variant="flat" class="text-white">
                    <v-card-title>
                        <v-icon start>mdi-calendar-check</v-icon>
                        Appointments
                    </v-card-title>
                    <v-card-text class="text-h5 font-weight-bold">{{ stats.appointments }}</v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="4">
                <v-card color="green" variant="flat" class="text-white">
                    <v-card-title>
                        <v-icon start>mdi-certificate</v-icon>
                        Active Subscriptions
                    </v-card-title>
                    <v-card-text class="text-h5 font-weight-bold">{{ stats.subscriptions }}</v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="4">
                <v-card color="orange" variant="flat" class="text-white">
                    <v-card-title>
                        <v-icon start>mdi-bell-ring</v-icon>
                        Notifications
                    </v-card-title>
                    <v-card-text class="text-h5 font-weight-bold">{{ stats.notifications }}</v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card elevation="0">
                    <div class="pa-4">

                    </div>
                </v-card>
            </v-col>
        </v-row>
        <!-- Recent Activity -->
        <v-row>
            <v-col cols="12" md="4">
                <v-card title="Recent Appointments" elevation="0">
                    <v-list density="compact">
                        <v-list-item v-for="(a, i) in recentAppointments" :key="i" :subtitle="a.date">
                            <v-list-item-title>{{ a.service }}</v-list-item-title>
                            <template #append>
                                <v-chip size="small" :color="a.statusColor" class="text-white" label>
                                    {{ a.status }}
                                </v-chip>
                            </template>
                        </v-list-item>
                    </v-list>
                </v-card>
            </v-col>
            <v-col cols="12" md="4">
                <v-card title="Subscription Notifications" elevation="0">
                    <v-list density="compact">
                        <v-list-item v-for="(n, i) in recentNotifications" :key="i" :subtitle="n.time">
                            <v-list-item-title>{{ n.title }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-card>
            </v-col>
            <v-col cols="12" md="4">
                <v-card title="Latest Notifications" elevation="0">
                    <v-list density="compact">
                        <v-list-item v-for="(n, i) in recentNotifications" :key="i" :subtitle="n.time">
                            <v-list-item-title>{{ n.title }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
export default {
    data() {
        return {
            user: {
                name: 'John Doe',
            },
            stats: {
                appointments: 3,
                subscriptions: 1,
                notifications: 5,
            },
            recentAppointments: [
                {
                    service: 'Pest Control Service',
                    date: 'Jul 15, 2025',
                    status: 'Confirmed',
                    statusColor: 'green',
                },
                {
                    service: 'Deep Cleaning',
                    date: 'Jul 10, 2025',
                    status: 'Completed',
                    statusColor: 'blue',
                },
                {
                    service: 'Carpet Shampoo',
                    date: 'Jul 5, 2025',
                    status: 'Cancelled',
                    statusColor: 'red',
                },
            ],
            recentNotifications: [
                { title: 'Subscription renewed successfully', time: '2 hours ago' },
                { title: 'Appointment confirmed for July 15', time: '1 day ago' },
                { title: 'Invoice generated for July service', time: '3 days ago' },
            ],
        };
    },
    methods: {
        goTo(page) {
            this.$router.push(`/${page}`);
        },
    },
};
</script>

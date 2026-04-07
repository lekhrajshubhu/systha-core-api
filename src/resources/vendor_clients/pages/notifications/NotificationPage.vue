<template>
  <v-container fluid class="py-6">
    <!-- Header -->
    <v-row class="mb-6 align-center justify-space-between">
      <h2 class="text-h5 font-weight-bold">🔔 Notifications</h2>
      <v-btn color="primary" variant="flat" @click="markAllRead" class="text-white">
        <v-icon start>mdi-check-all</v-icon>
        Mark All as Read
      </v-btn>
    </v-row>

    <!-- Notification List -->
    <v-row justify="center">
      <v-col cols="12" md="8">
        <transition-group name="fade" tag="div">
          <v-card
            v-for="(note, index) in notifications"
            :key="index"
            class="mb-4 notification-card"
            :class="{ 'bg-grey-lighten-4': !note.read }"
            elevation="0"
            hover
          >
            <div
              class="ribbon"
              :style="{ backgroundColor: getRibbonColor(note.type) }"
            ></div>

            <v-list-item class="py-4 pr-4">
              <!-- Icon -->
              <template #prepend>
                <v-avatar size="44" class="elevation-0">
                  <v-icon
                    size="28"
                    :color="getIconColor(note.type)"
                  >
                    {{ getIcon(note.type) }}
                  </v-icon>
                </v-avatar>
              </template>

              <!-- Content -->
              <v-list-item-content>
                <v-list-item-title class="font-weight-bold">
                  {{ note.title }}
                </v-list-item-title>
                <v-list-item-subtitle class="text-body-2 text-grey-darken-1">
                  {{ note.message }}
                </v-list-item-subtitle>
              </v-list-item-content>

              <!-- Timestamp -->
              <template #append>
                <div class="text-caption text-grey-darken-1 mt-2">
                  {{ formatDateTime(note.timestamp) }}
                </div>
              </template>
            </v-list-item>
          </v-card>
        </transition-group>

        <!-- Empty State -->
        <v-alert type="info" v-if="notifications.length === 0" variant="outlined" class="text-center">
          You're all caught up! No notifications right now.
        </v-alert>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      notifications: [
        {
          title: 'Appointment Confirmed',
          message: 'Your appointment on July 20 has been confirmed.',
          type: 'success',
          timestamp: '2025-07-16T10:30:00',
          read: false,
        },
        {
          title: 'Subscription Expired',
          message: 'Your monthly subscription expired yesterday.',
          type: 'warning',
          timestamp: '2025-07-15T18:00:00',
          read: true,
        },
        {
          title: 'Payment Failed',
          message: 'Your payment was declined. Please update your billing info.',
          type: 'error',
          timestamp: '2025-07-14T14:15:00',
          read: false,
        },
      ],
    };
  },
  methods: {
    formatDateTime(dateStr) {
      const date = new Date(dateStr);
      return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
      });
    },
    getIcon(type) {
      switch (type) {
        case 'success':
          return 'mdi-check-circle-outline';
        case 'warning':
          return 'mdi-alert-circle-outline';
        case 'error':
          return 'mdi-close-circle-outline';
        default:
          return 'mdi-bell-outline';
      }
    },
    getIconColor(type) {
      return {
        success: 'green-darken-2',
        warning: 'amber-darken-2',
        error: 'red-darken-2',
      }[type] || 'primary';
    },
    getRibbonColor(type) {
      return {
        success: '#4CAF50',
        warning: '#FB8C00',
        error: '#E53935',
      }[type] || '#1976D2';
    },
    markAllRead() {
      this.notifications = this.notifications.map(n => ({ ...n, read: true }));
    },
  },
};
</script>

<style scoped>
.notification-card {
  position: relative;
  overflow: hidden;
  transition: box-shadow 0.3s;
  border-radius: 12px;
}

.notification-card:hover {
  box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
}

.ribbon {
  position: absolute;
  top: 0;
  left: 0;
  width: 6px;
  height: 100%;
  border-top-left-radius: 12px;
  border-bottom-left-radius: 12px;
}
.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>

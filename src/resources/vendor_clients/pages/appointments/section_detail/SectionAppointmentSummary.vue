<template>
    <div>
        <div class="">
            <div class="d-flex align-center justify-space-between">
                <h5 class="text-uppercase mb-0 primary--text">{{  appointment.appointment_no }}</h5>
                <v-chip small color="success" class="text-capitalize">{{  appointment.status }}</v-chip>
            </div>
            <v-divider class="mt-4 mb-3"></v-divider>
            <div class="d-flex">
                <div>
                    <v-icon color="primary">mdi-calendar</v-icon>
                </div>
                <div class="pl-4">
                    <p class="mb-0">{{ formatDate(appointment.start_date) }}</p>
                    <p class="mb-0">{{ formatTimeString(appointment.start_time) }} - {{ formatTimeString(appointment.end_time) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default{
    props:{
        appointment:{}
    },
    data() {
        return {
            // 
        }
    }
}
</script>
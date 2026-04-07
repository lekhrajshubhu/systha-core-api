<template>
    <div>
        <div class="">
            <div class="mb-2">
                <h4>Appointment For</h4>
            </div>
            <div class="custom-bs mb-6">
                <div class="py-4">
                    <div class="d-flex">
                        <div class="pa-4">
                            <v-avatar size="60">
                                <v-img :src="(appointment && appointment.client && appointment.client.contact && appointment.client.contact.avatar)"></v-img>
                            </v-avatar>
                        </div>
                        <div class="pl-2">
                            <div >
                                <h3 class="mb-0">{{ appointment && appointment.client && appointment.client.fullName }}
                                </h3>
                                <div class="">
                                    <div>
                                        <p class="mb-0 f9">{{ appointment && appointment.client && appointment.client.email}}</p>
                                        <p class="f9">{{ appointment && appointment.client && appointment.client.phone_no}}</p>
                                    </div>
                                    <p class="mb-0 f9">{{ appointment && appointment.client && appointment.client.address.add1 }} {{ appointment && appointment.client && appointment.client.address.city }} </p>
                                    <p class="mb-0 f9">{{ appointment && appointment.client && appointment.client.address.state }} {{ appointment && appointment.client && appointment.client.address.zip }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                  
                </div>

                <div>
                    <!-- <div
                        v-if="(appointment && appointment.providers && appointment.providers.length) || (appointment && appointment.service_providers && appointment.service_providers.length)">
                        <div>
                            <div v-if="appointment && appointment.check_in">
                                <h4 class="primary--text">Check In</h4>
                                <p class="f9">Time: {{ formatTimeString(appointment.check_in_at) }}</p>
                            </div>
                            <div v-else-if="appointment.check_in && (appointment.appointment.check_out == 0)">
                                <v-btn rounded large color="primary" @click="handleInService()">In Service</v-btn>
                            </div>
                            <div v-else-if="appointment && appointment.check_out">
                                <h4 class="primary--text">Check Out</h4>
                                <p class="f9">{{ formatTimeString(appointment.check_out_at) }}</p>
                            </div>
                            <div v-else>
                                <v-btn rounded large color="primary" @click="handleCheckIn()">Check In</v-btn>
                            </div>
                        </div>
                    </div> -->
                    <DialogConfirm @handleConfirm="handleInOutAction" @close="handleClose" :message="message"
                        :dialogConfirm="modal_action" />
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { mapGetters } from 'vuex';
export default {
    data() {
        return {
            modal_action: false,
            message: 'Check In ?',
            action: '',
        }
    },
    methods: {
        handleInOutAction() {
            this.loaderShow();
            this.$axios.post("/appointment/" + `${this.action == 'in' ? 'checkin' : 'checkout'}`, {
                "appointment_id": this.appointment.id
            })
                .then((resp) => {
                    this.loaderHide();
                    this.handleClose();
                    this.$emit('refresh');
                    this.messageSuccess(resp.message);
                })
                .catch((error) => {
                    this.loaderHide();
                    console.log(error);
                })
        },
        handleClose() {
            this.modal_action = false;
            this.$emit('refresh');
        },

        handleInService() {
            this.action = "in_service";
            this.message = "Start Service ?";
            this.modal_action = true;
        },
        handleCheckIn() {
            this.action = "in";
            this.message = "Check In ?";
            this.modal_action = true;
        },

        handleCheckOut() {
            this.action = "out";
            this.message = "Check Out ?";
            this.modal_action = true;
        }
    },
    computed: {
        ...mapGetters({
            appointment: 'appointments/getCurrentAppointment'
        })
    },
    components: {
        DialogConfirm: () => import('@components/layout/DialogConfirm')
    }
}
</script>
<template>
    <div>
        <div class="">
            <div class="mb-2">
                <h2>Appointment For</h2>
            </div>
            {{ appointment }}
            <!-- <v-divider class="mb-3 mt-3"></v-divider> -->
            <!-- <div class="custom-bs mb-6" v-if="appointment">


                <div class="py-4">
                    <div class="d-flex pa-2">
                        <div class="">
                            <v-avatar size="60">
                                <v-img :src="appointment && appointment.client && appointment.client.avatar"></v-img>
                            </v-avatar>
                        </div>
                        <div>
                            <div class="pl-5">
                                <h3 class="mb-0">{{ appointment && appointment.client && appointment.client.fullName }}
                                </h3>
                                <div class="">
                                    <div>
                                        <p class="mb-0 f9">{{ appointment && appointment.client &&
                                            appointment.client.email }}</p>
                                        <p class="f9">{{ appointment && appointment.client &&
                                            appointment.client.phone_no }}</p>
                                    </div>
                                    <div class="mt-2" v-if="clientAddressLine1 || clientAddressLine2">
                                        <p>{{ clientAddressLine1 }}</p>
                                        <p>{{ clientAddressLine2 }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div> -->
        </div>
    </div>
</template>
<script>

// import { mapGetters } from 'vuex';
export default {
    props:{
        appointment:{
            required:true,
            type:Object,
        }
    },
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
        // ...mapGetters({
        //     appointment: 'appointments/getCurrentAppointment'
        // }),
        clientAddress() {
            return this.appointment && this.appointment.client
                ? this.appointment.client.address
                : null;
        },
        clientAddressLine1() {
            if (!this.clientAddress) return '';
            return [this.clientAddress.add1, this.clientAddress.city].filter(Boolean).join(' ');
        },
        clientAddressLine2() {
            if (!this.clientAddress) return '';
            return [this.clientAddress.state, this.clientAddress.zip].filter(Boolean).join(' ');
        }
    },
    components: {
        // DialogConfirm: () => import('@components/layout/DialogConfirm')
    }
}
</script>
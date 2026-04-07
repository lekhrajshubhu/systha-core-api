<template>
    <div class="">
        <div class="mb-2 d-flex align-center justify-space-between">
            <h2>Assigned Professionals </h2>
            <!-- <v-btn rounded large color="primary" @click="handleUpdate()"><v-icon>mdi-plus</v-icon> Add Provider</v-btn> -->
        </div>
        <!-- {{ appointment }} -->
        <!-- <v-divider class="mb-3 mt-3"></v-divider> -->
        <div class="pa-6 custom-bs pa-4">
            <!-- {{ appointment }} -->
            <div v-if="appointment && appointment.service_providers && appointment.service_providers.length">
                <div class="" v-for="(provider, index) in appointment.service_providers" :key="index">
                    <div class="mb-4">
                        <v-row>
                            <v-col cols="12" md="6">
                                <div class="d-flex">
                                    <div class="pt-2">
                                        <v-avatar size="60">
                                            <v-img :src="(provider && provider.provider && provider.provider.avatar)"></v-img>
                                        </v-avatar>
                                    </div>
                                    <div class="pl-4">
                                        <h4 class="mb-2">{{ provider.provider.fullName }}</h4>
                                        <p class="mb-0 f9">{{ formatPhoneNumber(provider.provider.phone_no) }}</p>
                                        <p class="mb-0 f9">{{ provider.provider.email }}</p>
                                    </div>
                                </div>
                            </v-col>
                            <v-col cols="12" md="6">
                                <div class="d-flex">
                                    <v-icon large color="primary">mdi-calendar-check-outline</v-icon>
                                    <div class="pl-2">
                                        <p class="mb-0 primary--text">{{ formatDateToDay(appointment.start_date) }}</p>
                                        <p class="mb-0 f9">{{ formatTimeString(appointment.start_time) }} - {{ formatTimeString(appointment.end_time) }}</p>
                                    </div>
                                </div>
                            </v-col>
                        </v-row>
                    </div>
                    <!-- {{ provider }} -->
                </div>
                <!-- <ModalProvider :dialogChangeProvider="modal_provider" :providers="providers" @close="handleClose()"/> -->
            </div>
            <!-- <div v-else-if="appointment && appointment.service_providers" class="text-center pb-4">
                <v-row>
                    <v-col cols="12" md="6" v-for="(provider, index) in appointment.service_providers" :key="index">
                        <div class="text-center">
                            <div class="pt-2">
                                <v-avatar size="30">
                                    <v-img :src="base_url + '/avatar/' + provider.provider"></v-img>
                                </v-avatar>
                            </div>
                            <div class="pl-2">
                                <p class="mb-1" style="font-weight: 600;">{{ provider.provider.fname }} {{ provider.provider.lname }}</p>
                                <p class="mb-0 f9">{{ formatDateToDay(provider.timeslot.date) }}</p>
                                <p class="mb-0 f9">{{ formatTimeString(provider.timeslot.start_time) }} - {{ formatTimeString(provider.timeslot.end_time) }}</p>
                            </div>
                        </div>
                    </v-col>

                </v-row>
            </div> -->
            <div v-else class="text-center pb-4">
                <p>Professional Not Assigned</p>
                <ModalProvider :dialogChangeProvider="modal_provider" :appointment="appointment" @close="handleClose()"/>
            </div>
        </div>
    </div>
</template>
<script>
// import { base_url } from '@/core/services/config'
// import { mapGetters } from 'vuex';
export default {
    // props: {
    //     appointment: {},
    //     editable: {}
    // },
    data() {
        return {
            // base_url,
            modal_provider: false,
            editable:false,
            providers:[],
        }
    },
    methods: {
        
        handleUpdate() {
            // this.modal_provider = true;
            if(this.appointment && this.appointment.services.length){
                console.log("test");
                let service_ids = this.appointment.services.map((item) => item.id);
                this.$axios.post("admin/provider-available", {
                    vendor_id: this.currentUser.vendor.id,
                    service_ids: service_ids
                })
                .then((resp) => {
                    this.providers = resp.data;
                    this.modal_provider = true;
                    // this.$emit("continue", {
                    //     step: 2,
                    //     appointment: resp.data
                    // });
                })
                .catch((error) => {
                    this.loading = false;
                    this.message = "Error!!"
                    console.log(error);
                })
            }else{
                this.messageError('Service Not Selected');
            }
        },
        handleClose() {
            this.modal_provider = false;
        }
    },
    computed:{
        // ...mapGetters({
        //     appointment:'appointments/getCurrentAppointment',
        //     currentUser:'auth/getCurrentUser'
        // })
    },
    components: {
        // ModalProvider:()=>import('@/views/app/service/appointment/modal/ModalChangeProvider')
    }
}
</script>
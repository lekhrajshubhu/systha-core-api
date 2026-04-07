<template>

        <div>
            <div class="">
               <h3 class="mb-0">Would you like to add a tip?</h3>
                <div class="mt-6">
                    <v-row>
                        <v-col cols="4" class="pb-0" v-for="(tip,index) in [5,10,12,15,'customize']" :key="index">
                            <div class="mb-4">
                                <v-btn large rounded block :color="tip_index==index?'warning':''" @click="updateTipAmount(tip,index)" elevation="0">
                                    {{tip}} {{tip == 'customize'?'':'%'}}
                                </v-btn>
                            </div>
                        </v-col>
                        <v-col cols="12">
                            <div class="" :class="!customize?'':''">
                                <div class="d-flex align-center pl-2" v-if="customize">
                                    <v-text-field v-mask="'##'" x-small label="Tip %" v-model="tipAmount"></v-text-field>
                                    <v-btn color="success" outlined class="ml-4" rounded @click="addTip()">Add tip</v-btn>
                                </div>
                            </div> 
                        </v-col>
                    </v-row>
                </div>
            </div>
        </div>

</template>
<script>
import { ApiService } from '@/core/services/api.service'
import { mapActions, mapGetters } from 'vuex';
export default{
    data() {
        return {
            customize:false,
            tip_index:null,
            tipAmount:0,
            appt_id:null,
        }
    },
    computed:{
        ...mapGetters({
            "cart_amount" : "cart/cartAmount",
            "currentUser" : "auth/user",
            cart: "cart/getCart",
        })
    },
    mounted(){
        this.appt_id = localStorage.getItem('appt_id') ??null;
        if(this.appt_id){
            this.fetchCart({appointment_id: this.appt_id});
        }
    },
    methods:{
        ...mapActions({
            "fetchCart":"cart/fetchPosAppointmentCarts",
        }),
        addTip(){
            if(this.tipAmount){
                this.updateTipAmount(this.tipAmount);
            }
        },
        
        updateTipAmount(tip,index){
            this.tip_index = index;
            if(tip=="customize") {
                this.customize = true;
            }else{
                this.customize = false;
                let tips_percentage = parseFloat(tip);
                ApiService.post("/pos-appoinment-tips",{
                    "appointment_id": parseInt(this.appointment_id),
                    "tips": parseFloat(tips_percentage),
                })
                .then((resp) =>{
                    this.messageSuccess(resp.message);
                    this.$emit('refresh');
                })
                .catch((error) =>{
                    this.messageError("Failed to update Tips",error.response.data.message);
                })
            }
        },
    }
}
</script>
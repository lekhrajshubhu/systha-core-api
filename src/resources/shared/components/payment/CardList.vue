<template>
    <div>


        <div class="mb-3">
            <v-card elevation="0" class="pa-4 position-relative">
                <div v-if="selectedCard" class="selected-card-preview">
                    <div class="mb-4">
                        <v-row>
                            <v-col cols="3" class="d-flex align-center justify-center px-0">
                                <div>
                                    <v-img :src="getCardLogo(selectedCard.card_brand)" width="46" contain />
                                </div>
                            </v-col>
                            <v-col cols="9">
                                <div class="text-center d-flex justify-space-between">
                                    <p class="mb-1">
                                        {{ brandDisplayName(selectedCard.card_brand) }}
                                    </p>
                                    <p class="mb-1">
                                        •••• {{ selectedCard.card_last4 }}
                                    </p>
                                </div>
                                <div class="d-flex justify-space-between">
                                    <div class="text-capitalize">{{ selectedCard.card_name }}</div>

                                    <div>
                                        {{ selectedCard.exp_month }}/{{ selectedCard.exp_year.toString().slice(-2)
                                        }}
                                    </div>
                                </div>


                            </v-col>
                        </v-row>
                    </div>
                    <div class="text-center pt-2">
                        <v-btn outlined block large color="primary"
                            @click="changeCard()"><v-icon>mdi-credit-card-sync</v-icon> change card</v-btn>
                    </div>
                </div>
                <div class="mt-4">
                    <v-btn block color="primary" outlined large @click="openCardForm()">
                        <v-icon>mdi-credit-card-plus</v-icon> Add new
                        card</v-btn>
                </div>
            </v-card>

        </div>


        <ModalTemplate ref="globalModal" @close="handleClose" />
    </div>
</template>

<script>
import visaLogo from './icons/visa.png'
import mastercardLogo from './icons/mastercard.png'
import amexLogo from './icons/amex.png'
import discoverLogo from './icons/discover.png'
import dinersLogo from './icons/diners.png'
import jcbLogo from './icons/jcb.png'
import unionpayLogo from './icons/unionpay.png'
import defaultLogo from './icons/default.png'

import ModalAddCard from './ModalAddCard.vue'
import CardChange from './CardChange.vue'


export default {
    props: {
        publishableKey: { type: String, required: true },
    },

    data() {
        return {
            cards: [],
            selectedCard: null,
        }
    },

    computed: {
        // ...mapState('users', ['profile']),
    },

    methods: {
        getCardLogo(brand) {
            const logos = {
                visa: visaLogo,
                mastercard: mastercardLogo,
                amex: amexLogo,
                discover: discoverLogo,
                diners: dinersLogo,
                jcb: jcbLogo,
                unionpay: unionpayLogo,
            }
            return logos[brand] || defaultLogo
        },

        brandDisplayName(brand) {
            const map = {
                visa: 'Visa',
                mastercard: 'MasterCard',
                'american-express': 'American Express',
                discover: 'Discover',
                jcb: 'JCB',
                unionpay: 'UnionPay',
                diners: 'Diners Club',
                unknown: 'Unknown',
            }
            return map[brand?.toLowerCase()] || 'Card'
        },

        async fetchCards() {
            try {

                const resp = await this.$axios.get('/payment-methods')
                this.cards = resp?.data?.payment_methods || []

                this.selectedCard = resp?.data?.default_payment_method;
                this.$emit('selectedCard', this.selectedCard);
            } catch (err) {
                this.cards = []
                this.selectedCard = null
                console.error('Error fetching cards:', err)
            }
        },

        openCardForm() {
            if (this.$refs.globalModal) {
                this.$refs.globalModal.open({
                    title: 'Add New Card',
                    component: ModalAddCard,
                    size: 'md',
                    props: {
                        publishableKey: this.profile?.vendor?.stripe_pub_key || '',
                        // appointment: this.appointment,
                    },
                })
            }
        },
        changeCard() {
            if (this.$refs.globalModal) {
                this.$refs.globalModal.open({
                    title: 'Change Card',
                    component: CardChange,
                    size: 'md',
                    props: {
                        publishableKey: this.profile?.vendor?.stripe_pub_key || '',
                        card_list: this.cards,
                        // appointment: this.appointment,
                    },
                })
            }
        },

        async handleClose() {
            console.log("close");
            await this.fetchCards()
        },
    },

    watch: {
        selectedCard(val) {
            if (val === 'new-card') {
                this.openCardForm()
            } else {
                this.$emit('selectedCard', val)
            }
        },
    },

    mounted() {
        this.fetchCards()
    },
}
</script>

<style scoped>
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.font-mono {
    font-family: monospace;
}

#card-element {
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 12px;
    box-sizing: border-box;
    background-color: white;
    margin: 0 auto;
}

.text-error {
    color: #fa755a;
    margin-top: 8px;
    font-weight: 600;
}

.selected-card-preview {
    background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 50%, #ecfeff 100%);
    border: 1px solid #bfdbfe;
    border-radius: 14px;
    padding: 14px;
}
</style>

<template>
    <v-card>
        <!-- <v-card-title class="text-center">{{ title }}</v-card-title> -->

        <v-card-text class="text-center mt-4">
            <h4>Proceed to Confirm ?</h4>
        </v-card-text>

        <v-card-actions>
            <v-btn text color="error" @click="$emit('onClose')">No</v-btn>
            <v-spacer />
            <v-btn text color="primary" :loading="loading" @click="handleConfirm">Yes</v-btn>
        </v-card-actions>
    </v-card>
</template>

<script>
export default {
    name: 'ConfirmDialog',
    props: {
        title: { type: String, default: 'Confirmation' },
        id: { type: [String, Number], required: true },
    },
    data() {
        return {

            loading: false,
        }
    },
    methods: {
        handleConfirm() {
            this.loading = true;
            this.$axios
                .post(`/quotations/${this.id}/confirm`)
                .then((resp) => {
                    this.loading = false;
                    console.log({ resp });
                    // Do something with resp.data if needed
                    //   this.$emit('confirmed', resp.data); // emit confirmed event with response
                    this.$emit('onClose'); // tell parent to close modal
                })
                .catch((error) => {
                    this.loading = false;
                    console.error(error);
                });
        },
    },
};
</script>

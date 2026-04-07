<template>
    <v-dialog v-model="show" :width="dialogWidth" persistent scrollable>
        <component v-if="currentComponent" :is="currentComponent" v-bind="componentProps" @onClose="close" />
    </v-dialog>
</template>

<script>
import { markRaw } from 'vue';

export default {
    name: 'ModalTemplate',
    data() {
        return {
            show: false,
            title: '',
            currentComponent: null,
            componentProps: {},
            dialogWidth: 600,
            sizeMap: {
                sm: 300,
                md: 600,
                lg: 900,
                xl: 1200,
            },
        };
    },
    methods: {
        open({ title = '', component = null, size = 'md', props = {} }) {
            this.title = title;
            this.currentComponent = component ? markRaw(component) : null;
            this.componentProps = props;
            this.dialogWidth = this.sizeMap[size] || this.sizeMap.md;
            this.show = true;
        },
        close() {
            this.show = false;
            this.$emit('close');
        },
    },
};
</script>

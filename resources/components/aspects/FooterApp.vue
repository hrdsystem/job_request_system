<template>
    <v-footer dense inset color="white" app>
        <span class="font-weight-bold" style="font-size: 12px;">Showing {{currentData}} of {{currentRecords}}</span>
    </v-footer>
</template>

<script>
    export default {
        name: 'footerapp',
        props:{
            footerapp:{
                type: Boolean,
            },
            currentData: 0,
            currentRecords: 0,
        }
    }
</script>

<style scoped>
    .v-footer{
        z-index: 0 !important;
        padding: 0 !important;
    }
</style>
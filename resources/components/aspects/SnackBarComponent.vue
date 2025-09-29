<template>
    <div class="text-center">
        <v-snackbar
            v-model="snackbar.show"
            :color="snackbar.color"
            :timeout="snackBarTimeout"
            location="top right"
        >
            {{ snackbar.text }}
        </v-snackbar>
    </div>
</template>

<script>
import { mapState} from 'pinia'
import { useSampleStore } from '../../js/store';
    export default {
        props:{
            snackbar:{
                type: Object
            }
        },
        computed: {
            ...mapState(useSampleStore, [
                'snackBarTimeout'
            ])
        },
        methods: {
            refresh(){
                this.snackbar.show = false
            }
        },
    }
</script>

<style lang="scss" scoped>

</style>
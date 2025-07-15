<template>
    <v-container>
            THIS IS EMAIL RECIPIENT PAGE
    </v-container>
</template>

<script setup>
import ToolBarComponent from '@aspect/ToolBarComponent.vue'
import SnackBarComponent from '@aspect/SnackBarComponent.vue'
import FloatButtonComponent from '@aspect/FloatButtonComponent.vue'
</script>

<script>
import {mapState,mapActions, mapWritableState} from 'pinia'
import {useSampleStore} from '../../../js/store'
export default {
    data(){
        return{

        }
    },

    computed:{
        ...mapState(useSampleStore,[

        ])
    },

    method:{
        ...mapActions(useSampleStore,[

        ])
    }
    
}
</script>

<style scoped>
    
</style>
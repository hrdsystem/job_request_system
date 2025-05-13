<template>
    <v-container class="container-main pt-0">
    <tool-bar-component :toolbarData="toolbarData"></tool-bar-component>
        <v-table fixed-header :height="tableHeight" class="mainTable">
            <thead>
                <tr>
                    <th v-show="editMode" class="text-left" style="width:20px">
                        <v-checkbox color="white"></v-checkbox>
                    </th>
                    <th v-show="editMode" class="text-left" style="width:20px">Edit</th>
                    <th v-show="editMode" class="text-left Sortable" style="width:20px">
                        No.
                    </th>
                    <th class="text-left Sortable" @click="sortCol($event.target, 'job_requireds.required_name')">
                        Required Name
                    </th>
                    <th class="text-left Sortable" @click="sortCol($event.target, 'job_requireds.filling_mark')">
                        Filling Mark
                    </th>
                    <th class="text-left Sortable" @click="sortCol($event.target, 'job_requireds.header_name')">
                        Header Name
                    </th>
                </tr>
            </thead>
        </v-table>
    </v-container>
</template>

<script setup>
import ToolBarComponent from '@aspect/ToolBarComponent.vue'
</script>

<script>
import {mapState,mapActions} from 'pinia'
import {useSampleStore} from '../../../js/store.js'
export default {
    data(){
        return{
            toolbarData:{
                title: "JOB REQUIRED",
                sub_title: "ALL",
                masters: {
                    show: false,
                    url: '/job_request_settings/job_required'
                },
                back: {
                    show: true,
                    url: '/job_request'
                },
                view: false,
                filter: true,
                info: false,
                key: false
            },
        }
    },

    computed:{
        ...mapState(useSampleStore,[
            'editMode',
            'filterMode',
            'viewMode',
            'tableHeight',
        ])
    }
}
</script>

<style scoped>
    
</style>
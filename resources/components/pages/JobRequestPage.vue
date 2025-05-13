<template>
    <v-container class="container-main pt-0">
    <tool-bar-component :toolbarData="toolbarData"></tool-bar-component>
        <v-table fixed-header :height="tableHeight" class="mainTable">
            <thead>
                <tr>
                    <th rowspan="2" v-show="editMode" class="text-left" style="width:20px">
                        <v-checkbox color="white"></v-checkbox>
                    </th>
                    <th rowspan="2" v-show="editMode" class="text-left" style="width:20px">Edit</th>
                    <th rowspan="2">PROJECT NAME</th>
                    <th rowspan="2">SUBJECT</th>
                    <th rowspan="2">LOT #</th>
                    <th rowspan="2">STATUS</th>
                    <th rowspan="2">REQUESTED <br> BY</th>
                    <th rowspan="2">REQUESTED DUE <br> DATE</th>
                    <th rowspan="2">ECD</th>
                    <th>JOB DOCUMENT</th>
                    <!-- <th :colspan="JobRequestRequiredData.length" style="border-bottom: 1px solid #d9d9d9 !important;">JOB DOCUMENT</th> -->
                    <th rowspan="2">Actions</th>
                    <th rowspan="2">Upload</th>
                </tr>
                <!-- <tr>
                    <th v-for="header in JobRequestRequiredData" :key="'a'+header.id" class="sub-header">
                        <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <span v-on="on">{{ header.header_name.toUpperCase()}}</span>
                            </template>
                            <span>{{ header.required_name }}</span>
                        </v-tooltip>
                    </th>
                </tr> -->
            </thead>
        </v-table>
    </v-container>
</template>

<script setup>
import ToolBarComponent from '@aspect/ToolBarComponent.vue'
</script>

<script>
import {mapState,mapActions} from 'pinia'
import {useSampleStore} from '../../js/store'
export default {
    data(){
        return{
            toolbarData:{
                title: "JOB REQUEST",
                sub_title: "ALL",
                masters: {
                    show: true,
                    url: '/job_request_settings/job_required'
                },
                back: {
                    show: false,
                    url: '/'
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
<template>
    <v-container class="container-main pt-0">
    <tool-bar-component :toolbarData="toolbarData"></tool-bar-component>
        <v-table fixed-header :height="tableHeight" class="mainTable">
            <thead>
                <tr>
                    <th rowspan="2" v-show="floatButtonData.editButtonActive" class="text-left" style="width:20px">
                        <v-checkbox style="display: flex;" color="white" :input-value="allSelected" @change="toggleSelectAll()"></v-checkbox>
                    </th>
                    <th rowspan="2" v-show="floatButtonData.editButtonActive" class="text-left" style="width:20px">Edit</th>
                    <th rowspan="2">PROJECT NAME</th>
                    <th rowspan="2">SUBJECT</th>
                    <th rowspan="2">LOT #</th>
                    <th rowspan="2">STATUS</th>
                    <th rowspan="2">REQUESTED <br> BY</th>
                    <th rowspan="2">REQUESTED DUE <br> DATE</th>
                    <th rowspan="2" class="text-center">ECD</th>
                    <th :colspan="JobRequestRequiredData.length" style="border-bottom: 1px solid #d9d9d9 !important;" class="text-center">JOB DOCUMENT</th>
                    <th rowspan="2" class="text-center">Actions</th>
                    <th rowspan="2">Upload</th>
                </tr>
                <tr>
                    <th v-for="header in JobRequestRequiredData" :key="'a'+header.id" class="sub-header">
                        <v-tooltip location="bottom">
                            <template v-slot:activator="{ props }">
                                <span v-bind="props">{{ header.header_name.toUpperCase()}}</span>
                            </template>
                            <span>{{ header.required_name }}</span>
                        </v-tooltip>
                    </th>
                </tr>
                <tr v-show="filterMode">
                    <th v-show="floatButtonData.editButtonActive"></th>
                    <th v-show="floatButtonData.editButtonActive"></th>
                    <th>
                        <v-text-field variant="solo" placeholder="Search"></v-text-field>
                    </th>
                    <th>
                        <v-text-field variant="solo" placeholder="Search"></v-text-field>
                    </th>
                    <th>
                        <v-text-field variant="solo" placeholder="Search"></v-text-field>
                    </th>
                    <th></th>
                    <th>
                        <v-text-field variant="solo" placeholder="Search"></v-text-field>
                    </th>
                    <th>
                        <v-text-field variant="solo" placeholder="Search"></v-text-field>
                    </th>
                    <th>
                        <v-text-field variant="solo" placeholder="Search"></v-text-field>
                    </th>
                    <th v-for="header in JobRequestRequiredData" :key="'b'+header.id"></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template v-for="(item, index) in JobRequestData" :key="index">
                    <tr>
                        <td v-show="floatButtonData.editButtonActive">
                            <v-checkbox 
                                style="display: flex;" 
                                color="indigo" 
                                :model-value="allSelected"
                                :input-value="allSelected"
                                @change="setSelected(index)">
                            </v-checkbox>
                        </td>
                        <td v-show="floatButtonData.editButtonActive">
                            <template v-if="item.status == '2' || item.status == '3'">
                                <v-btn disabled icon="mdi-pencil" flat size="30px"></v-btn>
                            </template>
                            <template v-else-if="item.status == '1'">
                                <v-btn icon="mdi-close" flat size="30px" color="red" @click="cancelRequestDialog()"></v-btn>
                            </template>
                            <template v-else>
                                <v-btn icon="mdi-pencil" flat size="30px" color="success" @click="Edit(item)"></v-btn>
                            </template>
                        </td>
                        <td>{{ item.project_name }}</td>
                        <td>{{ item.subject }}</td>
                        <td>{{ item.lot_number }}</td>
                        <td @click="statusEditDialog(item)">
                            <v-chip :color="statusMapping(item.status).color" dark>
                                {{ statusMapping(item.status).label }}
                            </v-chip>
                        </td>
                        <td>{{ item.username }}</td>
                        <td>{{ item.requested_date }}</td>
                        <td>{{ item.job_ecd }}</td>
                        <template v-for="(job_req, index) in JobRequestRequiredData" :key="index">
                            <template v-if="item.requirements.includes(job_req.id)">
                                <td class="text-center">
                                    <v-icon size="30px" color="success">mdi-checkbox-marked-circle-outline</v-icon>
                                </td>
                            </template>
                            <template v-else>
                                <td class="text-center">
                                    <v-icon size="30px" color="red">mdi-close-circle-outline</v-icon>
                                </td>
                            </template>
                        </template>
                        <td>
                            <v-tooltip location="bottom">
                                <template v-slot:activator="{ props }">
                                    <v-icon size="30px" style="color: green" flat v-bind="props">mdi-file-excel</v-icon>
                                </template>
                                <span>Download Excel</span>
                            </v-tooltip>
                            <v-tooltip location="bottom">
                                <template v-slot:activator="{ props }">
                                    <v-icon size="30px" style="color: red" flat v-bind="props">mdi-file-pdf-box</v-icon>
                                </template>
                                <span>Download PDF</span>
                            </v-tooltip>
                        </td>
                        <td>
                            <v-tooltip location="bottom" v-if="item.requirements.length > 0">
                                <template v-slot:activator="{ props }">
                                    <v-icon size="30px" style="color: blue" flat v-bind="props" @click="toggleUploadDialog(item)" >mdi-file-upload</v-icon>
                                </template>
                                <span>Upload</span>
                            </v-tooltip>
                            <v-icon v-else class="disabled" size="30px">mdi-file-upload</v-icon>
                        </td>
                    </tr>
                </template>
            </tbody>
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
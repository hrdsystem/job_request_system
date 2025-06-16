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

        <v-dialog v-model="insertDialog" persistent max-width="600" @keydown.esc="insertDialog = false">
            <v-form id="Insert" ref="Insert" @submit.prevent="Insert">
                <v-card>
                    <v-card-title>
                        <span class="headline">Add Required Job</span>
                        <v-icon style="float: right;" color="white" @click="insertDialog = false">mdi-close</v-icon>
                    </v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="tempName"
                                    @keyup="tempName = $event.target.value.toUpperCase()"
                                    label="PROJECT NAME"
                                    name="project_name"
                                    :rules="rules.required"
                                    class="uppercase-value"
                                    dense
                                    outlined
                                    persistent-placeholder
                                    autocomplete="off"
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>PROJECT NAME</span>
                                </template>
                                </v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="tempSubject"
                                    @keyup="tempSubject = $event.target.value.toUpperCase()"
                                    label="SUBJECT"
                                    name="subject"
                                    class="required uppercase-value"
                                    :rules="rules.required"
                                    dense
                                    outlined
                                    required
                                    autocomplete="off"
                                    persistent-placeholder
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>SUBJECT</span>
                                </template>
                                </v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="tempLot"
                                    @keyup="tempLot = $event.target.value.toUpperCase()"
                                    label="LOT #"
                                    name="lot_number"
                                    class="uppercase-value"
                                    :rules="rules.required"
                                    dense
                                    outlined
                                    persistent-placeholder
                                    autocomplete="off"
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>LOT #</span>
                                </template>
                                </v-text-field>
                            </v-col>
                            <v-col>
                                <v-menu
                                    v-model="insertDatepicker"
                                    :close-on-content-click="false"
                                    offset-y
                                    max-width="290px"
                                    min-width="290px"
                                >
                                    <template v-slot:activator="{ props }">
                                        <v-text-field
                                            v-model="formattedDate"
                                            v-bind="props"
                                            @click:clear="tempAddIssuedDate = null"
                                            label="REQUESTED DUE DATE" 
                                            name="requested_date"
                                            clearable
                                            dense
                                            outlined
                                            readonly
                                            persistent-placeholder
                                        ></v-text-field>
                                    </template>
                                    <v-date-picker
                                        v-model="tempAddIssuedDate"
                                        @update:model-value ="insertDatepicker = false"
                                    ></v-date-picker>
                                </v-menu>
                            </v-col>
                            <v-col cols="12" sm="12" md="12">
                                <v-textarea
                                    v-model="tempNote"
                                    name="note"
                                    label="Note"
                                    class="optional"
                                    rows="3"
                                    outlined
                                    dense
                                >
                                <template v-slot:label>
                                    <span><span style="color: green">#</span>Note</span>
                                </template>
                                </v-textarea>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-file-input
                                    label="Attachment/s"
                                    prepend-icon="mdi-file"
                                    persistent-placeholder
                                    variant="outlined"
                                    multiple
                                    dense
                                ></v-file-input>
                            </v-col>
                            <v-col cols="12" class="mb-1">
                                <b class="pl-1 required-label">JOB Requirements</b>
                            </v-col>
                            <v-row class="mx-1">
                                <template v-for="(job_document, index) in JobRequestRequiredData" :key="index">
                                    <v-col cols="4" sm="4" md="4">
                                        <v-checkbox multiple v-model="tempAddJobRequirement" :label="job_document.required_name.toUpperCase()" :value="job_document.id" color="indigo"></v-checkbox>
                                    </v-col>
                                </template>
                            </v-row>
                        </v-row>
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-btn text color="blue-grey darken-3">SEND</v-btn>
                        <v-btn type="submit" text color="blue-grey darken-3">SUBMIT</v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>

        <v-dialog v-model="editDialog" persistent max-width="600" @keydown.esc="editDialog = false">
            <v-form id="Update" ref="Update" @submit.prevent="Update">
                <v-card>
                    <v-card-title>
                        <span class="headline">{{ editData.project_name }}</span>
                        <v-icon style="float: right;" color="white" @click="editDialog = false">mdi-close</v-icon>
                    </v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="12" sm="6" md="6">
                                <v-text-field
                                    v-model="tempName"
                                    @keyup="tempName = $event.target.value.toUpperCase()"
                                    autocomplete="off"
                                    name="project_name"
                                    label="Project Name"
                                    class=" uppercase-value"
                                    dense
                                    outlined
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="6">
                                <v-text-field
                                    v-model="tempSubject"
                                    @keyup="tempSubject = $event.target.value.toUpperCase()"
                                    autocomplete="off"
                                    name="subject"
                                    label="Subject"
                                    class=" uppercase-value"
                                    dense
                                    outlined
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="6" md="6">
                                <v-text-field
                                    v-model="tempLot"
                                    @keyup="tempLot = $event.target.value.toUpperCase()"
                                    autocomplete="off"
                                    name="lot_number"
                                    label="Lot #"
                                    class="uppercase-value"
                                    dense
                                    outlined
                                ></v-text-field>
                            </v-col>
                            <v-col>
                                <v-menu
                                    v-model="insertDatepicker"
                                    :close-on-content-click="false"
                                    offset-y
                                    max-width="290px"
                                    min-width="290px"
                                >
                                    <template v-slot:activator="{ props }">
                                        <v-text-field
                                            v-model="formattedDate"
                                            v-bind="props"
                                            @click:clear="tempAddIssuedDate = null"
                                            label="REQUESTED DUE DATE" 
                                            name="requested_date"
                                            clearable
                                            dense
                                            outlined
                                            readonly
                                        ></v-text-field>
                                    </template>
                                    <v-date-picker
                                        v-model="tempAddIssuedDate"
                                        @update:model-value ="insertDatepicker = false"
                                    ></v-date-picker>
                                </v-menu>
                            </v-col>
                            <!-- <v-col cols="12" sm="6" md="6">
                                <v-select
                                    v-model="tempStatus"
                                    :items="statusItem"
                                    item-value="value"
                                    item-title="text"
                                    label="Status"
                                    name="status"
                                >
                                </v-select>
                            </v-col> -->
                            <v-col cols="12" sm="12" md="12">
                                <v-textarea
                                    v-model="tempNote"
                                    name="note"
                                    label="Note"
                                    class="optional"
                                    rows="3"
                                    outlined
                                    dense
                                >
                                <template v-slot:label>
                                    <span><span style="color: green">#</span>Note</span>
                                </template>
                                </v-textarea>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-file-input
                                    label="Attachment/s"
                                    prepend-icon="mdi-file"
                                    persistent-placeholder
                                    variant="outlined"
                                    multiple
                                    dense
                                ></v-file-input>
                            </v-col>
                            <v-col cols="12" class="mb-1">
                                <b class="pl-1 required-label">JOB Requirements</b>
                            </v-col>
                            <v-row class="mx-1">
                                <template v-for="(job_document, index) in JobRequestRequiredData" :key="index">
                                    <v-col cols="4" sm="4" md="4">
                                        <v-checkbox multiple v-model="tempAddJobRequirement" :label="job_document.required_name.toUpperCase()" :value="job_document.id" color="indigo"></v-checkbox>
                                    </v-col>
                                </template>
                            </v-row>
                        </v-row>
                        <input type="hidden" name="id" :value="editData.id">
                        <input type="hidden" name="old_job_req" :value="editData.requirements">
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-tooltip location="bottom">
                            <template v-slot:activator="{ props }">
                                <v-btn style="border: 1px solid grey; background-color: #e74c3c;" color="white" @click="cancelRequestDialog()" v-bind="props">
                                    <v-icon>mdi-email-remove-outline</v-icon>CANCEL
                                </v-btn>
                            </template>
                            <span>CANCEL REQUEST</span>
                        </v-tooltip>
                        <v-tooltip location="bottom">
                            <template v-slot:activator="{ props }">
                                <v-btn style="border: 1px solid grey; background-color: #227093;" color="white" v-bind="props">
                                    <v-icon>mdi-email-edit-outline</v-icon>REVISE
                                </v-btn>
                            </template>
                            <span>REVISE</span>
                        </v-tooltip>
                        <v-tooltip location="bottom">
                            <template v-slot:activator="{ props }">
                                <v-btn type="submit" style="border: 1px solid grey" color="blue-grey darken-3" v-bind="props">
                                    <v-icon>mdi-content-save</v-icon>SAVE
                                </v-btn>
                            </template>
                            <span>SAVE</span>
                        </v-tooltip>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>

        <v-dialog v-model="uploadDialog" persistent :width="uploadTab == 1 ? 600 : 800 " @keydown.esc="uploadDialog = false">
            <v-card>
                <v-card-title>
                    <span class="headline" v-if="uploadTab == 0" >Job Requirement</span>
                    <span class="headline" v-else-if="uploadTab == 1" >Upload {{ currentDocument.project_name }}</span>
                    <span class="headline" v-else>{{ currentDocument.project_name }} Upload History</span>
                    <v-icon style="float: right;" color="white" @click="uploadDialog = false">mdi-close</v-icon>
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-window v-model="uploadTab" touchless>
                        <v-window-item>
                            <v-col cols="auto">
                                <v-row class="mb-5 mt-0 mx-n2">
                                    <v-col cols="12" sm="4" md="4">
                                        <v-text-field
                                            v-model="ECD"
                                            @click:clear="ECD = null"
                                            label="ECD"
                                            hide-details
                                            readonly
                                            persistent-placeholder
                                        ></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="5" md=5 class="text-center"> 
                                        <div class="text-subtitle-2 font-weight-bold" :class="{'mt-3': xs }">
                                            Subject : {{ requestDetails.subject }}
                                        </div>
                                    </v-col>
                                    <v-col cols="12" sm="3" md="3" class="text-right">
                                        <v-tooltip location="bottom">
                                            <template v-slot:activator="{ props }">
                                                <v-btn
                                                    color="primary"
                                                    small
                                                    deep-purple
                                                    v-bind="props"
                                                    @click="editECD = !editECD"                                        
                                                >
                                                    <v-icon left>mdi-calendar-edit</v-icon>Edit ECD
                                                </v-btn>
                                            </template>
                                            <span>Edit Estimation Completion Date</span>
                                        </v-tooltip>
                                    </v-col>
                                </v-row>
                            </v-col>
                        </v-window-item>
                    </v-window>

                    <v-table fixed-header class="mainTable">
                        <thead>
                            <tr>
                                <th v-show="editECD">Edit</th>
                                <th>Type</th>
                                <th>Document</th>
                                <th>
                                    <v-tooltip location="bottom">
                                        <template v-slot:activator="{ props }">
                                            <span v-bind="props">ECD</span>
                                        </template>
                                        <span>Estimated Completion Date</span>
                                    </v-tooltip>
                                </th>
                                <th>Uploader</th>
                                <th>Date Uploaded</th>
                                <th title="Reason for Updating">Reason</th>
                                <th style="width:20px">Upload</th>
                                <th v-if="editECD">New Uploads</th>
                                <th>Viewed By</th>
                                <th>Date Viewed</th>
                                <th style="width:20px">History</th>
                            </tr>
                        </thead>
                        <tbody v-if="requiredDocuments.length > 0">
                            <tr >
                                
                            </tr>
                        </tbody>
                    </v-table>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                <v-spacer></v-spacer>
                <v-tooltip location="bottom">
                    <template v-slot:activator="{ props }">
                        <v-btn color="primary" @click="toggleSendDialog()" v-bind="props">
                            <v-icon>mdi-send</v-icon>Send
                        </v-btn>
                    </template>
                    <span>Send to Email</span>
                </v-tooltip>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="cancelDialog" persistent max-width="300" @keydown.esc="cancelDialog = false">
            <v-card>
                <v-card-title>
                    <span class="headline">Cancel Request?</span>
                    <v-icon style="float: right;" color="white" @click="cancelDialog = false">mdi-close</v-icon>
                </v-card-title>
                <v-card-text>
                    <v-col cols="12" class="mb-0">
                        <v-textarea
                            v-model="cancellingReason"
                            label="Reason for Cancelling"
                            name="cancelling_reason"
                            persistent-placeholder
                            :rules="rules.required"
                            rows="3"
                        ></v-textarea>
                    </v-col>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-tooltip location="bottom">
                        <template v-slot:activator="{ props }">
                            <v-btn color="primary" depressed :disabled="cancellingReason == null || cancellingReason == ''" @click="toggleSendDialog()" v-bind="props">
                                <v-icon>mdi-send</v-icon>Send
                            </v-btn>
                        </template>
                        <span>Send to Email</span>
                    </v-tooltip>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="sendDialog" persistent max-width="600" @keydown.esc="sendDialog = false">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ uploadDialog ? 'Send Uploading Changes' : 'Send Request' }}</span>
                    <v-icon style="float: right;" color="white" @click="sendDialog = false">mdi-close</v-icon>
                </v-card-title>
                <v-card-text>
                    <v-row class="mx-2" v-if="!uploadDialog">
                        <v-col>
                            <b>Email Subject:</b><br>
                            <b class="ml-4">working</b>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="12" sm="6" md="6">
                            <v-autocomplete
                            
                            ></v-autocomplete>
                        </v-col>
                    </v-row>
                </v-card-text -card-text>
            </v-card>
        </v-dialog>

        <v-dialog v-model="statusDialog" persistent max-width="250px" @keydown.esc="statusDialog = false">
            <v-form id="Status" ref="Status" @submit.prevent="Status">
                <template v-slot:default="{ props }">
                    <v-card>
                        <v-card-title>
                            <span class="headline">Update Status</span>
                            <v-icon style="float: right;" color="white" @click="statusDialog = false">mdi-close</v-icon>
                        </v-card-title>
                        <v-card-text>
                            <v-select
                                v-model="tempStatus"
                                :items="statusItem"
                                item-value="value"
                                item-title="text"
                                label="Status"
                                name="status"
                            >
                            </v-select>
                            <input type="hidden" name="id" :value="editData.id">
                        </v-card-text>
                        <v-divider></v-divider>
                        <v-card-actions>
                            <v-btn @click="statusDialog = false" color="red">Cancel</v-btn>
                            <v-btn type="submit" text color="success">save</v-btn>
                        </v-card-actions>
                    </v-card>
                </template>
            </v-form>
        </v-dialog>

        <v-dialog v-model="deleteDialog" persistent max-width="300" @keydown.esc="deleteDialog = false">
            <v-card>
                <v-card-title>
                    <span>Delete this record?</span>
                    <v-icon style="float: right;" color="white" @click="deleteDialog = false">mdi-close</v-icon>
                </v-card-title>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="blue-grey darken-3"
                        text
                        @click="Delete"
                    >Agree
                    </v-btn>
                    <v-btn
                        color="blue-grey darken-3"
                        text
                        @click="deleteDialog = false"
                    >Disagree</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <snack-bar-component :snackbar="snackbar"></snack-bar-component>

        <float-button-component
            :floatButtonData="floatButtonData"
            @addButtonClicked="toggleAddDialog($event)"
            @editButtonClicked="floatButtonData.editButtonActive = !floatButtonData.editButtonActive"
            @deleteButtonHandle="toggleDeleteDialog($event)"
        ></float-button-component>
    </v-container>
</template>

<script setup>
import ToolBarComponent from '@aspect/ToolBarComponent.vue'
import SnackBarComponent from '@aspect/SnackBarComponent.vue'
import FloatButtonComponent from '@aspect/FloatButtonComponent.vue'
import {useDisplay} from 'vuetify'
const { xs } = useDisplay()
</script>

<script>
import {mapState,mapActions, mapWritableState} from 'pinia'
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
            floatButtonData:{
                addButton: true,
                editButton: true,
                deleteButton: true
            },

            snackbar: {
                color: 'blue-grey',
                text: '',
                show: false
            },
            

            //SECTION - DIALOGS
            insertDialog: false,
            editDialog: false,
            deleteDialog: false,
            uploadDialog: false,
            cancelDialog: false,
            sendDialog:false,
            statusDialog: false,

            //SECTION - ADD & EDIT DIALOG DATA
            tempAddIssuedDate: '',
            tempName: null,
            tempSubject: null,
            tempLot: null,
            tempNote: null,
            tempAddJobRequirement: [],
            tempStatus: null,
            insertDatepicker: false,
            editECD: false,
            editData: [],
            
            //SECTION - ARRAY DATA FOR UPLOADING
            uploadTab: 0,
            ECD: null,
            currentDocument: {},
            requestDetails: {},
            requiredDocuments: [],

            //SECTION - FOR CANCELLING REQUEST / SENDING EMAIL
            cancellingReason: null,
            toRecipients: [],
            ccRecipients: [],
            projectMembers: [],
            toSearchStr: [],
            ccSearchStr: [],

            //SECTION - STATUS ITEM
            statusItem:[
                { value: 0, text: 'NEW', color: 'rgba(231, 76, 60, 1)' }, // Red
                { value: 1, text: 'ONGOING', color: 'rgba(52, 152, 219, 1)' }, // Blue
                { value: 2, text: 'COMPLETED', color: 'rgba(46, 204, 113, 1)' }, // Green
                { value: 3, text: 'CANCELLED', color: 'rgba(217, 217, 217, 1)' } // Grey
            ]
        }
    },

    computed:{
        ...mapState(useSampleStore,[
            'editMode',
            'filterMode',
            'viewMode',
            'rules',
            'tableHeight',
            'JobRequestRequiredData',
            'JobRequestData',
            'JobRequestRecords',
            'JobRequestSearch',
            'JobRequestSort'
        ]),

        ...mapWritableState(useSampleStore,[
            'allSelected',
            'selectedRows'
        ]),

        formattedDate() {
            if (!this.tempAddIssuedDate) return '';
            const date = new Date(this.tempAddIssuedDate);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`; // Format: YYYY-MM-DD
        },
    },
    methods:{
        ...mapActions(useSampleStore,[
            'jobRequiredPage',
            'searchColumn',
            'sortColumn',
            'jobRequestPage',
            'setSelected',
            'resetToggleSelectAll'
        ]),

        statusMapping(status){
            const mapping = {
                0 : { label: 'NEW', color: 'rgba(231, 76, 60, 1)'},
                1 : { label: 'ONGOING', color: 'rgba(52, 152, 219, 1)'},
                2 : { label: 'COMPLETED', color: 'rgba(46, 204, 113, 1)'},
                3 : { label: 'CANCELLED', color: 'rgba(217, 217, 217, 1)'},
            }
            return mapping[status] || {label: 'Unknown', color: grey};
        },

        searchCol(e, column){
            this.searchColumn({
                selector: e,
                column: column,
                page: 'JobRequestData',
                search: 'JobRequestSearch'
            })
        },

        sortCol(e, column){
            this.sortColumn({
                selector: e,
                column: column,
                page: 'JobRequestData',
                sort: 'JobRequestSort'
            })
            this.jobRequestPage()
        },

        statusEditDialog(data){
            this.editData = data
            this.tempStatus = data.status
            this.statusDialog = true
        },

        toggleUploadDialog(data){
            this.requestDetails = data
            this.requiredDocuments = []
            this.jobRequiredPage(data.id)
            this.uploadDialog = true
        },

        cancelRequestDialog(){
            this.cancellingReason = ''
            this.cancelDialog = true
        },

        toggleSendDialog(){
            this.sendDialog = true
        },

        toggleAddDialog(){
            this.insertDialog = true
            this.tempName = null
            this.tempLot = null
            this.tempSubject = null
            this.tempAddIssuedDate = null
            this.tempNote = null
            this.tempAddJobRequirement = []
        },

        toggleDeleteDialog(){
            this.deleteDialog = true
        },

        toggleSelectAll(){
            this.allSelected =  !this.allSelected
            if(this.allSelected){
                this.selectedRows = this.JobRequestData.map((_, index) => index)
                console.log('Select All', this.selectedRows)
            } else{
                this.selectedRows = []
                console.log('DESELECT', this.selectedRows)
            }
        },

        toggleAttachmentDialog(attachments) {
            this.attachments = attachments
            this.attachmentDialog = true
        },
    }
}
</script>

<style scoped>


</style>
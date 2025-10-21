<template>
    <v-container class="container-main pt-0">
    <tool-bar-component :toolbarData="toolbarData"></tool-bar-component>
        <v-table fixed-header :height="tableHeight" class="mainTable">
            <thead>
                <tr>
                    <th rowspan="2" v-show="floatButtonData.editButtonActive" class="text-left" style="width:20px">
                        <v-checkbox style="display: flex;" color="white" v-model="allSelected"></v-checkbox>
                    </th>
                    <th rowspan="2" v-show="floatButtonData.editButtonActive" class="text-left" style="width:20px">Edit</th>
                    <th rowspan="2" class="text-center Sortable" @click="sortCol($event.target, 'job_requests.project_name')">PROJECT NAME</th>
                    <th rowspan="2" class="text-center Sortable" @click="sortCol($event.target, 'job_requests.subject')">SUBJECT</th>
                    <th rowspan="2" class="text-center Sortable" @click="sortCol($event.target, 'job_requests.lot_number')">LOT #</th>
                    <th rowspan="2" class="text-center Sortable" @click="sortCol($event.target, 'job_requests.status')">STATUS</th>
                    <th rowspan="2" class="text-center Sortable" @click="sortCol($event.target, 'job_requests.note')">NOTE</th>
                    <th rowspan="2" class="text-center">Att</th>
                    <th rowspan="2" class="text-center Sortable" @click="sortCol($event.target, 'job_requests.created_by')">REQUESTED <br> BY</th>
                    <th rowspan="2" class="text-center Sortable" @click="sortCol($event.target, 'job_requests.requested_date')">REQUESTED DUE <br> DATE</th>
                    <th rowspan="2" class="text-center">ECD</th>
                    <th :colspan="JobRequestRequiredData.length" style="border-bottom: 1px solid #d9d9d9 !important;" class="text-center">JOB DOCUMENT</th>
                    <th rowspan="2" class="text-center">Actions</th>
                    <th rowspan="2">Upload</th>
                </tr>
                <tr>
                    <th v-for="header in JobRequestRequiredData" :key="'a'+header.id" class="sub-header text-center">
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
                        <v-text-field autocomplete="off" variant="solo" placeholder="Search" @keyup="searchCol($event.target, 'projects.name')"></v-text-field>
                    </th>
                    <th>
                        <v-text-field autocomplete="off" variant="solo" placeholder="Search" @keyup="searchCol($event.target, 'job_requests.subject')"></v-text-field>
                    </th>
                    <th>
                        <v-text-field autocomplete="off" variant="solo" placeholder="Search" @keyup="searchCol($event.target, 'job_requests.lot_number')"></v-text-field>
                    </th>
                    <th>
                        <v-text-field autocomplete="off" variant="solo" placeholder="Search" @keyup="searchCol($event.target, 'job_requests.status')"></v-text-field>
                    </th>
                    <th></th>
                    <th>
                        <v-text-field autocomplete="off" variant="solo" placeholder="Search" @keyup="searchCol($event.target, 'users.username')"></v-text-field>
                    </th>
                    <th>
                        <v-text-field autocomplete="off" variant="solo" placeholder="Search" @keyup="searchCol($event.target, 'job_requests.requested_date')"></v-text-field>
                    </th>
                    <th v-for="header in JobRequestRequiredData" :key="'b'+header.id"></th>
                    <th></th>
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
                                v-model="selectedRows"
                                :value="index">
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
                                <v-btn icon="mdi-pencil" flat size="30px" style="background-color: #227093; color: white;" @click="Edit(item)"></v-btn>
                            </template>
                        </td>
                        <td class="text-center">{{ item.projects_name }}</td>
                        <td class="text-center">{{ item.subject }}</td>
                        <td class="text-center">{{ item.lot_number }}</td>
                        <td class="text-center" @click="statusEditDialog(item)">
                            <v-chip :color="statusMapping(item.status).color" dark>
                                {{ statusMapping(item.status).label }}
                            </v-chip>
                        </td>
                        <td class="text-ellipsis">
                            <v-tooltip location="bottom">
                                <template v-slot:activator="{ props }">
                                    <span v-bind="props">{{ item.note }}</span>
                                </template>
                                <span>{{ item.note }}</span>
                            </v-tooltip>    
                        </td>
                        <td class="text-center">
                            <v-tooltip v-if="item.attachments.length > 0" location="bottom">
                                <template v-slot:activator="{ props }">
                                    <v-icon @click="toggleAttachmentDialog(item.attachments)" size="30px" style="color: goldenrod" flat v-bind="props">mdi-folder
                                        <p class="white--text">{{item.attachments.length}}</p>
                                    </v-icon>
                                </template>
                                <span>Attachments</span>
                            </v-tooltip>
                            <v-icon v-else class="disabled" style="color: gray;" size="30px">mdi-folder</v-icon>
                        </td>
                        <td class="text-center">
                            <v-tooltip location="botom">
                                <template v-slot:activator="{ props }">
                                    <v-avatar v-bind="props">
                                        <v-img
                                            :src="item.photo "
                                            :lazy-src="baseDir+'/img/avatar.png'"
                                        >
                                        </v-img>
                                    </v-avatar>
                                </template>
                                <span>{{ item.username }}</span>
                            </v-tooltip>
                        </td>
                        <td class="text-center">{{ item.requested_date }}</td>
                        <td class="text-center" @click="toggleEcdDialog(item)">{{ item.job_ecd }}</td>
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
                        <td class="text-center">
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
                        <td class="text-center">
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
                                <v-autocomplete
                                    v-model="tempProjectName"
                                    :items="projectLists"
                                    item-title="project_name"
                                    item-value="project_id"
                                    :rules="rules.required"
                                    @update:modelValue="getLotProjectList"
                                    label="PROJECT NAME"
                                    name="project_name"
                                    outlined
                                    autocomplete="off"
                                    hide-details
                                    persistent-placeholder
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>PROJECT NAME</span>
                                </template>
                                </v-autocomplete>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="uniqueSubject"
                                    label="SUBJECT"
                                    name="subject"
                                    persistent-placeholder
                                    outlined
                                    autocomplete="off"
                                    hide-details
                                    readonly
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>SUBJECT</span>
                                </template>
                                </v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-autocomplete
                                    v-model="tempLot2"
                                    :items="uniqueLots"
                                    item-title="lot"
                                    item-value="lot"
                                    label="LOT #"
                                    name="lot_number"
                                    persistent-placeholder
                                    outlined
                                    autocomplete="off"
                                    hide-details
                                    @update:modelValue="updateProjectRegisteredLotId"
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>LOT #</span>
                                </template>
                                </v-autocomplete>
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
                                    persistent-placeholder
                                    dense
                                >
                                <template v-slot:label>
                                    <span><span style="color: green">#</span>Note</span>
                                </template>
                                </v-textarea>
                            </v-col>
                            <v-col cols="6" sm="6" md="6"
                                @drop.prevent="multiDropFile('tempAddAttachments', 'addAttachments', $event.dataTransfer.files)"
                                @dragover.prevent
                                @change="multiChangeFile('tempAddAttachments', 'addAttachments')"
                            >
                                <v-file-input
                                    v-model="tempAddAttachments"
                                    label="Attachment/s"
                                    placeholder="testing "
                                    prepend-icon="mdi-file"
                                    variant="outlined"
                                    multiple
                                    persistent-placeholder
                                    dense
                                ></v-file-input>
                            </v-col>
                            <v-col cols="12" sm="6" md="6"></v-col>
                            <v-col cols="12" class="mb-5" v-if="addAttachments.length > 0">
                                <v-table>
                                    <tbody>
                                        <tr v-for="(file, index) in  addAttachments" :key="'cf' + index">
                                            <td class="text-center" width="40px">
                                                <v-icon @click="deleteItemDialog = true; tempRemoveFiles = {model: 'addAttachments', index: index}">mdi-delete</v-icon>
                                            </td>
                                            <td class="text-center" width="100px">
                                                <v-icon size="50px">{{ getIcon(file.filename) }}</v-icon>
                                            </td>
                                            <td>
                                                <span>Original file name:</span>
                                                <a :href="file.url" target="_blank" :download="file.filename">{{file.filename}}</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </v-table>
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
                            <input type="hidden" name="send_to_hrd" :value="sendToHrd">
                            <input type="hidden" name="register_id" :value="project_registered_id">
                        </v-row>
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-tooltip location="bottom">
                            <template v-slot:activator="{ props }">
                                <v-btn disabled style="border: 1px solid grey;" color="blue-grey darken-3" @click="toggleSendDialog()" v-bind="props">
                                    <v-icon>mdi-send</v-icon>Send
                                </v-btn>
                            </template>
                            <span>Send to Email</span>
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

        <v-dialog v-model="editDialog" persistent max-width="600" @keydown.esc="editDialog = false">
            <v-form id="Update" ref="Update" @submit.prevent="Update">
                <v-card>
                    <v-card-title >
                        <span class="headline">{{ editData.projects_name }}</span>
                        <v-icon style="float: right;" color="white" @click="editDialog = false">mdi-close</v-icon>
                    </v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="6" sm="6" md="6">
                                <v-autocomplete
                                    v-model="tempProjectName"
                                    :items="projectLists"
                                    item-title="project_name"
                                    item-value="project_id"
                                    :rules="rules.required"
                                    @update:modelValue="getLotProjectList"
                                    label="PROJECT NAME"
                                    name="project_name"
                                    outlined
                                    autocomplete="off"
                                    hide-details
                                    persistent-placeholder
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>PROJECT NAME</span>
                                </template>
                                </v-autocomplete>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="uniqueSubject"
                                    label="SUBJECT"
                                    name="subject"
                                    persistent-placeholder
                                    outlined
                                    autocomplete="off"
                                    hide-details
                                    readonly
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>SUBJECT</span>
                                </template>
                                </v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-autocomplete
                                    v-model="tempLot2"
                                    :items="uniqueLots"
                                    item-title="lot"
                                    item-value="lot"
                                    label="LOT #"
                                    name="lot_number"
                                    persistent-placeholder
                                    outlined
                                    autocomplete="off"
                                    hide-details
                                    @update:modelValue="updateProjectRegisteredLotId"
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>LOT #</span>
                                </template>
                                </v-autocomplete>
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
                            <v-col cols="6" sm="6" md="6"
                                @drop.prevent="multiDropFile('tempEditAttachments', 'editAttachments', $event.dataTransfer.files)"
                                @dragover.prevent
                                @change="multiChangeFile('tempEditAttachments', 'editAttachments')"
                            >
                                <v-file-input
                                    v-model="tempEditAttachments"
                                    label="Attachment/s"
                                    prepend-icon="mdi-file"
                                    persistent-placeholder
                                    variant="outlined"
                                    multiple
                                    dense
                                ></v-file-input>
                            </v-col>
                            <v-col cols="12" sm="6" md="6"></v-col>
                            <v-col cols="12" class="mb-5" v-if="editAttachments.length > 0">
                                <v-table>
                                    <tbody>
                                        <tr v-for="(file, index) in  editAttachments" :key="'cf' + index">
                                            <td class="text-center" width="40px">
                                                <v-icon @click="deleteItemDialog = true; tempRemoveFiles = {model: 'editAttachments', index: index}">mdi-delete</v-icon>
                                            </td>
                                            <td class="text-center" width="100px">
                                                <v-icon size="50px">{{ getIcon(file.filename) }}</v-icon>
                                            </td>
                                            <td>
                                                <span>Original file name:</span>
                                                <a :href="file.url" target="_blank" :download="file.filename">{{file.filename}}</a>
                                            </td>   
                                        </tr>
                                    </tbody>
                                </v-table>
                            </v-col>
                            <v-col cols="12" class="mb-1">
                                <b class="pl-1 required-label">JOB Requirements</b>
                            </v-col>
                            <v-row class="mx-1">
                                <template v-for="(job_document, index) in JobRequestRequiredData" :key="index">
                                    <v-col cols="4" sm="4" md="4">
                                        <v-checkbox hide-details multiple v-model="tempAddJobRequirement" :label="job_document.required_name.toUpperCase()" :value="job_document.id" color="indigo"></v-checkbox>
                                    </v-col>
                                </template>
                            </v-row>
                        </v-row>
                        <input type="hidden" name="id" :value="editData.id">
                        <input type="hidden" name="register_id" :value="project_registered_id">
                        <input type="hidden" name="old_job_req" :value="editData.requirements">
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <!-- <v-tooltip location="bottom">
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
                        </v-tooltip> -->
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

        <v-dialog v-model="uploadDialog" persistent :width="uploadTab == 1 ? 600 : 800 " @keydown.esc="showConfirmDialog()">
            <v-form id="uploadForm" ref="uploadForm" @submit.prevent="processUploadForm">

                <v-card>
                    <v-card-title>
                        <span class="headline" v-if="uploadTab == 0" >Job Requirement</span>
                        <span class="headline" v-else-if="uploadTab == 1" >Upload {{ CurrentSubject }}</span>
                        <span class="headline" v-else>{{ CurrentSubject }} Upload History</span>
                        <v-icon style="float: right;" color="white" @click="showConfirmDialog()">mdi-close</v-icon>
                    </v-card-title>
                    <v-card-text class="pa-0">
                        <v-window v-model="uploadTab" touchless>
                            <v-window-item>
                                <v-col cols="auto">
                                    <v-row class="mb-5 mt-0 mx-n2">
                                        <v-col cols="12" sm="4" md="4">
                                            <v-text-field
                                                v-model="tempEcd"
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
                                <v-table fixed-header class="mainTable">
                                    <thead>
                                        <tr>
                                            <th v-show="editECD">Edit</th>
                                            <th class="text-center">Type</th>
                                            <th class="text-center">Document</th>
                                            <th class="text-center">
                                                <v-tooltip location="bottom">
                                                    <template v-slot:activator="{ props }">
                                                        <span v-bind="props">ECD</span>
                                                    </template>
                                                    <span>Estimated Completion Date</span>
                                                </v-tooltip>
                                            </th>
                                            <th class="text-center">Uploader</th>
                                            <th class="text-center">Date Uploaded</th>
                                            <th title="Reason for Updating">Reason</th>
                                            <th style="width:20px">Upload</th>
                                            <th v-if="hasNewUploads">New Uploads</th>
                                            <th class="text-center">Viewed By</th>
                                            <th class="text-center">Date Viewed</th>
                                            <th style="width:20px">History</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="requiredDocuments.length > 0">
                                        <tr v-for="(doc, i) in requiredDocuments" :key="'rd' + i">
                                            <td class="text-center icon-btn" v-show="editECD">
                                                <v-btn icon="mdi-pencil" flat size="30px" style="background-color: #227093; color: white;" @click="EditECD(doc)"></v-btn>
                                            </td>
                                            <td>
                                                <a
                                                    :href="documentLink(CurrentSubject, doc.filling_mark, doc.job_request_id, doc.document_id)"
                                                    target="_blank"
                                                    icon="mdi-file"
                                                    @click="reloadCadRequests()"
                                                    v-if="doc.uploads.length === 1">
                                                    <v-icon size="30px" color="blue">mdi-file</v-icon>
                                                </a>
                                                <div v-else-if="doc.uploads.length > 1">
                                                    <v-icon size="30px" style="color: goldenrod;" @click="toggleJobAttachments(activeRequest, doc.document_id)">
                                                        mdi-folder
                                                    </v-icon>
                                                </div>
                                                <v-icon v-else color="grey" size="30px">mdi-file</v-icon>
                                            </td>
                                            <td class="text-center">{{ doc.required_name }}</td>
                                            <td>
                                                <v-tooltip v-if="doc.estimated_completion_date != null && doc.changedECD" color="black" transition="fade-transition" location="bottom">
                                                    <template v-slot:activator="{ props }">
                                                        <span v-bind="props">
                                                            {{ doc.estimated_completion_date }}
                                                            <v-icon color="red darken-3" size="20">mdi-exclamation-thick</v-icon>
                                                        </span>
                                                    </template>
                                                    <span>Not Yet Saved</span>
                                                </v-tooltip>
                                                <span v-else>{{ doc.estimated_completion_date }}</span> 
                                            </td> 
                                            <td class="text-center">{{ doc.job_uploader }}</td>
                                            <td class="text-center">{{ dateOnly(doc.date_uploaded) }}</td>
                                            <td class="text-center">{{ doc.updating_reason }}</td>
                                            <td class="text-center icon-btn">
                                                <v-tooltip location="bottom" >
                                                    <template v-slot:activator="{ props }">
                                                        <v-icon color="blue darken-3" size="30" v-bind="props" @click="showUploadTab(doc)">mdi-upload</v-icon>
                                                    </template>
                                                    <span>Upload</span>
                                                </v-tooltip>
                                                <!-- <v-icon v-else class="disabled" size="30">mdi-upload</v-icon> -->
                                                <!-- v-if="!disableUpload" -->
                                            </td>
                                            <td v-if="hasNewUploads" class="text-center">
                                                <!-- <v-icon v-if="doc.newUploads.length > 0" size="30px">mdi-file</v-icon> -->
                                                <v-tooltip v-if="doc.newUploads.length > 0 " color="black" transition="fade-transition" location="bottom">
                                                    <template v-slot:activator="{ props }">
                                                        <span v-bind="props" style="position: relative;">
                                                            <a
                                                                :href="doc.newUploads[0].url"
                                                                target="_blank"
                                                                v-if="doc.newUploads.length === 1"
                                                            >
                                                            <v-icon size="30px">mdi-file</v-icon>
                                                            </a>
                                                            <div
                                                                v-else-if="doc.newUploads.length > 1"
                                                                @click="newUploadedFiles = doc.newUploads; newUploadsDialog = true; dialogTitle = doc.required_name" 
                                                            >
                                                                <v-icon size="30px" style="color: goldenrod;">
                                                                    mdi-folder
                                                                </v-icon>
                                                                <v-icon color="red darken-3" size="20" style="position: absolute;">mdi-exclamation-thick</v-icon>
                                                            </div>
                                                            <v-icon v-if="doc.newUploads.length === 1" color="red darken-3" size="20" style="position: absolute;">mdi-exclamation-thick</v-icon>
                                                        </span>
                                                    </template>
                                                    <span>Not Yet Saved</span>
                                                </v-tooltip>
                                                <v-icon v-else color="grey" size="30px">mdi-folder</v-icon>
                                            </td>
                                            <td class="text-center">temp viewed by</td>
                                            <td class="text-center">temp date viewed</td>
                                            <td class="text-center icon-btn">
                                                <v-tooltip location="bottom" v-if="doc.updating_reason != null">
                                                    <template v-slot:activator="{ props }">
                                                        <v-icon size="30px" style="color: goldenrod;" v-bind="props" @click="showHistoryTab(doc)">mdi-history</v-icon>
                                                    </template>
                                                    <span>History</span>
                                                </v-tooltip>
                                                <v-icon v-else size="30px">mdi-history</v-icon>
                                            </td>
                                        </tr>
                                    </tbody>
                                </v-table>
                            </v-window-item>
                            <v-window-item>
                                <v-col cols="auto" class="py-0 px-1" v-if="uploadTab == 1">
                                    <v-row class="mx-0">
                                        <v-col cols="12" sm="6" md="6">
                                            <div>
                                                Required Document:<br>
                                                <p class="text-right">
                                                    <span style="padding-right: 30px;" class="font-weight-bold">{{ requiredFileName }}</span>
                                                </p>
                                            </div>
                                        </v-col>
                                        <v-col cols="12" sm="6" md="6">
                                            <span style="color: red;" v-if="invalidFile.length > 0">Invalid File(s):</span><br>
                                            <ul class="red--text">
                                                <li style="color: red" v-for="(invalid, i) in invalidFile" :key="'if' + i">
                                                    {{ invalid.filename }}
                                                </li>
                                            </ul>
                                        </v-col>
                                    </v-row>
                                    <v-row class="mx-0">
                                        <v-col cols="12" sm="6" md="6"
                                            @drop.prevent="multiDropFile('tempRequiredFile', 'requiredFile', $event.dataTransfer.files)"
                                            @dragover.prevent
                                            @change="multiChangeFile('tempRequiredFile', 'requiredFile')"
                                        >
                                            <v-file-input
                                                v-model="tempRequiredFile"
                                                label="Document"
                                                prepend-icon="mdi-file"
                                                :rules="rules.required"
                                                persistent-placeholder
                                                variant="outlined"
                                                multiple
                                            ></v-file-input>
                                        </v-col>
                                    </v-row>
                                </v-col>
                                <v-col cols="12" class="mb-5" v-if="requiredFile.length > 0">
                                    <v-table>
                                        <tbody>
                                            <tr v-for="(file, index) in requiredFile" :key="'rf' + index">
                                                <td class="text-center" width="40px">
                                                    <v-icon @click="deleteItemDialog = true; tempRemoveFiles = { model: 'requiredFile', index : index}">mdi-delete</v-icon>
                                                </td>
                                                <td class="text-center" width="100px">
                                                    <v-icon size="60px">mdi-file-pdf-box</v-icon>
                                                </td>
                                                <td>
                                                    <span>Original file name:</span><br>
                                                    <a :href="file.url" target="_blank">{{file.filename}}</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </v-table>
                                </v-col>
                                <v-col cols="12" v-if="currentDocument.date_uploaded != null">
                                    <v-textarea
                                        v-model="updatingReason"
                                        name="updating_reason"
                                        :rules="rules.required"
                                        persistent-placeholder
                                        variant="outlined"
                                        rows="3"
                                        hide-details
                                    >
                                    <template v-slot:label>
                                        <span><span style="color: red">*</span>Reason for Updating</span>
                                    </template>
                                    </v-textarea>
                                </v-col>
                            </v-window-item>
                            <v-window-item>
                                <v-col cols="auto">
                                    <v-row v-if="uploadHistories.length < 1">
                                        <v-btn @click="console.log('temp for testing')">TEST</v-btn>
                                    </v-row>
                                    <v-table fixed-header class="mainTable" v-if="uploadHistories.length > 0">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Type</th>
                                                <th class="text-center">Document</th>
                                                <th class="text-center">Uploader</th>
                                                <th class="text-center">Date Uploaded</th>
                                                <th class="text-center">Reason</th>
                                                <th class="text-center">Viewed by</th>
                                                <th class="text-center">Date Viewed</th>
                                        </tr>
                                        </thead>
                                        <tbody v-if="requiredDocuments.length > 0">
                                            <tr v-for="(history, index) in uploadHistories" :key="'uh' + index">
                                                <td class="text-center">
                                                    <a
                                                        :href="`/api/job_request/document/${history.files[0].id}/${encodeURIComponent(extractFileName(history.files[0].orig_filename))}`"
                                                        target="_blank"
                                                        icon="mdi-file"
                                                        @click="reloadCadRequests()"
                                                        v-if="history.files.length === 1">
                                                        <v-icon size="30px" color="blue">mdi-file</v-icon>
                                                    </a>
                                                    <div
                                                        v-else-if="history.files.length > 1"
                                                    >
                                                        <v-icon size="30px" style="color: goldenrod;" @click="toggleJobAttachments(activeRequest, history.document_id, history.id)">
                                                            mdi-folder
                                                        </v-icon>
                                                    </div>
                                                    <v-icon v-else color="grey" size="30px">mdi-file</v-icon>
                                                </td>
                                                <td class="text-center">{{ history.required_name }}</td>
                                                <td class="text-center">{{ history.uploaded_by }}</td>
                                                <td class="text-center">{{ dateOnly(history.date_uploaded) }}</td>
                                                <td class="text-center">{{ history.updating_reason }}</td>
                                                <td class="text-center">{{ testing }}</td>
                                                <td>{{ dateOnly(history.date_viewed) }}</td>
                                            </tr>
                                        </tbody>
                                    </v-table>
                                </v-col>
                            </v-window-item>
                        </v-window>
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-btn icon @click="uploadTab = 0" v-show="uploadTab != 0">
                            <v-icon>mdi-arrow-left</v-icon>
                        </v-btn>
                    <v-spacer></v-spacer>
                    <v-btn style="border: 1px solid grey;" outlined small v-if="uploadTab == 1" @click="uploadTab = 0">
                        <v-icon>mdi-close-outline</v-icon>
                        Cancel
                    </v-btn>
                    <v-tooltip location="bottom" v-if="uploadTab == 0">
                        <template v-slot:activator="{ props }">
                            <v-btn style="border: 1px solid grey;" color="primary" @click="toggleSendDialog()" v-bind="props" :disabled="!newUpdates">
                                <v-icon>mdi-send</v-icon>Send
                            </v-btn>
                        </template>
                        <span>Send to Email</span>
                    </v-tooltip>
                    <v-btn style="border: 1px solid grey;" type="submit" variant="outlined" small :disabled="requiredFile.length < 1 || disableUploadAndSend || (currentDocument.date_uploaded != null && updatingReason == null)" v-if="uploadTab == 1">
                        <v-icon color="success">mdi-check-bold</v-icon>
                        OK
                    </v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>

        <v-dialog v-model="cancelDialog" persistent max-width="300" @keydown.esc="cancelDialog = false">
            <v-card>
                <v-card-title>
                    <span class="headline">Cancel Request?</span>
                    <v-icon style="float: right;" color="white" @click="cancelDialog = false">mdi-close</v-icon>
                </v-card-title>
                <!-- <v-card-text> -->
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
                <!-- </v-card-text> -->
                <v-divider></v-divider>
                <v-card-actions>
                    <v-tooltip location="bottom">
                        <template v-slot:activator="{ props }">
                            <v-btn style="border: 1px solid grey" color="primary" depressed :disabled="cancellingReason == null || cancellingReason == ''" @click="toggleSendDialog()" v-bind="props">
                                <v-icon>mdi-send</v-icon>Send
                            </v-btn>
                        </template>
                        <span>Send to Email</span>
                    </v-tooltip>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="sendDialog" persistent width="600" @keydown.esc="sendDialog = false">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ uploadDialog ? 'Send Uploading Changes' : 'Send Request' }}</span>
                    <v-icon style="float: right;" color="white" @click="sendDialog = false">mdi-close</v-icon>
                </v-card-title>
                <!-- <v-card-text style="height:auto;"> -->
                    <v-row class="mx-2">
                        <v-col cols="12" sm="6" md="6">
                            <v-autocomplete
                                v-model="toRecipients"
                                :items="toEmails"
                                item-title="username"
                                item-value="id"
                                name="to_recipients"
                                label="To: "
                                variant="outlined"
                                :search-input.sync="toSearchStr"
                                @change="toSearchStr = ''"
                                @update:search-input="toSearchStr = $event"
                                @update:model-value="toSearchStr = ''"
                                persistent-placeholder
                                multiple
                                hide-selected
                                autocomplete="off"
                                chips
                                return-object
                            >   
                                <template v-slot:chip="{ props, item }">
                                    <v-chip
                                    v-bind="props"
                                    closable
                                    >
                                    <v-avatar left>
                                        <v-img
                                            :src="'/img/avatar.png'"
                                            :lazy-src="'/img/avatar.png'"                                            </v-img>
                                    </v-avatar>
                                    {{ item.title }}
                                    </v-chip>
                                </template>
                                <template v-slot:data="item">
                                    <v-avatar>
                                        <v-img
                                            :src="item.data.photo ? '/' + item.data.photo : '/img/avatar.png'"
                                            :lazy-src="'/img/avatar.png'"
                                        ></v-img>
                                    </v-avatar>
                                    <span>{{ item.data.title }}</span>
                                </template>
                            </v-autocomplete>
                        </v-col>
                        <v-col cols="12" sm="6" md="6">
                            <v-autocomplete
                                v-model="ccRecipients"
                                :items="ccEmails"
                                item-title="username"
                                item-value="id"
                                name="to_recipients"
                                label="CC:"
                                variant="outlined"
                                :search-input.sync="ccSearchStr"
                                @change="ccSearchStr = ''"
                                @update:search-input="ccSearchStr = $event"
                                @update:model-value="ccSearchStr = ''"
                                persistent-placeholder
                                multiple
                                hide-selected
                                clear-on-select
                                return-object
                                autocomplete="off"
                            >
                                <template v-slot:chip="{ props, item }">
                                    <v-chip
                                    v-bind="props"
                                    closable
                                    >
                                        <v-avatar left>
                                            <v-img
                                                :src="item.raw.photo ? `/${item.raw.photo}` : '/img/avatar.png'"
                                                :lazy-src="'/img/avatar.png'">
                                            </v-img>
                                        </v-avatar>
                                    {{ item.title }}
                                    </v-chip>
                                </template>
                                <template v-slot:data="item">
                                    <v-avatar>
                                        <v-img
                                            :src="item.data.avatar ? '/' + item.data.avatar : '/img/avatar.png'"
                                            :lazy-src="baseDir+'/img/avatar.png'"
                                        ></v-img>
                                    </v-avatar>
                                    <span>{{ item.data.title }}</span>
                                </template>
                            </v-autocomplete>
                        </v-col>
                    </v-row>
                <!-- </v-card-text> -->
                <v-divider></v-divider>
                <v-card-actions>
                    <v-tooltip location="bottom">
                        <template v-slot:activator="{ props }">
                            <v-btn
                                depressed
                                small
                                @click="processSending()"
                                v-bind="props"
                                class="mr-3"
                                color="white"
                                style="border: 1px solid green; background-color: #5ab55e;"
                            >
                            <v-icon>mdi-check</v-icon>Agree
                            </v-btn>
                        </template>
                        <span>Send</span>
                    </v-tooltip>
                    <v-tooltip location="bottom">
                        <template v-slot:activator="{ props }">
                            <v-btn
                                depressed
                                small
                                color="grey darken-3"
                                outlined
                                v-bind="props"
                                @click="sendDialog = false"
                                style="border: 1px solid grey;"
                            >
                            <v-icon left>mdi-close</v-icon>Disagree
                            </v-btn>
                        </template>
                        <span>Cancel</span>
                    </v-tooltip>
                </v-card-actions>
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
                        <v-card-text style="padding-top: 0px;">
                            <v-select
                                v-model="tempStatus"
                                :items="statusItem"
                                item-value="value"
                                item-title="text"
                                label="Status"
                                name="status"
                                hide-details
                            >
                            </v-select>
                            <input type="hidden" name="id" :value="editData.id">
                        </v-card-text>
                        <v-divider></v-divider>
                        <v-card-actions>
                            <!-- <v-btn @click="statusDialog = false" color="red">Cancel</v-btn> -->
                            <v-tooltip location="bottom">
                                <template v-slot:activator="{ props }">
                                    <v-btn style="border: 1px solid grey; background-color: #e74c3c;" color="white" v-bind="props" @click="statusDialog = false">
                                        <v-icon>mdi-email-remove-outline</v-icon>CANCEL
                                    </v-btn>
                                </template>
                                <span>CANCEL</span>
                            </v-tooltip>
                            <!-- <v-btn type="submit" text color="success">save</v-btn> -->
                            <v-tooltip location="bottom">
                                <template v-slot:activator="{ props }">
                                    <v-btn type="submit" style="border: 1px solid grey; background-color: #227093;" color="white" v-bind="props">
                                        <v-icon>mdi-email-edit-outline</v-icon>SAVE
                                    </v-btn>
                                </template>
                                <span>SAVE</span>
                            </v-tooltip>
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

        <v-dialog v-model="deleteItemDialog" persistent max-width="300" @keydown.esc="deleteItemDialog = false">
            <v-card>
                <v-card-title>
                    <span>Delete this item?</span>
                    <v-icon style="float: right;" color="white" @click="deleteDialog = false">mdi-close</v-icon>
                </v-card-title>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="blue-grey darken-3"
                        text
                        @click="removeItem"
                    >Agree
                    </v-btn>
                    <v-btn
                        color="blue-grey darken-3"
                        text
                        @click="deleteItemDialog = false"
                    >Disagree</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="confirmDialog" persistent max-width="400" @keydown.esc="confirmDialog = false">
            <v-card>
                <v-card-title>
                    <span class="headline">Please Confirm</span>
                    <v-icon style="float: right;" color="white" @click="confirmDialog = false">mdi-close</v-icon>
                </v-card-title>
                <v-card-text>
                    <p class="h4 mb-0 black--text">{{ confirmationText }}</p>
                </v-card-text>
                <v-card-actions>
                    <v-tooltip location="bottom">
                        <template v-slot:activator="{ props }">
                            <v-btn
                                depressed
                                small
                                @click="confirmationFor == 'uploadDialog' ? closeUploadDialog() : send()"
                                v-bind="props"
                                class="mr-3"
                                color="white"
                                style="border: 1px solid green; background-color: #5ab55e;"
                            >
                            <v-icon>mdi-check</v-icon>Agree
                            </v-btn>
                        </template>
                        <span>{{confirmationFor == 'uploadDialog' ? 'Close' : 'Send'}}</span>
                    </v-tooltip>
                    <v-tooltip location="bottom">
                        <template v-slot:activator="{ props }">
                            <v-btn
                                depressed
                                small
                                color="grey darken-3"
                                outlined
                                v-bind="props"
                                @click="confirmDialog = false"
                                style="border: 1px solid grey;"
                            >
                            <v-icon left>mdi-close</v-icon>Disagree
                            </v-btn>
                        </template>
                        <span>Cancel</span>
                    </v-tooltip>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="newUploadsDialog" persistent max-width="400" @keydown.esc="newUploadsDialog = false">
            <v-card>
                <v-card-title>
                    <span class="headline">{{ dialogTitle }}</span>
                    <v-icon style="float: right;" color="white" @click="newUploadsDialog = false">mdi-close</v-icon>
                </v-card-title>
                <v-card-text>
                    <v-col cols="auto">
                        <v-row v-if="newUploadedFiles.length < 1" class="mb-3">
                            <v-col class="text-center">
                                <v-progress-circular
                                    indeterminate
                                    color="primary"
                                ></v-progress-circular>
                            </v-col>
                        </v-row>
                        <v-table v-else fixed-header class="mainTable">
                            <thead>
                                <tr>
                                    <th style="width: 30px">Type</th>
                                    <th>File Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(file, index) in newUploadedFiles" :key="'att' + index">
                                    <td class="text-center">
                                        <a :href="file.url" target="_blank">
                                            <v-icon color="primary">{{getIcon(file.filename)}}</v-icon>
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="file.url" target="_blank">{{ file.filename }}</a>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-col>
                </v-card-text>
            </v-card>
        </v-dialog>

        <v-dialog v-model="attachmentDialog" persistent max-width="400" @keydown.esc="attachmentDialog = false">
            <v-card>
                <v-card-title>
                    <span>Attachment Dialog</span>
                    <v-icon style="float: right;" color="white" @click="attachmentDialog = false">mdi-close</v-icon>
                </v-card-title>
                <v-card-text>
                    <v-col cols="auto">
                        <v-table fixed-header class="mainTable">
                            <thead>
                                <tr>
                                    <th style="width: 30px">Type</th>
                                    <th>File Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(attachment, i) in attachments" :key="'att' + i">
                                    <td class="text-center">
                                        <a :href="`/job_requests/attachments/${attachment.id}/${attachment.orig_filename}`" target="_blank" class="text-decoration-none">
                                            <v-icon size="25px" color="primary">{{getIcon(attachment.orig_filename)}}</v-icon>
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="getAttachmentLink(attachment)" target="_blank" @click="logUrl(attachment)">
                                            {{attachment.orig_filename}}
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-col>
                </v-card-text>
            </v-card>
        </v-dialog>

        <v-dialog v-model="ecdDialog" persistent max-width="320" @keydown.esc="ecdDialog = false">
            <v-form id="ecdDate" ref="ecdDate" @submit.prevent="ecdForm">
                <v-card>
                    <v-card-title>
                        <span>Estimated Completion Date</span>
                        <v-icon style="float: right;" color="white" @click="ecdDialog = false">mdi-close</v-icon>
                    </v-card-title>
                    <v-card-text>
                        <v-menu
                            v-model="insertDatepicker"
                            :close-on-content-click="false"
                            offset-y
                            max-width="290px"
                            min-width="290px"
                        >
                            <template v-slot:activator="{ props }">
                                <v-text-field
                                    v-model="formatEcdDate"
                                    v-bind="props"
                                    @click:clear="tempEcd = null"
                                    label="ESTIMATED COMPLETION DATE" 
                                    name="ecd_date"
                                    clearable
                                    dense
                                    outlined
                                    readonly
                                    persistent-placeholder
                                    hide-details
                                ></v-text-field>
                            </template>
                            <v-date-picker
                                v-model="tempEcd"
                                @update:model-value ="insertDatepicker = false"
                            ></v-date-picker>
                        </v-menu>
                        <input type="hidden" name="id" :value="editData.id">
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-tooltip location="bottom">
                            <template v-slot:activator="{ props }">
                                <v-btn type="submit" style="border: 1px solid grey; background-color: #227093;" color="white" v-bind="props">
                                    <v-icon>mdi-calendar-cursor-outline</v-icon>SAVE
                                </v-btn>
                            </template>
                            <span>SAVE</span>
                        </v-tooltip>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>

        <v-dialog v-model="editEcdDialog" persistent max-width="330" @keydown.esc="editEcdDialog = false">
            <v-card>
                <v-card-title>
                    <span>Edit {{ activeDocument.required_name }} ECD</span>
                    <v-icon style="float: right;" color="white" @click="editEcdDialog = false">mdi-close</v-icon>
                </v-card-title>
                <v-card-text>
                    <v-menu
                        v-model="insertDatepicker"
                        :close-on-content-click="false"
                        offset-y
                        max-width="290px"
                        min-width="290px"
                    >
                        <template v-slot:activator="{ props }">
                            <v-text-field
                                v-model="formatChangeEcdDate"
                                v-bind="props"
                                @click:clear="currentECD = null"
                                label="ESTIMATED COMPLETION DATE" 
                                clearable
                                dense
                                outlined
                                readonly
                                persistent-placeholder
                                hide-details
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-model="currentECD"
                            @update:model-value ="insertDatepicker = false"
                        ></v-date-picker>
                    </v-menu>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                    <v-tooltip location="bottom">
                        <template v-slot:activator="{ props }">
                            <v-btn @click="saveNewECD()" style="border: 1px solid grey; background-color: #227093;" color="white" v-bind="props">
                                <v-icon>mdi-calendar-cursor-outline</v-icon>SAVE
                            </v-btn>
                        </template>
                        <span>SAVE</span>
                    </v-tooltip>
                </v-card-actions>
            </v-card>
        </v-dialog>
        
        <v-dialog v-model="jobUploadDialog" persistent max-width="400" @keydown.esc="jobUploadDialog = false">
            <v-card>
                <v-card-title>
                    <span>Job Attachment Dialog</span>
                    <v-icon style="float: right;" color="white" @click="jobUploadDialog = false">mdi-close</v-icon>
                </v-card-title>
                <v-card-text>
                    <v-col cols="auto">
                        <v-table fixed-header class="mainTable">
                            <thead>
                                <tr>
                                    <th style="width: 30px">Type</th>
                                    <th>File Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(attachment, i) in jobRequiredAttachments" :key="'att' + i">
                                    <td class="text-center">
                                        <a :href="getAttachmentLink(attachment)" target="_blank" @click="logUrl(attachment)" class="text-decoration-none">
                                            <v-icon size="25px" color="primary">{{getIcon(attachment.orig_filename)}}</v-icon>
                                        </a>
                                    </td>
                                    <td>
                                        <a :href="getAttachmentLink(attachment)" target="_blank" @click="logUrl(attachment)">
                                            {{attachment.orig_filename}}
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-col>
                </v-card-text>
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
import {PDFDocument} from 'pdf-lib'
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
                    url: 'job_request_settings/job_required'
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

            //SECTION - FOR SEARCHING
            searchTerm: '',

            //SECTION - DIALOGS
            insertDialog: false,
            editDialog: false,
            deleteDialog: false,
            uploadDialog: false,
            cancelDialog: false,
            sendDialog:false,
            statusDialog: false,
            deleteItemDialog: false,
            attachmentDialog: false,
            ecdDialog: false,
            editEcdDialog: false,
            isEditing: false,

            //SECTION - ADD & EDIT DIALOG DATA
            tempAddIssuedDate: '',
            tempName: null,
            tempSubject: null,
            tempLot: null,
            tempNote: null,
            tempEcd: null,
            tempAddJobRequirement: [],
            tempStatus: null,
            insertDatepicker: false,
            editData: [],
            attachments: [],
            addAttachments: [],
            tempAddAttachments: [],
            editAttachments: [],
            tempEditAttachments: [],
            tempRemoveFiles: {},
            deletedAttachments: [],
            
            //SECTION - ARRAY DATA FOR UPLOADING
            uploadTab: 0,
            activeRequest: null,
            currentDocument: {},
            requestDetails: {},
            requiredFileName: [],
            requiredDocuments: [],
            uploadHistories: [],
            activeDocument: {},
            CurrentSubject: null,
            progress: false,
            editECD: false,
            editEcdPicker: false,
            invalidFile: [],
            tempRequiredFile: [],
            requiredFile: [],
            sendToHrd: false,
            jobUploadDialog: false,
            updatingReason: null,
            jobRequiredAttachments: [],
            oldUploadData: [],
            currentECD: moment().format('YYYY-MM-DD') ,

            //SECTION - New uploadedFiles dialog
            newUploadedFiles: [],
            disableUploadAndSend: false,
            newUploadsDialog: false,
            dialogTitle: null,

            //SECTION - FOR CANCELLING REQUEST / SENDING EMAIL
            cancellingReason: null,
            toRecipients: [],
            ccRecipients: [],
            projectMembers: [],
            toSearchStr: '',
            ccSearchStr: '',

            //SECTION - CONFIRMATION DIALOG
            confirmDialog: false,
            confirmationFor: 'attachment',
            confirmationText: 'Are you sure you want to send this without an attachment?',

            //SECTION - TRIAL V-MODEL VARIABLE FOR PROJECT LIST DATA
            uniqueProjectLists: [],
            uniqueLots: [],
            tempProjectName: null,
            tempLot2: '',
            uniqueSubject: '',
            project_registered_id: null,

            //SECTION - STATUS ITEM
            statusItem:[
                { value: 'NEW', text: 'NEW', color: 'rgba(231, 76, 60, 1)' }, // Red
                { value: 'ONGOING', text: 'ONGOING', color: 'rgba(52, 152, 219, 1)' }, // Blue
                { value: 'COMPLETED', text: 'COMPLETED', color: 'rgba(46, 204, 113, 1)' }, // Green
                { value: 'CANCELLED', text: 'CANCELLED', color: 'rgba(217, 217, 217, 1)' } // Grey
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
            'JobRequestSort',
            'baseDir',
            'selected',
            'EmailRecipientsData'
        ]),

        ...mapWritableState(useSampleStore,[
            'allSelected',
            'masterUsers',
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

        formatEcdDate(){
            if(!this.tempEcd) return '';
            const date = new Date(this.tempEcd);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        formatChangeEcdDate(){
            if(!this.currentECD) return '';
            const date = new Date(this.currentECD);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },

        hasNewUploads(){
            return this.requiredDocuments.some(obj => obj.newUploads.length > 0)
        },
        
        jobRecipients(){
            return this.EmailRecipientsData.map(email => Number(email.user_id))
        },

        toEmails() {
            console.log('toRecipients:', this.toRecipients);
            console.log('ccRecipients:', this.ccRecipients);
            return this.masterUsers.filter(
                (user) => !this.ccRecipients.some((cc) => cc && cc.id === user.id)
            ).map((user) => ({
                id: user.id,
                username: user.username,
                avatar: user.photo,
            }));
        },

        ccEmails() {
            console.log('toRecipients:', this.toRecipients);
            console.log('ccRecipients:', this.ccRecipients);
            return this.masterUsers.filter(
                (user) => !this.toRecipients.some((to) => to && to.id === user.id)
            ).map((user) => ({
                id: user.id,
                username: user.username,
                avatar: user.photo,
            }));
        },


        // toEmails() {
        //     let items = []
        //     let alreadyListed = this.jobRecipients.concat(this.ccRecipients).concat(this.toRecipients);
        //     console.log('toEmails: ',alreadyListed)

        //     this.masterUsers.forEach(c => {
        //         console.log('c.id: ', c.id, 'included: ', alreadyListed.includes(c.id))
        //         items.push({ id: c.id, username: c.username, avatar: c.photo, disabled: alreadyListed.includes(c.id)})
        //     })
        //     return items
        // },

        // ccEmails(){
        //     let items = []
        //     let alreadyListed = this.toRecipients.concat(this.ccRecipients).concat(this.jobRecipients);
        //     console.log('ccEmails: ',alreadyListed)

        //     this.masterUsers.forEach(c => {
        //         console.log('c.id: ', c.id, 'included: ', alreadyListed.includes(c.id))
        //         items.push({ id: c.id, username: c.username, avatar: c.photo, disabled: alreadyListed.includes(c.id)})
        //     })
        //     return items
        // },

        newUpdates(){
            return _.some(this.requiredDocuments, {changedECD: true}) || this.hasNewUploads
        },

        allSelected: {
            get() {
                return this.selectedRows.length === this.JobRequestData.length;
            },
            set(value) {
                if (value) {
                    this.selectedRows = this.JobRequestData.map((_, index) => index);
                } else {
                    this.selectedRows = [];
                }
            }
        }
    },

    methods:{
        ...mapActions(useSampleStore,[
            'jobRequiredPage',
            'searchColumn',
            'sortColumn',
            'jobRequestPage',
            'setSelected',
            'resetToggleSelectAll',
            'getMasterUsers',
            'emailRecipientPage',
            'getProjects',
            'getProjectLists',
        ]),

        logUrl(attachment) {
            if(attachment.document_id){
                console.log(`${this.baseDir}/job_request/document/${attachment.file_id}/${attachment.orig_filename}`)
            }else{
                console.log(`${this.baseDir}/job_request/attachments/${attachment.id}/${attachment.orig_filename}`);
            }
        },

        dateOnly(dateTime) {
            return dateTime == null ? '' : dateTime.substring(0, 10)
        },

        defaultRecipient(item, list){
            const index = this[list].indexOf(item.id)
            return (index >= 0)
        },

        removeSelection(item, list){
            const index = this[list].indexOf(item.id)
            if (index >= 0) this[list].splice(index, 1)
        },

        statusMapping(status){
            const mapping = {
                'NEW' : { label: 'NEW', color: 'rgba(231, 76, 60, 1)'},
                'ONGOING' : { label: 'ONGOING', color: 'rgba(52, 152, 219, 1)'},
                'COMPLETED' : { label: 'COMPLETED', color: 'rgba(46, 204, 113, 1)'},
                'CANCELLED' : { label: 'CANCELLED', color: 'rgba(217, 217, 217, 1)'},
            }
            return mapping[status] || {label: 'Unknown', color: 'grey'};
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

        toggleEcdDialog(data){
            console.log(data)
            this.editData = data
            this.tempEcd = data.job_ecd
            this.ecdDialog = true
        },

        toggleUploadDialog(data){
            this.requestDetails = data
            this.tempEcd = data.job_ecd
            this.activeRequest = data.id
            this.CurrentSubject = data.subject
            this.requiredDocuments = []
            this.getRequiredDocuments(data.id)
            this.uploadDialog = true
        },

        cancelRequestDialog(){
            this.cancellingReason = ''
            this.cancelDialog = true
        },

        toggleSendDialog(){
            this.toRecipients = this.jobRecipients
            this.ccRecipients = this.projectMembers
            this.sendDialog = true
        },

        toggleAddDialog(){
            this.insertDialog = true
        },

        saveForm(){
            this.tempName = null
            this.tempProjectName = null
            this.tempLot2 = null
            this.uniqueSubject = null
            this.tempLot = null
            this.tempSubject = null
            this.tempAddIssuedDate = null
            this.tempNote = null
            this.tempAddJobRequirement = []

            if(this.$refs.Insert){
                this.$refs.Insert.resetValidation();
            }
        },

        toggleDeleteDialog(){
            this.deleteDialog = true
        },

        showConfirmDialog(){
            this.confirmationText = 'Are you sure you want to close without saving?'
            this.confirmationFor = 'uploadDialog'
            this.confirmDialog = true
        },

        closeUploadDialog() {
            this.confirmDialog = false
            this.uploadDialog = false
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

        toggleJobAttachments(reqId, docId, uploadId = null){
            console.log('function is working')
            axios({
                method: 'post',
                url: '/api/jobRequest/get_uploaded_requirements',
                data:{
                    request_id: reqId,
                    document_id: docId,
                    upload_id: uploadId
                },
            }).then((res) =>{
                this.jobRequiredAttachments = res.data
            }).catch(error =>{
                console.log(error)
            })

            this.jobUploadDialog = true
        },

        showUploadTab(doc){
            this.currentDocument = doc
            this.requiredFileName = `${this.CurrentSubject}-${doc.filling_mark}`
            this.uploadTab = 1
        },

        showHistoryTab(doc){
            console.log('history dialog tab show')
            this.uploadTab = 2

            axios({
                method: 'post',
                url: '/api/jobRequest/upload_history',
                data:{
                    request_id: doc.job_request_id,
                    document_id: doc.document_id
                }
            }).then((res) =>{
                this.uploadHistories = res.data
            }).catch(error =>{
                console.log(error)
            })
        },

        EditECD(doc){
            if (doc.estimated_completion_date != null){
                this.currentECD = doc.estimated_completion_date
            }

            console.log(doc)
            this.activeDocument = doc
            this.editEcdDialog = true
        },

        saveNewECD(){
            const index = this.requiredDocuments.findIndex(obj => obj.document_id === this.activeDocument.document_id)
            this.requiredDocuments[index].estimated_completion_date = this.formatChangeEcdDate
            // this.currentECD = this.formatChangeEcdDate
            this.editEcdDialog = false
        },

        setECD() {
            this.requiredDocuments
                .forEach(obj => {
                    if (obj.estimated_completion_date == null) {
                        obj.estimated_completion_date = moment().add(obj.estimated_duration, 'days').format('YYYY-MM-DD')
                    }
                })
        },

        processUploadForm(){
            console.log('working')
            const docIndex = this.requiredDocuments.findIndex(obj => obj.document_id === this.currentDocument.document_id)
            this.requiredDocuments[docIndex].newUploads = this.requiredFile
            this.requiredDocuments[docIndex].newUploadReasons = this.updatingReason
            this.requiredFile = []
            this.uploadTab = 0
        },

        processSending(){
            if(this.addAttachments.length > 0 || this.uploadDialog || this.cancelRequestDialog){
                this.send()
            }else{
                this.confirmationText = 'Are you sure you want to send this without attachment?'
                this.confirmationFor = 'attachment'
                this.confirmDialog = true
            }
        },

        getAttachmentLink(attachment){
            if(attachment.document_id){
                const rawFileName = ``
                return `/job_requests/document/${attachment.file_id}/${encodeURIComponent(this.extractFileName(attachment.orig_filename))}`
            }else{
                return `/job_requests/attachments/${attachment.id}/${encodeURIComponent(this.extractFileName(attachment.orig_filename))}`
            }
        },

        documentLink(constructionCode, planNo,fillingMark, requestId, documentId, latestUploadReferences = {}){
            // const arr = planNo.split('-')
            // const planNumber = arr.map(this.pad2).join('')
            let reference = `${requestId}-${documentId}`

            return `/job_requests/document/${reference}/${constructionCode}-${planNo}-${fillingMark}`
        },

        async Edit(data){
            console.log('Edit data: ', data)
            console.log('Existing Lot Number from selected Index: ', data.lot)
            this.editDialog = false

            this.editData = data
            this.isEditing = true
            this.isInitializingEdit = true

            this.tempLot2 = data.lot_number
            this.uniqueSubject = data.subject
            this.tempStatus = data.status
            this.tempAddIssuedDate = data.requested_date
            this.tempNote = data.note
            this.tempAddJobRequirement = data.requirements
            this.tempProjectName = data.project_name;

            // await this.getLotProjectList(data.project_name);

            if (data.project_name){
                try{
                    await this.getLotProjectList(data.project_name)
                    console.log('UNIQUE LOTS: ', this.uniqueLots)
                    if (data.lot_number && !this.uniqueLots.find(lot => lot.lot === data.lot_number)){
                        console.log('Lot not found, using data subject: ')
                        this.uniqueSubject = data.subject || data.lot_number || '';
                    }
                } catch(error){
                    console.error('failed to fetch lot project_lists', error)
                    this.uniqueSubject = data.subject || data.lot_number || ''
                }
            }

            // if (data.lot_number) {
            //     console.log('Reassigning tempLot2 with uniqueLots:', this.uniqueLots);
            //     this.tempLot2 = data.lot_number;
            //     // Fallback to data.subject if lot not found in uniqueLots
            //     if (!this.uniqueLots.find(lot => lot.lot === data.lot_number)) {
            //         console.log('Lot not found in uniqueLots, using data.subject');
            //         this.uniqueSubject = data.subject || '';
            //     }
            // }

            // if (data.lot_number) {
            //     this.tempLot2 = data.lot_number; // Trigger tempLot2 setter again
            // }
            
            this.oldJobRequirement = [...data.requirements]
            if (data.attachments.length > 0) {
                _.forEach(data.attachments, (item) => {
                    this.editAttachments.push({
                        id: item.id,
                        filename: item.orig_filename,
                        url: `${this.baseDir}/job_request/attachment/${item.id}/${encodeURIComponent(item.original_filename)}`
                    })
                })
            } else {
                this.editAttachments = []
            }


            this.isInitializingEdit = false
            this.editDialog = true
        },
        },

        Insert(){
            if(this.$refs.Insert.validate()){
                var myform = document.getElementById('Insert')
                var formdata = new FormData(myform)

                formdata.set('documents', JSON.stringify(this.tempAddJobRequirement))

                for(var i = 0; i < this.addAttachments.length; i++ ) {
                    formdata.append("attachments[]",JSON.stringify(this.addAttachments[i]))
                }
            }
            axios({
                method: 'post',
                url: '/api/jobRequest/job_insert',
                data: formdata
            })
            .then((res) =>{
                this.snackbar.show = true
                this.snackbar.text = 'Insert Successful'
                this.snackbar.color = 'blue-grey'
                this.insertDialog = false
                this.jobRequestPage()
            }).catch((res) =>{
                console.log(res)
            })
        },

        Update(){
            if(this.$refs.Update.validate()){
                var myform = document.getElementById('Update')
                var formdata = new FormData(myform)

                const addJobRequirement =   _.difference(this.tempAddJobRequirement, this.oldJobRequirement)
                const removeJobRequirement = _.difference(this.oldJobRequirement, this.tempAddJobRequirement)

                formdata.set("addJobRequirement", JSON.stringify(addJobRequirement));
                formdata.set("removeJobRequirement", JSON.stringify(removeJobRequirement));

                axios({
                    method: 'post',
                    url: '/api/jobRequest/job_update',
                    data: formdata
                })
                .then((res) =>{
                    this.snackbar.show = true
                    this.snackbar.text = 'Update Successful'
                    this.snackbar.color = 'blue-grey'
                    this.editDialog = false
                    this.jobRequestPage()
                }).catch((res) =>{
                    console.log(res)
                })
            }
        },

        Delete(){
            console.log('delete is working')
            axios({
                method: 'post',
                url: '/api/jobRequest/job_delete',
                data:{
                    id: this.selectedRows.map(index => this.JobRequestData[index].id)
                }
            })
            .then((res) =>{
                this.snackbar.show = true
                this.snackbar.text = 'Delete Successful'
                this.snackbar.color = 'blue-grey'
                this.jobRequestPage()
                this.deleteDialog = false
                this.resetToggleSelectAll()
            })
        },

        getIcon(filename) {
            if (filename) {
                const ext = _.last(_.split(filename.toLowerCase(), '.'))
                const image = [
                    'jpg',
                    'jpeg',
                    'png'
                ]
                // ternary if..else if..
                return ext === 'pdf' ? 'mdi-file-pdf-box'
                    : _.includes(image, ext) ? 'mdi-file-image'
                    : (ext === 'xls' || ext === 'xlsx') ? 'mdi-file-excel'
                    : (ext === 'dwg' || ext === 'dxf') ? 'mdi-file-cad'
                    : 'mdi-file'
            }
        },

        multiDropFile(temp, model, files) {
            const self = this
            _.forEach(files, function(file) {
                globalThis[temp].push(file)
            })
            self.multiChangeFile(temp, model)
        },
        
        multiChangeFile(temp, model) {
            const self = this
            if (self[temp].length > 0) {
                _.forEach(self[temp], function(file) {
                    self.multiFileChecker(file, model, temp)
                })
            } else {
                self[model] = []
            }
            self[temp] = []
        },

        multiFileChecker(file, model, temp) {
            const self = this
            // self.multiFileReader(file, model, file.name, file.type)

            if (model == 'addAttachments'){
                this.multiFileReader(file, model, file.name, file.type)
            } else if(model == 'editAttachments'){
                this.multiFileReader(file, model, file.name, file.type)
            }else{
                const requiredFilenameArr = this.requiredFileName.split('-')
                const filenameArr = file.name.split('.')
                const filename = filenameArr.shift()
    
                const validFileName = filename.includes(this.CurrentSubject) && filename.includes(requiredFilenameArr[requiredFilenameArr.length -1])
    
                if (temp == 'tempRequiredFile' && !validFileName) {
                    this.invalidFile.push({filename: file.name})
                } else {
                    this.multiFileReader(file, model, file.name, file.type)
                }
            }``

        },
        
        async multiFileReader(file, model, filename, filetype) {
            const self = this
            const fileUrl = URL.createObjectURL(file)

            if (filetype === 'application/pdf') {
                let pdfBytes = await fetch(fileUrl).then((res) => res.arrayBuffer());
                const pdfDoc = await PDFDocument.load(pdfBytes, { 
                    updateMetadata: true
                })
                
                pdfDoc.setTitle(' ')
                pdfDoc.setAuthor(' ')
                pdfDoc.setSubject(' ')
                pdfDoc.setKeywords([' '])
                pdfDoc.setCreator(' ')
                pdfDoc.setProducer(' ')

                pdfBytes = await pdfDoc.saveAsBase64({ dataUri: true })
                self[model].push({
                    data: pdfBytes,
                    url: fileUrl,
                    filename: self.spaceToUnderscore(filename)
                });
            } else {
                const reader = new FileReader()

                reader.readAsDataURL(file)
                reader.onloadend = function() {
                    self[model].push({
                        data: reader.result,
                        url: fileUrl,
                        filename: self.spaceToUnderscore(filename)
                    });
                }
            }
        },

        spaceToUnderscore(str) {
            return str.replace(/ /g,'_')
        },
    }
}
</script>

<style scoped>


</style>
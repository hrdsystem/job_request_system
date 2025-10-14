<template>
    <v-container class="container-main pt-0">
    <tool-bar-component :toolbarData="toolbarData"></tool-bar-component>
        <v-table fixed-header :height="tableHeight" class="mainTable">
            <thead>
                <tr>
                    <th v-show="floatButtonData.editButtonActive" class="text-left" style="width:20px">
                        <v-checkbox style="display: flex;" color="white" v-model="allSelected"></v-checkbox>
                    </th>
                    <th v-show="floatButtonData.editButtonActive" class="text-left" style="width:20px">Edit</th>
                    <th v-show="floatButtonData.editButtonActive" class="text-left Sortable" style="width:20px">
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
                <tr v-show="filterMode">
                    <th v-show="floatButtonData.editButtonActive"></th>
                    <th v-show="floatButtonData.editButtonActive"></th>
                    <th v-show="floatButtonData.editButtonActive"></th>
                    <th>
                        <v-text-field variant="solo" placeholder="Search" @keyup="searchCol($event.target, 'job_requireds.required_name')"></v-text-field>
                    </th>
                    <th>
                        <v-text-field variant="solo" placeholder="Search" @keyup="searchCol($event.target, 'job_requireds.filling_mark')"></v-text-field>
                    </th>
                    <th>
                        <v-text-field variant="solo" placeholder="Search" @keyup="searchCol($event.target, 'job_requireds.header_name')"></v-text-field>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template v-for="(item, index) in JobRequestRequiredData" :key="index">
                    <tr @click="setDrawerSubData({ data: item, index: index})" :class="{ 'tr-active': drawerSubDataActive == index}">
                        <td v-show="floatButtonData.editButtonActive">
                            <v-checkbox 
                                style="display: flex;" 
                                color="indigo" 
                                v-model="selectedRows"
                                :value="index">
                            </v-checkbox>
                        </td>
                        <td v-show="floatButtonData.editButtonActive">
                            <template v-if="item.disabled">
                                <v-btn icon="mdi-folder"></v-btn>
                            </template>
                            <template v-else>
                                <v-btn icon="mdi-pencil" flat size="30px" color="success" @click="Edit(item)"></v-btn>
                            </template>
                        </td>
                        <td v-show="floatButtonData.editButtonActive">{{item.seq}}</td>
                        <td class="pa-0px" style="padding : 0px 5px">
                            <template v-if="item.sub_docs.length !== 0">
                                <v-icon v-if="!expanded.includes(item.id)" @click="pushToExpanded(item.id)">mdi-menu-right</v-icon>                                
                                <v-icon v-else @click="removeInExpand(item.id)" >mdi-menu-down</v-icon>                                
                            </template>
                            <span class="text-uppercase">{{ item.required_name }}</span>
                        </td>
                        <td class="text-uppercase">{{ item.filling_mark }}</td>
                        <td class="text-uppercase">{{ item.header_name }}</td>
                    </tr>
                    <template v-for="(subDoc, subIndex) in item.sub_docs">
                        <tr :key="`sub-doc-${index}-${subIndex}`" v-if="expanded.includes(item.id)">
                            <td v-if="floatButtonData.editButtonActive"></td>
                            <td v-if="floatButtonData.editButtonActive"></td>
                            <td v-if="floatButtonData.editButtonActive"></td>
                            <td class="text-center text-uppercase"><v-icon size="15">mdi-subdirectory-arrow-right</v-icon>{{ subDoc.required_name }}</td>
                            <td class="text-center text-uppercase">{{ subDoc.filling_mark }}</td>
                            <td class="text-center text-uppercase">{{ subDoc.header_name}}</td>
                        </tr>
                    </template>
                </template>
                <tr class="records">
                    <td colspan="2">Showing {{ JobRequestRequiredData.length }} of {{ JobRequestRequiredRecords }}</td>
                </tr>
            </tbody>
        </v-table>

        <v-overlay v-model="overlay" overlay class="overlay-center">
            <v-progress-circular 
                indeterminate 
                :size="64"
                style="color: white;"
            ></v-progress-circular>
        </v-overlay>

        <v-dialog v-model="insertDialog" persistent max-width="300" @keydown.esc="insertDialog = false">
            <v-form id="Insert" ref="Insert" @submit.prevent="Insert">
                <v-card>
                    <v-card-title>
                        <span class="headline">Add Required Job</span>
                        <v-icon style="float: right;" color="white" @click="insertDialog = false">mdi-close</v-icon>
                    </v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="12" sm="12" md="12">
                                <v-autocomplete
                                    v-model="last_seq"
                                    :items="seqs"
                                    :rules="rules.required"
                                    class="required"
                                    label="No ."
                                    name="seq"
                                    persistent-placeholder
                                    dense
                                    required
                                    outlined
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>No .</span>
                                </template>
                                </v-autocomplete>
                            </v-col>
                            <v-col cols="12" sm="12" md="12">
                                <v-text-field
                                    v-model="tempName"
                                    label="Required Name"
                                    name="required_name"
                                    class="required uppercase-value"
                                    @keyup="tempName = $event.target.value.toUpperCase()"
                                    :rules="rules.required"
                                    dense
                                    outlined
                                    autocomplete="off"
                                    hide-details
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>Required Name</span>
                                </template>
                                </v-text-field>
                            </v-col>
                            <v-col cols="12" sm="12" md="12">
                                <v-text-field
                                    v-model="tempFillName"
                                    label="Filling Mark"
                                    name="filling_mark"
                                    class="required uppercase-value"
                                    @keyup="tempFillName = $event.target.value.toUpperCase()"
                                    :rules="rules.required"
                                    dense
                                    outlined
                                    autocomplete="off"
                                    hide-details
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>Filling Mark</span>
                                </template>
                                </v-text-field>
                            </v-col>
                            <v-col cols="12" sm="12" md="12">
                                <v-text-field
                                    v-model="tempHeaderName"
                                    label="Header Name"
                                    name="header_name"
                                    class="required uppercase-value"
                                    @keyup="tempHeaderName = $event.target.value.toUpperCase()"
                                    :rules="rules.required"
                                    dense
                                    outlined
                                    autocomplete="off"
                                    
                                >
                                <template v-slot:label>
                                    <span><span style="color: red">*</span>Header Name</span>
                                </template>
                                </v-text-field>
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-btn type="submit" color="blue-grey darken-3">Submit</v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>

        <v-dialog v-model="editDialog" persistent max-width="500" @keydown.esc="editDialog = false">
            <v-form id="Update" ref="Update" @submit.prevent="Update">
                <v-card>
                    <v-card-title>
                        <span class="headline">{{ editData.required_name }}</span>
                        <v-icon style="float: right;" color="white" @click="editDialog = false">mdi-close</v-icon>
                    </v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="6" sm="6" md="6">
                                <v-autocomplete
                                    v-model="editData.seq"
                                    :items="seqs"
                                    :rules="rules.required"
                                    class="required"
                                    label="No ."
                                    name="seq"
                                    persistent-placeholder
                                    hide-details
                                    dense
                                    required
                                    outlined
                                ></v-autocomplete>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="tempName"
                                    label="Required Name"
                                    name="required_name"
                                    class="required uppercase-value"
                                    @keyup="tempName = $event.target.value.toUpperCase()"
                                    :rules="rules.required"
                                    dense
                                    outlined
                                    autocomplete="off"
                                    hide-details
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="tempFillName"
                                    label="Filling Mark"
                                    name="filling_mark"
                                    class="required uppercase-value"
                                    @keyup="tempFillName = $event.target.value.toUpperCase()"
                                    :rules="rules.required"
                                    dense
                                    outlined
                                    autocomplete="off"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="6" sm="6" md="6">
                                <v-text-field
                                    v-model="tempHeaderName"
                                    label="Header Name"
                                    name="header_name"
                                    class="required uppercase-value"
                                    @keyup="tempHeaderName = $event.target.value.toUpperCase()"
                                    :rules="rules.required"
                                    dense
                                    outlined
                                    autocomplete="off"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                        <br></br>
                        <span>Sub Documents</span>
                        <v-btn @click="addNewItem" x-small color="white" class="float-right">Add New Item</v-btn>
                        <v-table class="mainTable">
                            <thead>
                                <tr>
                                    <th class="text-center"></th>
                                    <th class="text-center">
                                        <span>Required Name</span>
                                    </th>
                                    <th class="text-center">
                                        <span>Filling Mark</span>
                                    </th>
                                    <th class="text-center">
                                        <span>Header Name</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </v-table>
                        <template v-for="(item, index) in tempSubDocumentItems" :key="index">
                            <v-row class="row-impact">
                                <v-col cols="1" sm="1" md="1">
                                    <v-icon style="padding-left: 10px;" color="error" class="mt-6" @click="removeItemAtIndex(index)">mdi-delete</v-icon>
                                </v-col>
                                <v-col cols="4" sm="4" md="4">
                                    <v-text-field
                                        v-model="item.required_name"
                                        label="Require Name"
                                        persistent-placeholder
                                        hide-details
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="3" sm="3" md="3">
                                    <v-text-field
                                        v-model="item.filling_mark"
                                        hide-details
                                        label="Filling Mark"
                                        persistent-placeholder
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="4" sm="4" md="4">
                                    <v-text-field
                                        v-model="item.header_name"
                                        hide-details
                                        label="Header Name"
                                        persistent-placeholder
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                        </template>
                        <input type="hidden" name="id" :value="editData.id">
                        <input type="hidden" name="old_seq" :value="editData.seq">
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn type="submit" text color="blue-grey darken-3">Submit</v-btn>
                    </v-card-actions>
                </v-card>
            </v-form>
        </v-dialog>

        <v-dialog v-model="deleteDialog" persistent max-width="300" @keydown.esc="deleteDialog = false">
            <v-card>
                <v-card-title>
                    <span class="headline">Delete this data?</span>
                    <v-icon style="float: right;" color="white" @click="deleteDialog = false">mdi-close</v-icon>
                </v-card-title>
                <v-card-actions>
                <v-spacer></v-spacer>
                    <v-btn
                    color="blue-grey darken-3"
                    text
                    @click="Delete"
                    >Agree</v-btn>
                    <v-btn
                        color="blue-grey darken-3"
                        text
                        @click="deleteDialog = false"
                    >Disagree</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="deleteConfirmationDialog" persistent max-width="200">
            <v-card>
                <v-card-title>
                    <span class="headline">Confirm Removal</span>
                    <v-spacer></v-spacer>
                </v-card-title>
                <v-card-actions>
                <v-spacer></v-spacer>
                    <v-btn
                        color="blue-grey darken-3"
                        text
                        @click="confirmRemoveItem"
                    >Agree</v-btn>
                    <v-btn
                        color="blue-grey darken-3"
                        text
                        @click="deleteConfirmationDialog = false"
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
import FloatButtonComponent from '@aspect/FloatButtonComponent.vue'
import SnackBarComponent from '@aspect/SnackBarComponent.vue'
</script>

<script>
import {mapState,mapActions, mapWritableState} from 'pinia'
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
                    url: '/iconnsystem/job_request'
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

            insertDialog: false,
            editDialog: false,
            deleteDialog: false,
            deleteConfirmationDialog: false,

            tempName: null,
            tempHeaderName: null,
            tempFillName: null,
            editData: [],
            tempSubDocumentItems: [],
            expanded:[],
            deletedItemIds: []
        }
    },

    computed:{
        ...mapState(useSampleStore,[
            'editMode',
            'filterMode',
            'viewMode',
            'tableHeight',
            'rules',
            'overlay',
            'JobRequestRequiredData',
            'JobRequestRequiredRecords',
            'JobRequestRequiredSearch',
            'JobRequestRequiredSort',
            'drawerSubData',
            'drawerSubDataActive',
        ]),

        ...mapWritableState(useSampleStore,[
            'selectedRows'
        ]),

        seqs(){
            return (this.insertDialog) ? _.reverse(_.range(1,this.JobRequestRequiredRecords + 2)) : _.reverse(_.range(1,this.JobRequestRequiredRecords + 1))
        },
        last_seq(){
            return this.JobRequestRequiredRecords + 1
        },

        allSelected: {
            get() {
                // This checks if the length of the selectedRows array
                // is equal to the total number of items.
                return this.selectedRows.length === this.JobRequestRequiredData.length;
            },
            set(value) {
                // This is the setter that runs when the select all checkbox is clicked.
                if (value) {
                    // Select all rows
                    this.selectedRows = this.JobRequestRequiredData.map((_, index) => index);
                } else {
                    // Deselect all rows
                    this.selectedRows = [];
                }
            }
        }
    },

    methods: {
        ...mapActions(useSampleStore,[
            'jobRequiredPage',
            'toggleFilterMode',
            'toggleViewMode',
            'setDrawerSubData',
            'setSelected',
            'searchColumn',
            'sortColumn',
            'resetToggleSelectAll'
        ]),

        toggleAddDialog(){
            this.insertDialog = true
        },

        toggleEditDialog(){
            this.editDialog = true
        },

        toggleDeleteDialog(){
            this.deleteDialog = true
        },

        searchCol(e, column){
            this.searchColumn({
                selector: e,
                column: column,
                page: 'JobRequestRequiredData',
                search: 'JobRequestRequiredSearch'
            })
        },

        sortCol(e, column){
            this.sortColumn({
                selector: e,
                column: column,
                page: 'JobRequestRequiredData',
                sort: 'JobRequestRequiredSort'
            })
            this.jobRequiredPage()
        },

        Edit(data){
            console.log(data)
            this.editData = data
            this.editDialog = true
            this.tempName = data.required_name
            this.tempFillName = data.filling_mark
            this.tempHeaderName = data.header_name
            this.tempSubDocumentItems = data.sub_docs
        },

        toggleSelectAll(){
            this.allSelected =  !this.allSelected
            if(this.allSelected){
                this.selectedRows = this.JobRequestRequiredData.map((_, index) => index)
                console.log('Select All', this.selectedRows)
            } else{
                this.selectedRows = []
                console.log('DESELECT', this.selectedRows)
            }
        },

        removeItemAtIndex(index){
            this.deleteConfirmationDialog = true
            this.itemToRemoveIndex = index
        },

        pushToExpanded(name){
            this.expanded.push(name)
        },

        removeInExpand(num){
            let b = this.expanded.indexOf(num)
            this.expanded.splice(b,1)
        },

        confirmRemoveItem(){
            const deleteItemId = this.tempSubDocumentItems[this.itemToRemoveIndex].id;
            this.deletedItemIds.push(deleteItemId)
            this.tempSubDocumentItems.splice(this.itemToRemoveIndex, 1)
            this.deleteConfirmationDialog = false
        },

        addNewItem(){
            this.tempSubDocumentItems.push({
                id: null,
                required_name: '',
                filling_mark: '',
                header_name: '',
            })
        },

        async Insert(){
            const validation = await this.$refs.Insert.validate();

            if(!validation.valid){
                this.snackbar.show = true
                this.snackbar.text = 'Please fill all required fields!'
                this.snackbar.color = 'red darken 2'
                return;
            }
            const myform = document.getElementById('Insert')
            const formdata = new FormData(myform)
            formdata.append('seq', this.last_seq)

            try{
                const res = await axios({
                    method: 'post',
                    url: '/api/jobMaster/insert_job_required',
                    data: formdata,
                });

                if(res.data === 1){
                    console.log('required name already exists')
                    this.snackbar.show = true
                    this.snackbar.text = 'Required name already exists'
                    this.snackbar.color = 'red darken 2'
                } else if (res.data === 2){
                    console.log('filling mark already exists')
                    this.snackbar.show = true
                    this.snackbar.text = 'Filling mark already exists'
                    this.snackbar.color = 'red darken 2'
                } else if (res.data === 3){
                    console.log('Header name already exists')
                    this.snackbar.show = true
                    this.snackbar.text = 'Header name already exists'
                    this.snackbar.color = 'red darken 2'
                } else{
                    this.snackbar.show = true
                    this.snackbar.text = 'Insert Successful'
                    this.snackbar.color = 'blue-grey'
                    this.insertDialog = false
                    this.jobRequiredPage()
                }
            }catch(error){
                console.log(error)
                this.snackbar.show = true
                this.snackbar.text = 'Error submitting the form please try again'
                this.snackbar.color = 'red darken 2'
            }
        },

        async Update(){
            const  validation = await this.$refs.Update.validate()


            if(!validation.valid){
                this.snackbar.show = true
                this.snackbar.text = 'Please fill all required field'
                this.snackbar.color = 'red darken 2'
                return
            }
            var myform = document.getElementById('Update');
            var formdata = new FormData(myform);
            
            formdata.append('seq', this.editData.seq)
            formdata.set('sub_docs', (JSON.stringify(this.tempSubDocumentItems)))
            formdata.set('deleted_items', (JSON.stringify(this.deletedItemIds)))
            
            try{
                const res = await axios({
                    method: 'post',
                    url: '/api/jobMaster/update_job_required',
                    data: formdata
                });

                if(res.data === 1){
                    console.log('required name already exists')
                    this.snackbar.show = true
                    this.snackbar.text = 'Required name already exists'
                    this.snackbar.color = 'red darken 2'
                } else if (res.data === 2){
                    console.log('filling mark already exists')
                    this.snackbar.show = true
                    this.snackbar.text = 'Filling mark already exists'
                    this.snackbar.color = 'red darken 2'
                } else if (res.data === 3){
                    console.log('Header name already exists')
                    this.snackbar.show = true
                    this.snackbar.text = 'Header name already exists'
                    this.snackbar.color = 'red darken 2'
                } else{
                    this.snackbar.show = true
                    this.snackbar.text = 'Insert Successful'
                    this.snackbar.color = 'blue-grey'
                    this.editDialog = false
                    this.jobRequiredPage()
                }
            }catch(error){
                console.log(error)
                this.snackbar.show = true
                this.snackbar.text = 'Error submitting the form please try again'
                this.snackbar.color = 'red darken 2'
            }
        },

        Delete(){
            console.log('delete is working')
            this.overlay = true
            axios({
                method: 'post',
                url: '/api/jobMaster/delete_job_required',
                data: {
                    id: this.selectedRows.map(index => this.JobRequestRequiredData[index].id)
                }
            })
            .then((res) =>{
                this.snackbar.show = true
                this.snackbar.text = 'Delete Successful'
                this.snackbar.color = 'blue-grey'
                this.deleteDialog = false
                this.overlay = false
                this.jobRequiredPage()
                this.resetToggleSelectAll()
            })
        },

        saveForm(){
            this.tempName = null
            this.tempFillName = null
            this.tempHeaderName = null

            if(this.$refs.Insert){
                this.$refs.Insert.resetValidation();
            }
        },
    },

    mounted(){
        this.jobRequiredPage();
    },

    watch:{
        insertDialog(val){  
            if(!val){
                this.saveForm()
            }
        },

        editDialog(val){
            if(!val){
                this.$refs.Update.resetValidation()
                this.saveForm()
            }
        },
    }
}  
</script>

<style scoped>
.overlay-center {
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center;    
}

.v-table th,.v-table td{
    white-space: nowrap !important;
    font-size: 12px !important;
    height: 40px !important;
}

.container-main{
    max-width: 100% !important;
    padding: 0px !important;
    padding-left: 14px !important;
    overflow: hidden;
}

.v-table :deep(.v-text-field input){
    background-color: white;
    min-height: 30px;
    max-height: 30px;
    color: black;
    border-radius: 4px;
    font-size: 13px;
    margin-bottom: -15px;
}

.text-ellipsis{
    white-space: nowrap;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.v-table.mainTable th{
background: #34495e !important;
color:white !important;
}

.v-table .v-checkbox :deep(.v-selection-control){
    min-height: 0px !important;
}

.v-table .v-checkbox :deep(.v-selection-control--density-default){
    --v-selection-control-size: 24px;
}

.v-autocomplete :deep(.v-field .v-field__input){
    min-height: 40px;
    max-height: 40px;
    overflow: hidden;
    text-overflow: clip;
}

.v-dialog :deep(.v-text-field input){
    min-height: 40px;
    max-height: 40px;
}

.v-dialog :deep(.v-row [class*="col"]){
    padding: 0px 8px !important;
}

.v-dialog :deep(.v-row:not([class*="mt-"])){
    margin-top: 5px !important;
}

.v-dialog .v-card-title {
    cursor: grab !important;
}

.v-dialog .v-card-title:active {
    cursor: grabbing !important;
}

.v-dialog > .v-overlay__content > .v-card > .v-card-text, .v-dialog > .v-overlay__content > form > .v-card > .v-card-text {
    padding: 0px 16px 16px 
}

.float-right{
    background-color: #53B257;
}

tbody tr:hover{
    cursor: pointer;
}

tr.records{
    vertical-align: top;
}

.text-field-small {
    font-size: 12px;
    padding: 4px;
    margin: 2px;
}

.row-compact {
    margin-bottom: 4px;
}
</style>
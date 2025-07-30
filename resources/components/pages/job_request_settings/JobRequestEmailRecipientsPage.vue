<template>
    <v-container class="container-main pt-0">
    <tool-bar-component :toolbarData="toolbarData"></tool-bar-component>
        <v-table fixed-header :height="tableHeight" class="mainTable">
            <thead>
                <tr>
                    <th v-show="floatButtonData.editButtonActive" class="text-left" style="width:20px">
                        <v-checkbox style="display: flex;" color="white" :input-value="allSelected" @change="toggleSelectAll()" ></v-checkbox>
                    </th>
                    <th>RECIPIENTS</th>
                </tr>
                <tr v-show="filterMode">
                    <th>
                        <v-text-field variant="solo" placeholder="Search"></v-text-field>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in EmailRecipientsData" :key="index">
                    <td v-show="floatButtonData.editButtonActive" class="text-left" style="width: 20px">
                        <v-checkbox
                            style="display: flex;"
                            color="indigo"
                            :model-value="allSelected"
                            :input-value="allSelected"
                            @change="setSelected(index)"
                        ></v-checkbox>
                    </td>
                    <td>{{ item.username }}</td>
                    <td class="text">𝔱𝔢𝔰𝔱𝔦𝔫𝔤 𝔡𝔞𝔱𝔞</td>
                </tr>
            </tbody>
        </v-table>

        <v-dialog v-model="insertDialog" persistent width="300" @keydown.esc="insertDialog = false">
            <v-form id="Insert" ref="Insert" @submit.prevent="Insert">
                <v-card>
                    <v-card-title style="background-color: #455A64;" class="d-flex align-center">
                        <span class="headline" style="color: white;" >Add Email Recipient</span>
                        <v-spacer></v-spacer>
                        <v-icon color="white" @click="insertDialog = false">mdi-close</v-icon>
                    </v-card-title>
                    <v-card-text>
                        <v-autocomplete
                            v-model="tempName"
                            :items="masterUsers"
                            item-title="username"
                            item-value="id"
                            name="user_id"
                            variant="outlined"
                            autocomplete="off"
                            :rules="rules.required"
                        >
                            <template v-slot:label>
                                <span><span style="color: red">*</span> Recipient</span>
                            </template>
                        </v-autocomplete>
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-btn type="submit" style="border: 1px solid grey; background-color: #227093;" color="white">Submit</v-btn>
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
                    <v-tooltip location="bottom">
                        <template v-slot:activator="{ props }">
                            <v-btn style="border: 1px solid grey; background-color: #e74c3c;" color="white" @click="Delete" v-bind="props">
                                <v-icon>mdi-delete-alert</v-icon>Delete
                            </v-btn>
                        </template>
                        <span>Delete</span>
                    </v-tooltip>
                    <v-tooltip location="bottom">
                        <template v-slot:activator="{ props }">
                            <v-btn style="border: 1px solid grey; background-color: #227093;" color="white" @click="deleteDialog = false" v-bind="props">
                                <v-icon>mdi-close-thick</v-icon>Disagree
                            </v-btn>
                        </template>
                        <span>Disagree</span>
                    </v-tooltip>
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
</script>

<script>
import {mapState,mapActions, mapWritableState} from 'pinia'
import {useSampleStore} from '../../../js/store'
export default {
    data(){
        return{
            toolbarData:{
                title: "EMAIL RECIPIENTS",
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

            //SECTION - DIALOGS
            insertDialog: false,
            deleteDialog: false,

            //SECTION - Insert data
            tempName: null,

            //SECTION - CSS data properties
            fontSizes: '55px'
        }
    },

    computed:{
        ...mapState(useSampleStore,[
            'masterUsers',
            'tableHeight',
            'filterMode',
            'viewMode',
            'EmailRecipientsData',
            'rules'
        ]),

        ...mapWritableState(useSampleStore,[
            'allSelected',
            'selectedRows'
        ]),
    },

    methods:{
        ...mapActions(useSampleStore,[
            'getMasterUsers',
            'toggleFilterMode',
            'searchColumn',
            'sortColumn',
            'emailRecipientPage',
            'setSelected',
            'resetToggleSelectAll'
        ]),

        toggleAddDialog(){
            this.insertDialog = true
        },

        toggleDeleteDialog(){
            this.deleteDialog = true
        },

        toggleSelectAll(){
            this.allSelected =  !this.allSelected
            if(this.allSelected){
                this.selectedRows = this.EmailRecipientsData.map((_, index) => index)
                console.log('Select All', this.selectedRows)
            } else{
                this.selectedRows = []
                console.log('DESELECT', this.selectedRows)
            }
        },

        Insert(){
            console.log('testing')

            if(this.$refs.Insert.validate()){
                var myform = document.getElementById('Insert')
                var formdata = new FormData(myform)
            }

            formdata.append('user_id', this.tempName)

            axios({
                method: 'post',
                url: '/api/jobMaster/insert_recipients',
                data: formdata
            })
            .then((res) =>{
                if(res.data == 1){
                    console.log('already existing')
                    this.snackbar.show = true
                    this.snackbar.text = 'Email Recipient Already Exists'
                    this.snackbar.color = 'red darken-2'
                } else{
                    console.log('insert successful')
                    this.snackbar.show = true
                    this.snackbar.text = 'Insert Successful'
                    this.snackbar.color = 'blue-grey'
                    this.emailRecipientPage()
                    this.insertDialog = false
                }
            })
        },

}
</script>

<style scoped>
    
</style>
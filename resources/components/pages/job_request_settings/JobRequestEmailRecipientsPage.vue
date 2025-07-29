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
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
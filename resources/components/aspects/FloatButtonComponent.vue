<template>
    <div style="position: fixed; bottom: 2vh; right: 2vh; z-index: 3; ">
        <!-- *ADD BUTTON -->
        <template v-if="floatButtonData?.addButton && !floatButtonData?.editButtonActive">
            <v-tooltip location="top">
                <template v-slot:activator="{ props }">
                    <v-btn
                        v-bind="props"
                        class="ma-1"
                        size="large"
                        color="success"
                        icon="mdi-plus"
                        @click="addButtonHandle()"
                    </v-btn>
                </template>
                <span>Add</span>
            </v-tooltip>
        </template>

        <!-- *DELETE BUTTON -->
        <template v-if="floatButtonData?.editButtonActive && floatButtonData?.deleteButton && selectedRows.length > 0">
            <v-tooltip location="top">
                <template v-slot:activator="{ props }">
                    <v-btn
                        v-bind="props"
                        class="ma-1"
                        size="large"
                        color="success"
                        icon="mdi-delete"
                        @click="deleteButtonHandle()"
                    ></v-btn>
                </template>
                <span>Delete</span>
            </v-tooltip>
        </template>

        <!-- *EDIT BUTTON -->
        <template v-if="floatButtonData.editButton">
            <v-tooltip location="top">
                <template v-slot:activator="{ props }">
                    <v-btn
                        v-bind="props"
                        class="ma-1"
                        size="large"
                        color="success"
                        :icon="floatButtonData?.editButtonActive ? 'mdi-close' : 'mdi-pencil'"
                        @click="editButtonHanle()"
                    ></v-btn>
                </template>
                <span>{{ floatButtonData?.editButtonActive ? "Close" : "Edit" }}</span>
            </v-tooltip>
        </template>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import { useSampleStore } from '../../js/store';


export default {
    name: "FloatButtonCompnent",
    props:{
        floatButtonData: {
            addButton: Boolean,
            editButton: Boolean,
            editButtonActive: Boolean,
            deleteButton: Boolean,
        },

    },

    data() {
        return {  
        };
    },

    computed:{
        ...mapState(useSampleStore,[
            'selected',
            'selectedRows'
        ])
    },

    methods:{
        addButtonHandle(){
            this.$emit("addButtonClicked", true)
        },
        editButtonHanle(){
            this.$emit("editButtonClicked", true)
        },
        deleteButtonHandle(){
            this.$emit("deleteButtonHandle", true)
        }
    }
}
</script>

<style scoped>
    
</style>
<template>
    <v-toolbar class="toolBar" density="compact" color="white">
    <v-sheet>
        <p class="title">{{ toolbarData?.title }}</p>
        <p class="subTitle">{{ toolbarData?.subTitle }}</p>
        <!-- <p>{{ badge_cnt }}</p> -->
    </v-sheet>
    <template v-slot:append>
        <v-btn v-if="toolbarData.back.show" icon :color="'grey-darken-2'" v-tooltip="'Back'" :href="toolbarData.back.url">
        <v-icon>mdi-arrow-left</v-icon>
        </v-btn>

        <v-btn v-if="toolbarData.masters.show" icon :color="'grey-darken-2'" v-tooltip="'Masters'" :href="toolbarData.masters.url" >
        <v-icon>mdi-database</v-icon>
        </v-btn>

        <v-btn v-if="toolbarData.view" @click="toggleViewMode(!viewMode)" :class="{ 'indigo--text': viewMode }" icon :color="'grey-darken-2'" v-tooltip="viewMode ? 'Hide' : 'Show'">
        <v-icon>{{viewMode ? 'mdi-eye-off-outline' : 'mdi-eye-outline'}}</v-icon>
        </v-btn>

        <v-btn v-if="toolbarData.filter" @click="toggleFilterMode(!filterMode)" :class="{ 'indigo--text': filterMode }" icon :color="'grey-darken-2'" v-tooltip="filterMode ? 'Un-filter' : 'Filter'">
        <v-icon>{{filterMode ? 'mdi-filter-minus-outline' : 'mdi-filter-plus-outline'}}</v-icon>
        </v-btn>

        <v-btn v-if="toolbarData.poWeeksFilter" @click="togglepoWeeksFilter()" icon :color="'grey-darken-2'" v-tooltip="'Weeks Filter'">
        <v-icon>mdi-calendar-search</v-icon>
        </v-btn>

        </template>
    </v-toolbar>
</template>

<script>
import { mapState, mapActions } from "pinia";
import { useSampleStore } from "../../js/store.js";

export default {
    emits: ['routeChange'],
    name: "ToolBarComponent",
    props: {
        toolbarData: {
            title: String,
            back: Boolean,
            view: Boolean,
            filter: Boolean,
            poWeeksFilter: Boolean,
            default: () => ({ masters: { show: false, url: ''} })
        },
    },
    methods: {
        ...mapActions(useSampleStore, [
            'toggleFilterMode',
            'toggleViewMode',
        ]),

        handleClick() {
        this.$emit("button-clicked", "Button clicked!");
        },

        togglepoWeeksFilter() {
        this.$emit("togglepoWeeksFilter");
        },
    },
    computed: {
        ...mapState(useSampleStore,
        [
            'filterMode',
            'viewMode',
        ])
    },
};
</script>

<style>
.toolBar .v-card .v-card-item {
padding: 0px !important;
}
.toolBar .v-card-title {
font-size: 16px !important;
}
.toolBar .v-card-subtitle {
font-size: 13px !important;
padding: 0px !important;
}
.title{
font-size: 16px;
font-weight: 400;
}
.subTitle{
font-size: 13px;
font-weight: 100;
color: rgb(124, 114, 114) !important;
}
</style>
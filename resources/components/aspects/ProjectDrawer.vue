<template>
    <v-navigation-drawer app v-model="drawerVal" @transitionend="toggleDrawer(drawerVal)">
    <v-list-item class="search_field pt-0 pb-0 mb-0 mt-0">
    <v-list-item-title>
        <div class="d-flex align-center justify-space-evenly pt-2">
        <span class="mt-1 mr-4" style="font-size: 18px; color: #424242">PROJECTS</span>
        <!--NOTE Start Menu Sort -------------------------------------------------------------------------------------------------->
        <v-menu bottom offset-y transition="slide-y-transition" v-model="menu_sort" :close-on-content-click="false">
            <template v-slot:activator="{ props: menu }">
            <v-tooltip bottom>
                <template v-slot:activator="{ props: tooltip }">
                <v-btn icon size="small" density="compact" variant="plain" v-bind="{...tooltip, ...menu}" :color="selected_sort.length ? 'primary' : ''">
                    <svg fill="currentColor" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="24" height="24" viewBox="0 -30 460 500" xml:space="preserve">
                    <path d="M 0.008 93.70507 C 0.008 79.59207 11.45 68.15007 25.563 68.15007 L 283.089 68.15007 L 283.089 119.26106999999999 L 25.562999999999988 119.26106999999999 C 11.449999999999989 119.26106999999999 0.00799999999998846 107.81806999999999 0.00799999999998846 93.70506999999999 z M 0.008 196.26607 C 0.008 182.15207 11.45 170.71007 25.563 170.71007 L 283.089 170.71007 L 283.089 221.82107 L 25.562999999999988 221.82107 C 11.449999999999989 221.82107 0.00799999999998846 210.38007 0.00799999999998846 196.26606999999998 z M 209.857 273.27007 C 204.334 282.76406999999995 201.377 293.58106999999995 201.377 304.85807 C 201.377 311.59207 202.44400000000002 318.15707 204.465 324.38107 L 25.555000000000007 324.38107 C 11.442000000000007 324.38107 7.105427357601002e-15 312.94007000000005 7.105427357601002e-15 298.82607 C 7.105427357601002e-15 284.71207000000004 11.442000000000007 273.27107 25.555000000000007 273.27107 L 25.555000000000007 273.27007000000003 z M 450.62569 327.706 L 368.92569000000003 409.40500000000003 C 356.29569000000004 422.048 335.80769000000004 422.00500000000005 323.21169000000003 409.40500000000003 L 241.51169000000004 327.706 C 228.89069000000003 315.08500000000004 228.89069000000003 294.62300000000005 241.51169000000004 282.002 C 254.13269000000005 269.38100000000003 274.59469 269.382 287.21569000000005 282.002 L 313.7506900000001 308.53700000000003 L 313.7506900000001 32.31700000000001 C 313.7506900000001 14.469000000000008 328.21969000000007 7.105427357601002e-15 346.06869000000006 7.105427357601002e-15 C 363.91769000000005 7.105427357601002e-15 378.38669000000004 14.468000000000007 378.38669000000004 32.31700000000001 L 378.38669000000004 308.53700000000003 L 404.92169000000007 282.002 C 417.54269000000005 269.38100000000003 438.0056900000001 269.38100000000003 450.6256900000001 282.002 C 463.24669000000006 294.623 463.2456900000001 315.08500000000004 450.6256900000001 327.706"/>
                    </svg>
                </v-btn>
                </template>
                <span>Sorting</span>
            </v-tooltip>
            </template>
            <v-card max-width="320" width="100%" elevation="0">
                <v-card-title>
                <span style="font-weight: bold"> Sort Project </span>
                </v-card-title>
                <v-divider></v-divider>
                <v-card-text style="padding: 2px 2px;">
                <v-container fluid class="px-2 py-2">
                    <v-row v-for="item in item_sort" no-gutters :key="item.id">
                    <template v-if="item.sort == ''">
                        <v-col cols="9">
                        <v-checkbox
                            @change="trigSorting(item.id)"
                            density="compact"
                            hide-details
                            v-model="selected_sort"
                            :label="item.name"
                            :value="item"
                            style="padding-bottom: 10px;"
                            >
                        </v-checkbox>
                        </v-col>
                    </template> 
                    </v-row>
                    <div class="separator" v-show="selected_sort.length">Sort By</div>
                    <v-row v-for="item in menu_sorting" no-gutters :key="item.name">
                        <template v-if="item.sort != ''">
                        <v-col cols="9">
                            <v-checkbox
                            @change="trigSorting(item.id)"
                            v-model="selected_sort"
                            :label="item.name"
                            :value="item"
                            hide-details
                            density="compact"
                            >
                            </v-checkbox>
                        </v-col>
                        <v-col cols="3">
                            <template v-if="item.sort">
                            <v-btn icon @click="trigSorting(item.id)" class="asde">
                                <template v-if="item.sort === 'asc'">
                                <svg style="width:20px;height:20px" viewBox="0 0 20 24">
                                    <path d="M19 17H22L18 21L14 17H17V3H19M11 13V15L7.67 19H11V21H5V19L8.33 15H5V13M9 3H7C5.9 3 5 3.9 5 5V11H7V9H9V11H11V5C11 3.9 10.11 3 9 3M9 7H7V5H9Z" />
                                    <title>Ascending</title>
                                </svg>
                                </template>
                                <template v-else>
                                <svg style="width:20px;height:20px" viewBox="0 0 24 24">
                                    <path d="M19 7H22L18 3L14 7H17V21H19M11 13V15L7.67 19H11V21H5V19L8.33 15H5V13M9 3H7C5.9 3 5 3.9 5 5V11H7V9H9V11H11V5C11 3.9 10.11 3 9 3M9 7H7V5H9Z" />
                                    <title>Descending</title>
                                </svg>
                            </template>
                            </v-btn>
                            </template>
                        </v-col>
                        </template>
                    </v-row>
                </v-container>
                </v-card-text>
                <v-divider></v-divider>
                <v-card-actions>
                <v-spacer></v-spacer>
                    <v-btn
                    small
                    color="success"
                    @click="item_sort.map(e => e.sort = ''); selected_sort = []"
                    :disabled="selected_sort.length === allProjects.length"
                    >
                    <v-icon dark>mdi-cached</v-icon>RESET
                    </v-btn>
                    <v-btn @click="sortMenuCancel('close'); menu_sort = !menu_sort" :disabled="disabled" color="grey darken-3" outlined small >
                    <svg style="width:20px;height:20px">
                        <path fill="currentColor" d="M20 6.91L17.09 4L12 9.09L6.91 4L4 6.91L9.09 12L4 17.09L6.91 20L12 14.91L17.09 20L20 17.09L14.91 12L20 6.91Z" />
                    </svg>CLOSE
                </v-btn>
                <v-btn @click="sortMenuUpdate(), menu_sort = !menu_sort" :disabled="disabled" color="grey darken-3" outlined small >
                    <v-icon small>mdi-content-save</v-icon>SAVE
                </v-btn>
                </v-card-actions>
            </v-card>
        </v-menu>
        <!-- End Menu Sort ---------------------------------------------------------------------------------------------------->
        <!--NOTE Start Menu Filter -------------------------------------------------------------------------------------------->
        <v-menu offset-y transition="slide-y-transition" v-model="menu_filter" :close-on-content-click="false">
            <template v-slot:activator="{ props: menu }">
            <v-tooltip bottom>
                <template v-slot:activator="{ props: tooltip }">
                <v-btn variant="plain" density="compact" v-bind="{ ...tooltip, ...menu }" :color="selected_filter.length ? '': 'primary'"  icon>
                    <v-icon>mdi-filter-menu</v-icon>
                </v-btn>
                </template>
                <span>Filtering</span>
            </v-tooltip>
            </template>
            <v-card max-width="280" width="100%" elevation="0" :disabled="disabled" style="overflow: hidden;">
            <v-card-title>
                <span style="font-weight: bold">Filter Project</span>
            </v-card-title>
            <v-row align="center">
                <v-col class="text-center pl-0 pb-7 pt-7">
                <v-btn elevation="2" width="100" @click="setHideProject('SEATTLE')" small :disabled="selected_branch == 'SEATTLE'" style="font-weight:bold;">WA</v-btn>
                </v-col>
                <v-col class="text-center pl-0 pb-6 pt-6">
                <v-btn elevation="2" width="100" @click="setHideProject('PORTLAND')" small :disabled="selected_branch =='PORTLAND'" style="font-weight:bold;">OR</v-btn>
                </v-col>
            </v-row>
            <v-divider></v-divider>
            <v-card-text class="card_content" style="overflow: auto; max-height: 300px; padding: 6px 14px;">
                <v-container>
                <v-row v-for="project in masterDrawerProjects"
                    :key="project.id"
                    align="center"
                    class="mx-n2 my-n5"
                    no-gutters
                    >
                    <v-col cols="2" class="pt-">
                    <v-checkbox @change="selected_branch = null" v-model="selected_filter" :value="project.id"></v-checkbox>
                    </v-col>
                    <v-col cols="10">
                    <span style="font-weight: bold;">{{project.text}}</span>
                    </v-col>
                </v-row>
                </v-container>
            </v-card-text>
            <v-divider></v-divider>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                small
                color="success"
                @click="selected_filter = masterDrawerProjects.map(proj => proj.id) ;selected_branch = null"
                :disabled="selected_filter.length === masterDrawerProjects.length"
                >
                <v-icon dark>mdi-cached</v-icon>RESET
                </v-btn>
                <v-btn @click="selected_filter = drawerProjectFeatures.filter.length ? drawerProjectFeatures.filter : masterDrawerProjects.map(proj => proj.id); menu_filter = !menu_filter" :disabled="disabled" color="grey darken-3" outlined sm>
                <svg style="width:20px;height:20px" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M20 6.91L17.09 4L12 9.09L6.91 4L4 6.91L9.09 12L4 17.09L6.91 20L12 14.91L17.09 20L20 17.09L14.91 12L20 6.91Z" />
                </svg>CLOSE
                </v-btn>
                <v-btn @click="filterMenuUpdate(selected_filter), menu_filter = !menu_filter" :disabled="disabled" color="grey darken-3" outlined small >
                <v-icon small>mdi-content-save</v-icon>SAVE
                </v-btn>
            </v-card-actions>
            </v-card>
        </v-menu>
        <!-- End Menu Filter ---------------------------------------------------------------------------------------------------->
        <!--NOTE Start View Mode ---------------------------------------------------------------------------------------------------->
            <v-tooltip bottom>
            <template v-slot:activator="{ props: tooltip }">
                <v-btn icon variant="plain" density="compact" v-bind="tooltip" @click="viewMode" :color="view_mode ? 'primary' : ''" :disabled="selected_filter.length"><v-icon>{{view_mode ? 'mdi-eye-off': 'mdi-eye' }}</v-icon></v-btn>
            </template>
            <span>{{view_mode ? 'HIDE' : 'SHOW'}}</span>
        </v-tooltip>
        <!-- End View Mode ---------------------------------------------------------------------------------------------------->
        <!--NOTE Start Menu Details ---------------------------------------------------------------------------------------------------->
        <v-menu offset-y transition="slide-y-transition" v-model="menu_details" :close-on-content-click="false">
            <template v-slot:activator="{ props: menu }">
            <v-tooltip bottom>
                <template v-slot:activator="{ props: tooltip }">
                <v-btn variant="plain" density="compact" v-bind="{ ...tooltip, ...menu }"
                    :color="JSON.stringify(details_items) !== JSON.stringify(clone_details_item) || selected_detail.length < 4 ? 'primary' : ''"
                    icon><v-icon>mdi-file-table-box-multiple</v-icon>
                </v-btn>
                </template>
                <span>Details</span>
            </v-tooltip>
            </template>
            <v-card width="320" elevation="0" :disabled="disable">
            <v-card-title>
                <span style="font-weight: bold">Details</span>
            </v-card-title>
                <v-divider class="mx-2"></v-divider>
            <v-card-text class="card-content" style="padding: 6px 14px;">
                <v-list>
                    <v-list-item-group multiple v-model="selected_detail">
                    <draggable :list="details_items" handle=".move" ghost-class="ghost" :force-fallback="true" fallback-class="dragged-item">
                        <v-list :list="details_items">
                        <template  v-for="item in details_items" :key="item.value">
                            <v-list-item :value="item.value" class="d-flex">
                            <template v-slot:default="{ active }">
                                <div>
                                <v-list-item-action >
                                    <v-checkbox
                                    :v-model="active"
                                    density="compact"
                                    hide-details
                                    >
                                    </v-checkbox>
                                    <v-list-item>
                                    <v-list-item-title>{{ item.name}}</v-list-item-title>
                                    </v-list-item>
                                    <v-list-item-icon>
                                    <v-icon class="move">mdi-drag-horizontal-variant</v-icon>
                                    </v-list-item-icon>
                                </v-list-item-action>
                                </div>
                            </template>
                            </v-list-item>
                        </template>
                        </v-list>
                    </draggable>         
                    </v-list-item-group>
                </v-list>    
            </v-card-text>
            <v-divider></v-divider>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                small
                color="success"
                @click="details_items = [...clone_details_item]; selected_detail = [...clone_selected_detail]"
                >
                <v-icon dark>mdi-cached</v-icon>
                RESET
                </v-btn>
                <v-btn @click="closeDetails();
                menu_details = !menu_details"
                :disabled="disabled"
                color="grey darken-3" outlined small >
                <svg style="width:20px;height:20px" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M20 6.91L17.09 4L12 9.09L6.91 4L4 6.91L9.09 12L4 17.09L6.91 20L12 14.91L17.09 20L20 17.09L14.91 12L20 6.91Z" />
                </svg>
                CLOSE
                </v-btn>
                <v-btn @click="detailsInfoUpdate('save', selected_detail, details_items), menu_details = !menu_details" :disabled="disabled" color="grey darken-3" outlined small >
                <v-icon small>mdi-content-save</v-icon>SAVE
                </v-btn>
            </v-card-actions>
            </v-card>
        </v-menu>
        <!-- End Menu Details ---------------------------------------------------------------------------------------------------->

        </div>
    </v-list-item-title>
    <v-list-item-title>
        <v-text-field
        v-model="tempSearch"
        class="mt-3 nav-input"
        label="Search"
        persistent-placeholder
        hide-details
        clearable
        variant="outlined"
        append-inner-icon="mdi-magnify"
        density="compact"
        ></v-text-field>
    </v-list-item-title>
    </v-list-item>

    <v-list-item
    class="mt-1"
    link
    :class="{'project_all v-item--active v-list-item--active': masterDrawerProjects}"
    >
        <v-list-item-title class="caption">ALL</v-list-item-title>
    </v-list-item>
    <v-divider></v-divider>
    <v-list density="compact">
    <template v-for="project in masterDrawerProjects" :key="project.id">
        <v-list-item
        link
        hide-details>
        <v-list-item-action @click="project.model = !project.model">
            <v-list-item-title class="project-name">{{ project.text }}</v-list-item-title>
            <v-btn icon small density="compact" variant="plain">
        <v-icon >{{ project.model ? 'mdi-chevron-down' : 'mdi-chevron-left' }}</v-icon>
        </v-btn>
    </v-list-item-action>
        </v-list-item>
        <v-divider></v-divider>
    </template>
    </v-list>


</v-navigation-drawer>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useSampleStore } from '../../js/store.js';
export default {
    props: {
        drawer: {
            type: Boolean,
            required: true
        },
    },
    data(){
        return{
            drawerVal: this.drawer,
            menu_sort:false,
            menu_details: false,
            selected_sort:[],
            disabled:false,
            allProjects: [],
            menu_filter:false,
            selected_filter: [],
            details_items: [],
            view_mode: false,
            view_mode: false,
            selected_detail: [],
                
            tempSearch:'',
            item_sort: [
            { id: 1, name: 'Lot', value: 'lot', sort: '' },
            { id: 2, name: 'Type Code', value: 'type_code', sort: ''},
            { id: 3, name: 'Customer Code', value: 'construction_code', sort: ''},
            { id: 4, name: 'House Code', value: 'house_code', sort: ''},
            { id: 5, name: 'Shipping Flag', value: 'shipping_flag', sort: ''},
            { id: 6, name: 'Project Name', value: 'text', sort: ''},
            ],
            clone_details_item: [
            { id: 1, name: 'Type Code', value: 'type_code' },
            { id: 2, name: 'Customer Code', value: 'construction_code' },
            { id: 3, name: 'House Code', value: 'house_code' },
            { id: 4, name: 'Shipping Flag', value: 'shipping_flag' }
            ],
            clone_selected_detail: ['type_code', 'construction_code', 'house_code', 'shipping_flag'],
        }
    },

    methods:{
    ...mapActions(useSampleStore,[
        'getmasterDrawerProjects'
    ]),

    toggleDrawer(val){
        this.$emit("toggleDrawer",val)
    },

    trigSorting(id){
            const allProjects = this.masterDrawerProjects;
            let res = this.item_sort.find(item => item.id === id)
            if(res.sort !== ''){
            let selected = this.selected_sort.find(item => item.id === id)
            if(!selected){
                res.sort = ''
            }
            else{
                selected.sort = selected.sort === 'asc' ? 'desc' : 'asc'
            }
            }else{
            res.sort = 'asc'
        }
        },
        viewMode(bool){
        this.view_mode = !bool
        let allProjects = this.masterDrawerProjects;
        const selected = this.selected_filter;
        if(bool){
            if(selected.length){
            allProjects = allProjects.filter(project => {
            if(selected.includes(project.id))
                return project
            })
            }
        }
        this.allProjects = [...allProjects];
        },
        
        closeDetails(){
            if('details' in this.drawerProjectFeatures){
            var details = this.drawerProjectFeatures.details
            this.details_items = details[1].length ? [...details[1]] : [...this.clone_details_item];
            this.selected_detail = details[0].length ? [...details[0]] : [...this.clone_selected_detail]
            }
            else {
            this.details_items = [...this.clone_details_item];
            this.selected_detail = [...this.clone_selected_detail]
            }
        },
    },

    computed:{
    ...mapState(useSampleStore,[
        'masterDrawerProjects'
    ]),

    menu_sorting(){
        return [...this.selected_sort].reverse();
    },
    },

    watch: {
        drawer(val){
            this.drawerVal = val
        }
    },
    
    created(){
        this.getmasterDrawerProjects()
    }
}
</script>

<style scoped>
.search_field {
    min-height: 48px;
    padding-right: 0px;
}

.v-navigation-drawer .v-list {
    padding: 0px !important
}

.v-navigation-drawer .v-list-item__content {
    padding: 0px !important;
}

.v-navigation-drawer .v-list-group__header .v-list-item--active {
    background-color: #1866d1;
    color:#1866d1;
}

.active-item {
    background-color:#e3eefa;
    color:#1866d1 !important;
}

.v-navigation-drawer .v-list-group__header .v-list-item__title {
    font-size: 12px !important;
    font-weight: bold;
}

.v-navigation-drawer .v-list-group__header .v-list-item__icon .v-icon {
    font-size: 16px !important
}

.v-navigation-drawer .v-list-group__items .v-list-item__title {
    font-size: 12px !important;
}

/* .v-navigation-drawer .v-list-group__items .v-list-item__title > span {
    font-size: 8px !important;
} */

.v-navigation-drawer .v-list-group__items .v-list-item {
    padding-left: 16px !important;
}

.v-navigation-drawer .project_all.v-item--active.v-list-item--active{
    background-color:#e3eefa;
    color:#1866d1;
}

.v-input__slot {
    -webkit-box-align: stretch;
    align-items: stretch;
    min-height: 32px !important;
}

.v-list-group >>> .v-list-group__header__append-icon {
    min-width: 24px !important;
}

.v-navigation-drawer .v-list-group__header .v-list-item__icon .v-icon {
    font-size: 24px !important;
}

.project-name {
    font-size: 12px !important;
    font-family: Roboto, sans-serif !important;
    font-weight: bold;
    flex-grow: 1;
}

.project-list {
    font-size: 12px !important;
}

/* .project-list span {
    font-size: 8px !important;
} */

.card-content::-webkit-scrollbar {
width: 14px;
}

/* Track */
.card-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    margin: 4px;
    border-radius: 5px
}
/* Handle */
.card-content::-webkit-scrollbar-thumb {
    background: #888;
    border: 1px solid white;
    border-radius: 5px;
}

/* Handle on hover */
.card-content::-webkit-scrollbar-thumb:hover {
background: #555;
}

.separator {
display: flex;
align-items: center;
text-align: center;
color: #919191;
}

.separator::before,
.separator::after {
content: '';
flex: 1;
border-bottom: 1px solid #e0e0e0;
}

.separator:not(:empty)::before {
margin-right: .25em;
}

.separator:not(:empty)::after {
margin-left: .25em;
}

.ghost {
    opacity: 0;
}

.dragged-item {
    opacity: 1;
}

.separator::before,
.separator::after {
content: '';
flex: 1;
border-bottom: 1px solid #e0e0e0;
}

.separator:not(:empty)::before {
margin-right: .25em;
}

.separator:not(:empty)::after {
margin-left: .25em;
}

.caption{
font-size: 12px;
font-weight: 400;
font-family: Roboto, sans-serif !important;
}

.asde{
margin-top: 5px !important;
color: #919191;
}

.detailsDrag{
display: block;
margin: 15px;
align-content: center;
}

</style>

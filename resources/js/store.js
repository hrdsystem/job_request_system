import { defineStore } from 'pinia'

export const useSampleStore = defineStore('sampleStore', {
    state: () => {
        return {
            // Global Variables

            tableHeight: window.innerHeight - 160,
            viewMode: false,
            filterMode: false,
            editMode: false,
            overlay: false,
            
            masterDrawerProjects: [],
            masterProjectSearch: null,
        }
    },

    getters: {
        // Global Computed
    },

    actions: {
        // Global Functions
        toggleViewMode(value){
            this.viewMode = value
        },
        
        toggleFilterMode(value){
            this.filterMode = value
        },

        getmasterDrawerProjects() {
            this.overlay = true
            axios.get(`/api/project_drawer`, {
                keyword: this.masterProjectSearch,
              // path: this.route.name
            }).then((response) => {
                var obj = [...response.data];
                var result = obj.map(row => {
                let active = row.children.find(children => children.id === this.masterProjectRegisterActive);
                if (active != undefined) {
                    row.model = true
                }
                return row;
                })
                this.masterDrawerProjects = [...result]
            }).catch(error => {
                console.log(error)
            })
        },
    },
})

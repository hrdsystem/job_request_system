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

            JobRequestData: [],
            JobRequestRecords: 0,
            JobRequestSearch: [],
            JobRequestSort: [],

            JobRequestRequiredData: [],
            JobRequestRequiredRecords: 0,
            JobRequestRequiredSearch: [],
            JobRequestRequiredSort: [],

            selected: [],
            selectedRows: [],
            
            masterDrawerProjects: [],
            masterProjectSearch: null,

            rules: {
                required: [
                    v => !!v || 'Field is required'
                ],
                email: [
                    v => !v || /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'E-mail must be valid'
                ],
                password: [
                    v => !!v || 'Field is required',
                    v => !v || /[0-9]/.test(v) || 'Must contain number',
                    v => !v || /[a-zA-Z]/.test(v) || 'Must contain letter',
                  // v => !v || /[@$!%*?&]/.test(v) || 'Must contain symbol',
                    v => (v || '').length >= 8 || 'Must contain atlest 8 characters.'
                ],
                hex: [
                    v => !v || /[0-9A-Fa-f]{6}/.test(v) || 'Must be a hex value'
                ],
                ext_required: [
                    v => !v || /w*(\.\w{3})+$/.test(v) || 'Extension is Require'
                ],
                // confirmpassword(temp,actual){
                //   return [v => (temp === actual) || 'Password must match']
                // },
                memo_number: [
                    v => !!v || 'Field is required',
                    v => (v && v.length >= 8) || 'Incomplete'
                ],
                complaint_number: [
                    v => !!v || 'Field is required',
                    v => (v && v.replace("_", "").length >= 8) || 'Incomplete'
                ],
                requiredEditSeq: [
                    v => !v || /[0-9]/.test(v) || 'Field is required'
                ],
                // unique(list,title){
                //   return [
                //   v => !!v || 'Field is required',
                //   v => (_.includes(list,v) == false)  || title +' is already exists.'
                //   ]
                // },
                array: [
                    v => !v.length == 0 || 'Field is required', 
                ],
                // higher_number(min, max){
                //   return [
                //     v => !!v || 'Field is required',
                //     v => v > min || "Must Higher",
                //     v => v <= max || 'Too much',
                //   ]
                // },
                numberRule: [v => {
                    if (!isNaN(v) && v >= 0 && v <= 999) return true;
                    return 'Field is required';
                }],
                isignNumberRule: [v => {
                    if (!isNaN(v) && v >= 0 && v <= 9999999999) return true;
                    return 'Input must be number';
                }],
                isignPhoneNo: [
                    (v) => !!v || "Field is required",
                    (v) => (v || "").length == 10 || "Must contain atlest 10 Digits.",
                ],
                isignZipCode: [
                    (v) => ((v || "").length == 5 || (v || "").length == 0) || "Must contain atlest 5 characters.",
                ],
            },
        }
    },

    getters: {
        // Global Computed
    },

    actions: {
        // Global Functions
        executeSearch(obj){
            const pageActions = {
                //SECTION - JOB REQUEST MASTER
                JobRequestRequiredData: this.jobRequiredPage.bind(this)
            };
            const pageAction = pageActions[obj.page];
            if (pageAction){
                pageAction();
            } else{
                console.log(`no page action is found for ${obj.page}`);
            }
        },
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

        jobRequestPage(){
            this.overlay = true
            axios.post(`/api/job_requests/get_job_requests`,{
                search: this.JobRequestSearch,
                sort: this.JobRequestSort
            })
            .then(res =>{
                this.JobRequestData = res.data[0]
                this.JobRequestRecords = res.data[1]
                this.overlay = false
            }).catch(error =>{
                console.log(error)
            })
        },

        jobRequiredPage(){
            this.overlay = true
            axios.post(`/api/job_requests_master/get_job_requireds`,{
                search: this.JobRequestRequiredSearch,
                sort: this.JobRequestRequiredSort
            })
            .then(res =>{
                this.JobRequestRequiredData = res.data[0]
                this.JobRequestRequiredRecords = res.data[1]
                this.overlay = false
            }).catch(error =>{
                console.log(error)
            })
        }
    },
})

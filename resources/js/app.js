import './bootstrap';
// import { createApp } from 'vue'
import { createApp } from "vue/dist/vue.esm-bundler"

// ROUTER
import router from './router.js'

// VUETIFY
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

// MDI/FONT
import '@mdi/font/css/materialdesignicons.css'

//APP.VUE 
import JobRequestApp from '../components/templates/JobRequestApp.vue'
import JobRequestSettingsApp from '../components/templates/JobRequestSettingsApp.vue';


// PINIA
import { createPinia } from 'pinia'

const pinia = createPinia()
const vuetify = createVuetify({
    components,
    directives,
    icons: {
        defaultSet: 'mdi',
    },
    defaults: {
        VCard:{
            VCardItem:{
                style: 'padding:0px;',
            },
            // elevation:5,
            VCardActions:{
                variant:'elevated'
        
            },
            VCardTitle:{
                style: 'background-color:#37474F; color:#ffffff; padding:5px 10px',
            },
        
            VBtn:{
                variant:'text',
                density:'compact',
            },
            VTextField:{
                variant:'outlined',
                density:'compact',
                style: 'padding: 15px 0 0 0;', //old padding: 15px 15px 0 15px
                // persistentPlaceholder:true,
                // hideDetails:true
            },
            VTextarea:{
                style: 'padding: 15px 0 0 0;', //old padding: 15px 15px 0 15px
                variant:'outlined',
                density:'compact'
            },
            VAutocomplete:{
                style: 'padding: 15px 0 0 0;', //old padding: 15px 15px 0 15px
                variant:'outlined',
                density:'compact',
                // persistentPlaceholder:true,
                // hideDetails:true
            },
        }
    }
})

// const app = createApp(App)
const app = createApp({})
app.component("JobRequestApp", JobRequestApp);
app.component("JobRequestSettingsApp", JobRequestSettingsApp);
app.use(pinia)
app.use(vuetify)
app.use(router)
app.mount('#app')

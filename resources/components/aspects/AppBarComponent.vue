<template>
    <v-app-bar  color="#263238">
        <div class="d-flex justify-center w-100">
        <div class="d-flex justify-center" >
            <v-img src="https://iconn.hrd-s.com/iconnsystem/img/iconn-logo-header-2.png" aspect-ratio="1/1" :width="20" class="mr-2 mb-2">
            </v-img>
            <p class="JOB REQUEST text-h6">iconn system testing</p>
        </div>
        </div>
        <template v-slot:prepend>
            <v-tooltip location="bottom">
                <template v-slot:activator="{ props }">
                    <v-app-bar-nav-icon v-bind="props" variant="text" class="ml-4" @click="toggleDrawer(!drawer)"></v-app-bar-nav-icon>
                </template>
                <span>{{ drawer ? 'Hide' : 'Show' }}</span>
            </v-tooltip>
            <v-btn icon="mdi-home"></v-btn>
            <v-btn icon="mdi-apps"></v-btn>
        </template>
        <template v-slot:append>
        <v-avatar class="mr-2" color="surface-variant" size="small">
            <v-img
                :src="'/img/avatar.png'"
            ></v-img>
            <!-- <v-img src="http://hrd-adminweb/photos/46227.jpg" aspect-ratio="1/1" :width="10">
            </v-img> -->
        </v-avatar>
        </template>
    </v-app-bar>
</template>

<script>
export default {
    props:{
        drawer: {
            type:Boolean,
            Required: true
        }
    },

    methods:{
        toggleDrawer(val){
            this.$emit("toggleDrawer", val)
        }
    }
}
</script>

<style scoped>
@media (max-width: 600px){
    .iconnsystem-title {
        display: none !important; 
    }
}
</style>
<template>
    <v-app app>
        <AppBarComponent :drawer="drawer" @toggleDrawer="toggleDrawer($event)"></AppBarComponent>
        <!-- <ProjectDrawer :drawer="drawer"></ProjectDrawer> -->
        <v-main>
            <router-view></router-view>
        </v-main>
    </v-app>
</template>

<script setup>
import ProjectDrawer from "@aspect/ProjectDrawer.vue"
import AppBarComponent from "@aspect/AppBarComponent.vue";
</script>

<script>
import { mapState, mapActions  } from 'pinia'
import { useSampleStore } from '../../js/store.js'
export default {
    data (){
        return {
            drawer: true,
        }
    },

    computed: {
        ...mapState(useSampleStore, [
        ]),
    },

    methods: {
        toggleDrawer(val){
            this.drawer = val
        }
    }
}

</script>

<style scoped>
.v-main{
    background-color: #ffffff;
}

#app{
    font-family: 'Arial';
}
</style>




<!-- <v-app-bar  color="#263238">
            <div class="d-flex justify-center w-100">
            <div class="d-flex justify-center" >
                <v-img src="img/iconn-logo-header-2.png" aspect-ratio="1/1" :width="20" class="mr-2 mb-2">
                </v-img>
                <p class="text-h6">iconn system</p>
            </div>
            </div>
            <template v-slot:prepend>
            <v-app-bar-nav-icon variant="text" class="ml-4" @click="toggleDrawer(!drawer)" />
            <v-btn icon="mdi-home"></v-btn>
            <v-spacer></v-spacer>
            <v-btn icon="mdi-apps"></v-btn>
            </template>
            <template v-slot:append>
            <v-avatar class="mr-2" color="surface-variant" size="small">
                <v-img src="/img/avatar.jpg" aspect-ratio="1/1" :width="10">
                </v-img>
            </v-avatar>
            </template>
        </v-app-bar> -->


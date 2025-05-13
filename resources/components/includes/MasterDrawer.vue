<template>
<v-navigation-drawer v-model="drawerVal" @click="toggleDrawer(drawerVal)">
    <v-list>
        <v-list-group>
            <v-list-item
                v-for="(item, i) in drawerItems"
                :key="i"
                :to="item.path"
                class="drawTitle"
                link
                density="compact"
                style="margin-left: 2"
            >
                <v-icon :icon="item.icon" class="ml-0 drawIcon"></v-icon>
                {{ item.title }}
            </v-list-item>
        </v-list-group>
    </v-list>
</v-navigation-drawer>
</template>

<script>
export default {
    props: {
        drawer: {
            type: Boolean,
            required: true
        },
        drawerItems: {
            type: Array,
            required: true
        }
    },

    data(){
        return {
            drawerVal: this.drawer,
        }
    },

    watch: {
        drawer(val){
            this.drawerVal = val
        }
    },

    methods: {
        toggleDrawer(val) {
            this.$emit("toggleDrawer", val)
        },
    }
}
</script>

<style scoped>
/* keep the same styles as before */
.drawIcon{
    font-size: 24px;
    color: rgb(94, 91, 91);
}
.drawTitle{
    font-size: 13px;
}

.v-navigation-drawer .v-list-item{
    min-height: 32px !important;
}
.v-navigation-drawer .v-list-item__icon{
    align-self: center !important;
    font-size: 16px !important;
    margin-right: 16px !important;
}
.v-navigation-drawer .v-list-item__content{
    padding: 0px !important;
}
.v-navigation-drawer .v-list-item--active{
    background: #e7effd;
    color:#1866d1;
}
.v-navigation-drawer .v-list-item{
    height: 32px !important;
    font-size:  12px !important;
}
.v-navigation-drawer .v-list-item__title{
    font-size: 12px !important;
}

</style>
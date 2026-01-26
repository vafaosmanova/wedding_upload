import {createRouter, createWebHistory} from 'vue-router';

import HeroSection from '../Components/HeroSection.vue';
import Login from '../Components/Auth/Login.vue';
import Dashboard from '../Components/User/Dashboard.vue';
import GastAlbum from '../Components/Gast/GastAlbum.vue';
import PinVerification from "../Components/Gast/PinVerification.vue";
import Datenschutz from "../Components/Datenschutz.vue";
import Impressum from "../Components/Impressum.vue";

const routes = [
    {path: '/', name: 'Home', component: HeroSection},
    {path: '/login', name: 'Login', component: Login},
    {path: '/dashboard', name: 'Dashboard', component: Dashboard},
    {path: '/gast/:albumId/verify-pin', name: 'PinVerification', component: PinVerification, props: true},
    {path: '/gast/:albumId', name: 'GastAlbum', component: GastAlbum, props: true},
    {path: '/datenschutz', name: 'Datenschutz', component: Datenschutz},
    {path: '/impressum', name: 'Impressum', component: Impressum},
    {path: '/:catchAll(.*)', redirect: '/'}
]
export default createRouter({
    history: createWebHistory(),
    routes
})

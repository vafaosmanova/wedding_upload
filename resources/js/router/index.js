import { createRouter, createWebHistory } from 'vue-router';

import HeroSection from '../Components/HeroSection.vue';
import Login from '../Components/Auth/Login.vue';
import Register from '../Components/Auth/Register.vue';
import Dashboard from '../Components/Besitzer/Dashboard.vue';
import AlbumsSection from '../Components/Besitzer/AlbumList.vue';
import MediaApproval from '../Components/Besitzer/MediaApproval.vue';

import GuestAlbum from '../Components/Gast/GuestAlbum.vue';

const routes = [
    { path: '/', name: 'Home', component: HeroSection },
    { path: '/login', name: 'Login', component: Login },
    { path: '/register', name: 'Register', component: Register },
    { path: '/dashboard', name: 'Dashboard', component: Dashboard },
    { path: '/albums', name: 'Albums', component: AlbumsSection },
    {
        path: '/guest/:albumId',
        name: 'GuestAlbum',
        component: GuestAlbum,
        props: route => ({ albumId: route.params.albumId }) // <-- use albumId
    },


    { path: '/approval', name: 'MediaApproval', component: MediaApproval },

    { path: '/:catchAll(.*)', redirect: '/' }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;

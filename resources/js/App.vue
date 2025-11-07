<template>
    <div class="font-lila bg-gray-100 min-h-screen">
        <Header />
        <div class="max-w-6xl mx-auto p-6">
            <router-view></router-view>
        </div>
        <Footer />
    </div>
</template>

<script>
import Header from './Components/Header.vue';
import Footer from "./Components/Footer.vue";

export default {
    components: {Footer, Header }
};
</script>

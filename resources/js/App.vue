<template>
        <Header/>
        <main>
            <div>
            <router-view></router-view>
            </div>
        </main>
        <Footer/>

        <div class="absolute inset-0 pointer-events-none">
            <span class="heart animate-float absolute bg-pink-300 w-2 h-2 rounded-full top-10 left-10"></span>
            <span class="heart animate-float absolute bg-white w-2 h-2 rounded-full top-20 right-16"></span>
            <span class="heart animate-float absolute bg-pink-200 w-1.5 h-1.5 rounded-full bottom-16 left-20"></span>
            <span class="heart animate-float absolute bg-white w-1.5 h-1.5 rounded-full bottom-10 right-12"></span>
        </div>
</template>

<script>
import Header from './Components/Header.vue';
import Footer from './Components/Footer.vue';

export default {
    components: {Header, Footer}
};
</script>

<style scoped>

@keyframes float {
    0% {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    50% {
        transform: translateY(-12px) scale(1.2);
        opacity: 0.7;
    }
    100% {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

.heart {
    animation: float 6s ease-in-out infinite;
}
</style>

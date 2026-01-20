<template>
    <header
        class="bg-gradient-to-r from-purple-700 to-pink-500 text-white shadow-md p-4 font-sans relative overflow-hidden">

        <div class="absolute top-0 left-0 w-full h-full pointer-events-none">
            <span class="heart animate-float absolute bg-pink-300 w-2 h-2 rounded-full"></span>
            <span class="heart animate-float absolute bg-white w-1.5 h-1.5 rounded-full"></span>
            <span class="heart animate-float absolute bg-pink-200 w-1.5 h-1.5 rounded-full"></span>
        </div>

        <div class="flex items-center justify-between z-10 relative">

            <h1 class="text-2xl md:text-3xl font-script tracking-wide">Hochzeits-Album-Plattform</h1>

            <button
                @click="isOpen = !isOpen"
                class="md:hidden flex flex-col justify-between w-6 h-5 focus:outline-none"
            >
                <span class="block h-0.5 w-full bg-white transition-transform"
                      :class="{'rotate-45 translate-y-2': isOpen}"></span>
                <span class="block h-0.5 w-full bg-white transition-opacity" :class="{'opacity-0': isOpen}"></span>
                <span class="block h-0.5 w-full bg-white transition-transform"
                      :class="{'-rotate-45 -translate-y-2': isOpen}"></span>
            </button>

            <nav class="hidden md:flex gap-4 text-white text-lg font-sans">
                <router-link to="/FeaturesSection" class="hover:text-yellow-300 transition-colors duration-200">
                    Leistungen
                </router-link>
                <router-link to="/PricingSection" class="hover:text-yellow-300 transition-colors duration-200">Preise
                </router-link>
                <router-link to="/ContactSection" class="hover:text-yellow-300 transition-colors duration-200">Kontakt
                </router-link>
                <button @click.prevent="logout" class="hover:text-yellow-300 transition-colors duration-200">Logout
                </button>
                <router-link to="/login" class="hover:text-yellow-300 transition-colors duration-200">Login
                </router-link>
            </nav>

        </div>

        <transition name="slide-fade">
            <nav v-if="isOpen" class="flex flex-col gap-3 mt-3 text-white font-sans md:hidden z-10 relative">
                <ul>
                    <li>
                        <router-link to="/FeaturesSection" class="hover:text-yellow-300 transition-colors duration-200">
                            Leistungen
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/PricingSection" class="hover:text-yellow-300 transition-colors duration-200">
                            Preise
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/ContactSection" class="hover:text-yellow-300 transition-colors duration-200">
                            Kontakt
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/login" class="hover:text-yellow-300 transition-colors duration-200">Login
                        </router-link>
                    </li>
                    <li>
                        <button @click.prevent="logout" class="hover:text-yellow-300 transition-colors duration-200">
                            Logout
                        </button>
                    </li>
                </ul>
            </nav>
        </transition>

    </header>
</template>

<script>
export default {
    data() {
        return {
            isOpen: false,
        };
    },
    methods: {
        logout() {
            localStorage.removeItem('authToken');
            this.$router.push('/');
        }
    }
};
</script>

<style scoped>
@keyframes float {
    0% {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    50% {
        transform: translateY(-20px) scale(1.2);
        opacity: 0.7;
    }
    100% {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

.heart {
    animation: float 4s ease-in-out infinite;
}

.heart:nth-child(1) {
    top: 10%;
    left: 15%;
    animation-delay: 0s;
}

.heart:nth-child(2) {
    top: 50%;
    left: 50%;
    animation-delay: 1.5s;
}

.heart:nth-child(3) {
    top: 80%;
    left: 80%;
    animation-delay: 3s;
}

/* Slide fade transition for mobile menu */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateY(-10px);
    opacity: 0;
}
</style>

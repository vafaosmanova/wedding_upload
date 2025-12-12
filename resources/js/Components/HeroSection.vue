<template>
    <div
        class="relative text-5xl text-purple-700 font-script text-center bg-gradient-to-r from-purple-200 to-purple-800-800 flex col justify-center items-center overflow-hidden">
        <h1 class="text-5xl font-dancing mb-5 drop-shadow-lg">
            Die Foto-App für deine Hochzeitsgäste
        </h1>
        <p class="text-xl mb-10 opacity-90">
            Alle Bilder deiner Hochzeit einfach teilen und sichern – ganz ohne Stress.
        </p>
    </div>
    <section
        class="relative py-12 px-5 min-h-screen bg-gradient-to-r from-purple-200 to-purple-800-800 flex justify-center items-center overflow-hidden">


        <div class="hidden md:flex flex-col gap-8 absolute left-0 top-1/4 z-0">
            <img src="/assets/images/img1.jpg" alt="Couple 1"
                 class="w-64 md:w-80 animate-float-slow rounded-xl shadow-lg"/>
            <img src="/assets/images/img2.jpg" alt="Couple 2"
                 class="w-56 md:w-72 animate-float-variation1 rounded-xl shadow-lg"/>

        </div>


        <div class="bg-purple-200 shadow-xl rounded-3xl p-10 max-w-md w-full z-10 relative">
            <h2 class="text-5xl text-purple-700 mb-6 font-script text-center">Registrieren</h2>

            <form @submit.prevent="register" class="flex flex-col gap-4">
                <input v-model="form.name" placeholder="Name" class="input-field"/>
                <input v-model="form.email" type="email" placeholder="E-Mail" class="input-field"/>
                <input v-model="form.password" type="password" placeholder="Passwort" class="input-field"/>
                <input v-model="form.password_confirmation" type="password" placeholder="Passwort bestätigen"
                       class="input-field"/>
                <input v-model="form.title" placeholder="Album Name" class="input-field"/>
                <input v-model="form.pin" placeholder="PIN" class="input-field"/>

                <button type="submit" class="btn-primary w-full">Registrieren</button>
                <p v-if="Object.keys(errors).length" class="error-text mt-2 text-center">{{ formatErrors }}</p>
            </form>

            <p class="mt-6 text-base text-center">
                Bereits registriert?
                <router-link to="/login" class="underline hover:text-yellow-300 transition duration-300">
                    Zum Login
                </router-link>
            </p>
        </div>

        <div class="hidden md:flex flex-col gap-8 absolute right-0 top-1/4 z-0">
            <img src="/assets/images/img3.jpg" alt="Couple 3"
                 class="w-56 md:w-72 animate-float-variation2 rounded-xl shadow-lg"/>
            <img src="/assets/images/img4.jpg" alt="Couple 4"
                 class="w-64 md:w-80 animate-float-variation3 rounded-xl shadow-lg"/>
        </div>
    </section>
    <FeaturesSection class="mt-24"/>
    <PricingSection class="mt-16"/>
    <ContactSection class="mt-16"/>
</template>

<script>
import Register from "./Auth/Register.vue";
import FeaturesSection from "./FeaturesSection.vue";
import PricingSection from "./PricingSection.vue";
import ContactSection from "./ContactSection.vue";
import axios from 'axios';
import AlbumZipDownload from "./AlbumZipDownload.vue";

export default {
    components: {AlbumZipDownload, Register, FeaturesSection, PricingSection, ContactSection},
    data() {
        return {
            form: {
                name: '',
                email: '',
                password: '',
                password_confirmation: '',
                title: '',
                pin: ''
            },
            errors: {}
        };
    },
    computed: {
        formatErrors() {
            return Object.values(this.errors).flat().join(', ');
        }
    },
    methods: {
        async register() {
            this.errors = {};
            try {
                await axios.get('/sanctum/csrf-cookie', {withCredentials: true});
                await axios.post('/api/register', this.form);
                this.$router.push('/dashboard');
            } catch (err) {
                console.error('Axios error:', err);
                if (err.response && err.response.status === 422) {
                    const e = err.response.data.errors;
                    this.errors = (e && typeof e === "object" && !Array.isArray(e)) ? e : {general: ["Ungültige Eingabedaten."]};
                    this.form.password = "";
                    this.form.password_confirmation = "";
                } else {
                    alert("Keine Antwort vom Server erhalten oder anderer Fehler.");
                }
            }
        }
    }
};
</script>

<style scoped>
/* Floating animations for images */
@keyframes float-slow {
    0% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-15px) rotate(2deg);
    }
    100% {
        transform: translateY(0) rotate(0deg);
    }
}

@keyframes float-slower {
    0% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-8px) rotate(-1deg);
    }
    100% {
        transform: translateY(0) rotate(0deg);
    }
}

@keyframes float-variation1 {
    0% {
        transform: translateY(0) rotate(1deg);
    }
    50% {
        transform: translateY(-12px) rotate(-2deg);
    }
    100% {
        transform: translateY(0) rotate(1deg);
    }
}

@keyframes float-variation2 {
    0% {
        transform: translateY(0) rotate(-1deg);
    }
    50% {
        transform: translateY(-10px) rotate(1deg);
    }
    100% {
        transform: translateY(0) rotate(-1deg);
    }
}

@keyframes float-variation3 {
    0% {
        transform: translateY(0) rotate(2deg);
    }
    50% {
        transform: translateY(-14px) rotate(-1deg);
    }
    100% {
        transform: translateY(0) rotate(2deg);
    }
}

@keyframes float-variation4 {
    0% {
        transform: translateY(0) rotate(-2deg);
    }
    50% {
        transform: translateY(-16px) rotate(2deg);
    }
    100% {
        transform: translateY(0) rotate(-2deg);
    }
}

.animate-float-slow {
    animation: float-slow 6s ease-in-out infinite;
}

.animate-float-slower {
    animation: float-slower 8s ease-in-out infinite;
}

.animate-float-variation1 {
    animation: float-variation1 7s ease-in-out infinite;
}

.animate-float-variation2 {
    animation: float-variation2 6.5s ease-in-out infinite;
}

.animate-float-variation3 {
    animation: float-variation3 7.5s ease-in-out infinite;
}

.animate-float-variation4 {
    animation: float-variation4 8s ease-in-out infinite;
}
</style>

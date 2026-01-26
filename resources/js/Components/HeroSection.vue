<template>
    <div
        class="relative text-purple-700 font-script text-center bg-gradient-to-r from-purple-200 to-purple-800-800 flex col justify-center items-center overflow-hidden" id="top">
        <h1 class="text-5xl mb-5">
            Die Foto-App für deine Hochzeitsgäste
        </h1>
        <p class="mb-10">
            Alle Bilder deiner Hochzeit einfach teilen und sichern – ganz ohne Stress.
        </p>
    </div>
    <section
        class="relative py-12 px-5 min-h-screen flex justify-center items-center overflow-hidden">
        <div class="hidden md:flex flex-col gap-8 absolute left-0 top-1/4 z-0">
            <img src="/assets/images/img1.jpg" alt="Couple 1"
                class="w-64 md:w-80 animate-float rounded-xl shadow-lg"
                style="--y:-15px; --rot:1deg; --rot2:-2deg"
            />
            <img src="/assets/images/img2.jpg" alt="Couple 2"
                class="w-64 md:w-80 animate-float rounded-xl shadow-lg"
                style="--y:-15px; --rot:1deg; --rot2:-2deg"
            />
        </div>

        <div class="bg-purple-200 shadow-xl rounded-3xl p-10 max-w-md w-full z-10 relative">
            <h1 class="font-script text-purple-700 text-5xl font-bold text-center mb-10">Registrieren</h1>

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

            <p class="text-xl text-gray-400 font-bold text-center mt-10">
                Bereits registriert?
                <router-link to="/login" class="underline hover:text-yellow-300 transition duration-300">
                    Zum Login
                </router-link>
            </p>
        </div>

        <div class="hidden md:flex flex-col gap-8 absolute right-0 top-1/4 z-0">
            <img src="/assets/images/img3.jpg" alt="Couple 3"
                class="w-64 md:w-80 animate-float rounded-xl shadow-lg"
                style="--y:-15px; --rot:1deg; --rot2:-2deg"
            />
            <img src="/assets/images/img4.jpg" alt="Couple 4"
                class="w-64 md:w-80 animate-float rounded-xl shadow-lg"
                style="--y:-15px; --rot:1deg; --rot2:-2deg"
            />

        </div>
    </section>
    <FeaturesSection id="features" class="mt-24" />
    <PricingSection id="pricing" class="mt-16"  />
    <ContactSection id="contact" class="mt-16"/>
    <a href="#top"
        class="fixed bottom-6 right-6
           w-10 h-10 rounded-full
           bg-gray-400 text-white
           flex items-center justify-center
           shadow-lg hover:bg-gray-500"
        aria-label="Back to top"
    >
        ↑
    </a>
</template>

<script>

import FeaturesSection from "./FeaturesSection.vue";
import PricingSection from "./PricingSection.vue";
import ContactSection from "./ContactSection.vue";
import AlbumZipDownload from "./AlbumZipDownload.vue";
export default {
    components: {AlbumZipDownload, FeaturesSection, PricingSection, ContactSection},
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
                await this.$axios.post('/api/register', this.form);
                this.$router.push('/dashboard');
            } catch (err) {
                console.error('Axios error:', err);
                if (err.response && err.response.status === 422) {
                    const e = err.response.data.errors;
                    if (e && typeof e === "object") {
                        this.error = Object.values(e).flat().join(", ");
                    } else {
                        this.error = "Ungültige Eingabedaten.";
                    }
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
@keyframes float {
    0%   { transform: translateY(0) rotate(var(--rot)); }
    50%  { transform: translateY(var(--y)) rotate(var(--rot2)); }
    100% { transform: translateY(0) rotate(var(--rot)); }
}

.animate-float {
    animation: float 7s ease-in-out infinite;
}
</style>

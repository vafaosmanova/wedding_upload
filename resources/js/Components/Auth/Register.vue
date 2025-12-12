<template>
    <section class="relative py-12 px-5 min-h-screen bg-violet-700 flex flex-col justify-center items-center bg-gradient-to-b from-purple-50 to-blue-50">

        <div class="absolute inset-0 pointer-events-none">
            <span class="heart animate-float absolute bg-pink-800 w-2 h-2 rounded-full top-10 left-12"></span>
            <span class="heart animate-float absolute bg-pink-200 w-2 h-2 rounded-full top-20 right-16"></span>
            <span class="heart animate-float absolute bg-pink-400 w-1.5 h-1.5 rounded-full bottom-16 left-20"></span>
            <span class="heart animate-float absolute bg-pink-300 w-1.5 h-1.5 rounded-full bottom-10 right-12"></span>
        </div>

        <div class="bg-purple-400 shadow-xl rounded-3xl p-10 max-w-xl w-full relative z-10">
            <h2 class="text-12xl text-purple-700 mb-6 font-script text-center">Registrieren</h2>

            <form @submit.prevent="register" class="flex flex-col gap-4">
                <input v-model="form.name" placeholder="Name" class="input-field" />
                <input v-model="form.email" type="email" placeholder="E-Mail" class="input-field" />
                <input v-model="form.password" type="password" placeholder="Passwort" class="input-field" />
                <input v-model="form.password_confirmation" type="password" placeholder="Passwort bestätigen" class="input-field" />
                <input v-model="form.title" placeholder="Album Name" class="input-field" />
                <input v-model="form.pin" placeholder="PIN" class="input-field" />

                <button type="submit" class="btn-primary w-full">Registrieren</button>

                <p v-if="Object.keys(errors).length" class="error-text mt-2 text-center">{{ formatErrors }}</p>
            </form>
        </div>
    </section>
</template>

<script>
import axios from 'axios';

export default {
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
/*
        async register() {
            this.errors = {};
            try {
                await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
                await axios.post('/api/register', this.form);
                this.$router.push('/dashboard');
            } catch (err) {
                console.error('Axios error:', err);
                if (err.response && err.response.status === 422) {
                    const e = err.response.data.errors;
                    this.errors = (e && typeof e === "object" && !Array.isArray(e)) ? e : { general: ["Ungültige Eingabedaten."] };
                    this.form.password = "";
                    this.form.password_confirmation = "";
                } else {
                    alert("Keine Antwort vom Server erhalten oder anderer Fehler.");
                }
            }

        }*/
    }
};
</script>

<style scoped>
.input-field {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid #ccc;
    border-radius: 12px;
    font-size: 32px;
}
.btn-primary {
    background: #9b5de5;
    color: white;
    padding: 14px;
    border-radius: 12px;
    font-weight: 600;
    transition: 0.3s;
}
.btn-primary:hover {
    background: #6a11cb;
}
.font-script {
    font-family: 'Dancing Script', cursive;
    font-size: medium;
}
.heart {
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}
@keyframes float {
    0% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-12px) rotate(2deg); }
    100% { transform: translateY(0) rotate(0deg); }
}
</style>

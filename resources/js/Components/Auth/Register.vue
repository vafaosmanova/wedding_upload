<template>
    <div class="font-lila bg-gray-100 min-h-screen flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow w-full max-w-md">
            <h2 class="text-2xl mb-4 text-purple-600 font-semibold">Registrieren</h2>

            <input
                v-model="form.name"
                placeholder="Name"
                class="input-field"
            />
            <input
                v-model="form.email"
                type="email"
                placeholder="E-Mail"
                class="input-field"
            />
            <input
                v-model="form.password"
                type="password"
                placeholder="Passwort"
                class="input-field"
            />
            <input
                v-model="form.password_confirmation"
                type="password"
                placeholder="Passwort bestätigen"
                class="input-field"
            />
            <input
                v-model="form.album_title"
                placeholder="Album Name"
                class="input-field"
            />
            <input
                v-model="form.pin"
                placeholder="PIN"
                class="input-field"
            />

            <button
                class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 mt-2 w-full"
                @click="register"
            >
                Registrieren
            </button>

            <p v-if="qrCode" class="mt-4 text-center" v-html="qrCode"></p>
            <p v-if="Object.keys(errors).length" class="text-red-500 mt-2">
                {{ formatErrors }}
            </p>
        </div>
    </div>
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
                album_title: '',
                pin: ''
            },
            qrCode: null,
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
                await axios.get('/sanctum/csrf-cookie', { withCredentials: true });

                const { data } = await axios.post(
                    '/api/register',
                    this.form
                );

                this.qrCode = data.qrCode;

                this.$router.push('/dashboard');
            } catch (err) {
                console.error('Axios error:', err);
                if (err.response) {
                    if (err.response.status === 422) {
                        this.errors = err.response.data.errors || {};
                    } else {
                        alert(`Fehler: ${err.response.status} - ${JSON.stringify(err.response.data)}`);
                    }
                } else {
                    alert('Keine Antwort vom Server erhalten.');
                }
            }
        }
    }
};
</script>

<style scoped>
.input-field {
    width: 100%;
    padding: 10px;
    margin-bottom: 8px;
    border: 1px solid #ccc;
    border-radius: 6px;
}
</style>

<template>
    <h2 class="text-2xl text-purple-600 font-semibold">Registrieren</h2>
    <div class="font-lila bg-gray-100  flex justify-center items-center">

            <form
                @submit.prevent="register"
                class="bg-gray-100 text-gray-800 rounded-2xl shadow-xl p-8 w-full max-w-sm flex flex-col gap-4"
            >
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
                    v-model="form.title"
                    placeholder="Album Name"
                    class="input-field"
                />
                <input
                    v-model="form.pin"
                    placeholder="PIN"
                    class="input-field"
                />

                <button
                    type="submit"
                    class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-xl transition duration-300 shadow-md w-full"
                >
                    Registrieren
                </button>

                <p v-if="Object.keys(errors).length" class="text-red-500 text-sm mt-2 text-center">
                    {{ formatErrors }}
                </p>
            </form>
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

                const {data} = await axios.post('/api/register', this.form);

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

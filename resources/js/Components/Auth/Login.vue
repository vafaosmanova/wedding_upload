<template>
    <div class="font-lila max-w-sm mx-auto mt-12 bg-white p-6 rounded shadow">
        <h2 class="text-2xl text-purple-600 mb-4 text-center">Login</h2>

        <input v-model="form.email" type="email" placeholder="Email" class="w-full p-2 mb-2 border rounded"/>
        <input v-model="form.password" type="password" placeholder="Password" class="w-full p-2 mb-2 border rounded"/>

        <button @click="login" class="bg-purple-600 text-white px-4 py-2 rounded w-full hover:bg-purple-700">
            Login
        </button>

        <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            form: {email: '', password: ''},
            error: ''
        };
    },
    methods: {
        async login() {
            this.error = '';
            try {
                await axios.get('/sanctum/csrf-cookie', {withCredentials: true});
                const res = await axios.post('/api/login', {
                    email: this.form.email,
                    password: this.form.password
                }, {withCredentials: true});

                if (res.data.success) {
                    console.log('Login success für: ' + res.data.success);
                    this.$router.push('/dashboard');
                } else {
                    this.error = res.data.message;
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Login fehlgeschlagen';
            }
        }
    }
};
</script>

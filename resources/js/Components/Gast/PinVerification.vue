<template>
    <div class="font-lila bg-gray-100 p-6 rounded-lg shadow max-w-sm mx-auto text-center">
        <h2 class="text-2xl mb-4 text-purple-600">PIN eingeben</h2>
        <input v-model="pin" type="text" placeholder="PIN" class="border rounded p-2 w-48 mb-4"/>
        <button @click="verifyPin" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
            Verifizieren
        </button>
        <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: ['albumId'],
    data() {
        return {
            pin: '',
            error: ''
        };
    },
    methods: {
        async verifyPin() {
            this.error = '';
            try {
                await axios.get('/sanctum/csrf-cookie', {withCredentials: true});
                const res = await axios.post(
                    `/api/guest/${this.albumId}/verify-pin`,
                    {pin: this.pin},
                    {withCredentials: true}
                );
                if (res.data.success) {
                    this.$emit('verified')
                    this.$emit('set-token', res.data.token);
                } else {
                    this.error = res.data.message || 'Falsche PIN';
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Fehler bei der PIN-Überprüfung';
            }
        }
    }
};
</script>

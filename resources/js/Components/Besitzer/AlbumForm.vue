<template>
    <div class="bg-slate-300 p-6 rounded-lg shadow mb-6">
        <h2 class="text-2xl font-semibold mb-4 text-purple-600">Neues Album erstellen</h2>

        <input v-model="title" placeholder="Album Name" class="border p-2 w-64 mb-2 text-gray-800 bg-white" />
        <input v-model="pin" placeholder="PIN" class="border p-2 w-64 mb-2 text-gray-800 bg-white" />

        <button class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700" @click="createAlbum">
            Album erstellen
        </button>

        <p v-if="message" class="text-green-600 mt-2">{{ message }}</p>
        <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            title: '',
            pin: '',
            message: '',
            error: ''
        };
    },
    methods: {
        async createAlbum() {
            this.message = '';
            this.error = '';

            try {
                await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
                const res = await axios.post('/api/albums', { title: this.title, pin: this.pin }, { withCredentials: true });

                this.message = 'Album erstellt!';
                this.$emit('albumCreated', res.data);
                this.title = '';
                this.pin = '';
            } catch (err) {
                this.error = err.response?.data?.message || 'Fehler beim Erstellen des Albums';
                console.error(err);
            }
        }
    }
};
</script>

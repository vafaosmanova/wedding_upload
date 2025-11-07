<template>
    <div>
        <h2 class="text-2xl text-purple-600 mb-4">Medien freigeben</h2>
        <div v-for="media in pendingMedia" :key="media.id" class="p-2 border mb-2 rounded flex justify-between items-center">
            <span>{{ media.name }}</span>
            <button @click="approve(media.id)" class="bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">Approve</button>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return { pendingMedia: [] };
    },
    async mounted() {
        try {
            await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
            const res = await axios.get('/api/media/pending', { withCredentials: true });
            this.pendingMedia = res.data;
        } catch (err) {
            console.error('Fehler beim Laden der Medien:', err);
        }
    },
    methods: {
        async approve(mediaId) {
            try {
                await axios.post(`/api/media/${mediaId}/approve`, {}, { withCredentials: true });
                this.pendingMedia = this.pendingMedia.filter(m => m.id !== mediaId);
            } catch (err) {
                console.error('Freigabe fehlgeschlagen:', err);
            }
        }
    }
};
</script>

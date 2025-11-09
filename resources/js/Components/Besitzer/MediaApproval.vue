<template>
    <div class="font-lila bg-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto p-6">
            <h1 class="text-4xl mb-8 text-center text-purple-600">
                Medienfreigabe
            </h1>

            <div v-if="error" class="text-red-500 mb-4">{{ error }}</div>

            <div v-if="pendingMedia.length === 0" class="text-gray-600">
                Keine Medien zur Genehmigung.
            </div>

            <div v-else class="grid grid-cols-3 gap-4">
                <div
                    v-for="media in pendingMedia"
                    :key="media.id"
                    class="border p-2 rounded"
                >
                    <img
                        v-if="media.type === 'photo'"
                        :src="getUrl(media)"
                        class="w-full h-32 object-cover rounded"
                        alt="image"
                    />
                    <video
                        v-else
                        controls
                        class="w-full h-32 rounded"
                    >
                        <source :src="getUrl(media)" :type="media.type" />
                    </video>

                    <div class="mt-2 text-center">
                        <button
                            class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700"
                            @click="approveMedia(media)"
                        >
                            Genehmigen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            pendingMedia: [],
            error: ''
        };
    },
    async mounted() {
        await this.loadPendingMedia();
    },
    methods: {
        async loadPendingMedia() {
            try {
                const res = await axios.get('/api/media/pending');
                this.pendingMedia = res.data.map(item => ({
                    ...item,
                    type: item.type === 'photo' ? 'photo' : 'video'
                }));
            } catch (err) {
                console.error(err);
                this.error = err.response?.data?.message || 'Fehler beim Laden der Medien';
            }
        },
        getUrl(media) {
            // Use Hetzner disk if stored there
            return `/storage/${media.path}`;
        },
        async approveMedia(media) {
            try {
                await axios.post(`/api/media/${media.id}/approve`, { type: media.type });
                // Remove approved media from pending list
                this.pendingMedia = this.pendingMedia.filter(m => m.id !== media.id);
            } catch (err) {
                console.error(err);
                this.error = err.response?.data?.message || 'Genehmigung fehlgeschlagen';
            }
        }
    }
};
</script>

<style>
.font-lila {
    font-family: 'Lila', sans-serif;
}
</style>

<template>
    <div class="p-4">
        <h3 class="text-lg font-semibold mb-4">Mediengalerie</h3>

        <!-- Upload -->
        <input type="file" multiple @change="handleUpload" class="mb-4" />
        <button @click="submitUpload" :disabled="!uploadFiles.length" class="bg-green-600 text-white px-4 py-2 rounded">
            Upload starten
        </button>

        <!-- Vorschau & Medien -->
        <div class="grid grid-cols-3 gap-4 mt-4" v-if="mediaList.length">
            <div v-for="m in mediaList" :key="m.id" class="border p-2 rounded">
                <img v-if="m.type === 'photo'" :src="m.url" class="w-full h-32 object-cover rounded" />
                <video v-else controls class="w-full h-32 rounded">
                    <source :src="m.url" :type="m.type" />
                </video>
                <div class="text-sm mt-1">{{ m.filename }}</div>
            </div>
        </div>

        <div v-else class="text-gray-500 mt-4">Keine Medien vorhanden.</div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: { albumId: { type: [String, Number], required: true } },
    data() {
        return {
            mediaList: [],
            uploadFiles: [],
        };
    },
    mounted() {
        this.loadMedia();
    },
    methods: {
        async loadMedia() {
            try {
                const res = await axios.get(`/api/albums/${this.albumId}/media`);
                this.mediaList = res.data.media;
            } catch (err) {
                console.error(err);
            }
        },
        handleUpload(e) {
            this.uploadFiles = Array.from(e.target.files || []);
        },
        async submitUpload() {
            if (!this.uploadFiles.length) return;

            const fd = new FormData();
            this.uploadFiles.forEach(f => fd.append(f.type.startsWith('image') ? 'photos[]' : 'videos[]', f));

            await axios.post(`/api/albums/${this.albumId}/upload`, fd, { withCredentials: true });
            this.uploadFiles = [];
            this.loadMedia();
        }
    }
};
</script>

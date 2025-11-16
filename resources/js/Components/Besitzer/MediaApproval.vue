<template>
    <div class="font-lila bg-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto p-6">
            <h1 class="text-4xl mb-8 text-center text-purple-600">Medienfreigabe</h1>

            <div v-if="error" class="text-red-500 mb-4">{{ error }}</div>

            <div v-if="pendingMedia.length === 0" class="text-gray-600 mb-4">
                Keine Medien zur Genehmigung.
            </div>

            <!-- Medien, die auf Genehmigung warten -->
            <div v-else class="grid grid-cols-3 gap-4 mb-6">
                <div v-for="media in pendingMedia" :key="media.id" class="border p-2 rounded">
                    <img
                        v-if="media.type.startsWith('image')"
                        :src="media.url"
                        alt="media"
                        class="w-full h-32 object-cover rounded"
                    />
                    <video v-else controls class="w-full h-32 rounded">
                        <source :src="media.url" :type="media.type" />
                    </video>

                    <div class="mt-2 text-center">
                        <button
                            class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700"
                            @click="approveMedia(media)"
                        >
                            Genehmigen
                        </button>
                        <button
                            class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700 ml-2"
                            @click="deleteMedia(media)"
                        >
                            Löschen
                        </button>
                    </div>
                </div>
            </div>

            <!-- ZIP Export -->
            <div v-if="pendingMedia.length > 0" class="mb-4 text-center">
                <button
                    class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 disabled:opacity-50"
                    @click="exportAlbum"
                    :disabled="exportProgress > 0 && exportProgress < 100"
                >
                    ZIP Export starten
                </button>

                <!-- Fortschrittsanzeige -->
                <div v-if="exportProgress > 0" class="mt-3 w-full bg-gray-200 h-4 rounded-full overflow-hidden">
                    <div
                        class="h-4 bg-purple-600 transition-all duration-300"
                        :style="{ width: exportProgress + '%' }"
                    ></div>
                </div>

                <p v-if="exportProgress > 0" class="text-sm text-gray-700 mt-1">
                    Exportfortschritt: {{ exportProgress }}%
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "MediaApproval",
    props: { albumId: { type: Number, required: true } },
    data() {
        return {
            pendingMedia: [],
            error: "",
            exportProgress: 0,
            exportInterval: null,
        };
    },
    async mounted() {
        await this.loadPendingMedia();
    },
    methods: {
        async loadPendingMedia() {
            try {
                const res = await axios.get(`/api/media/pending/${this.albumId}`, { withCredentials: true });
                this.pendingMedia = res.data.map(item => ({
                    ...item,
                    type: item.type.startsWith("image") || item.type === "photo" ? "image" : "video",
                }));
            } catch (err) {
                console.error(err);
                this.error = err.response?.data?.message || "Fehler beim Laden der Medien.";
            }
        },

        async approveMedia(media) {
            try {
                await axios.post(`/api/media/${media.id}/approve`, {}, { withCredentials: true });
                this.pendingMedia = this.pendingMedia.filter(m => m.id !== media.id);
            } catch (err) {
                console.error(err);
                this.error = err.response?.data?.message || "Genehmigung fehlgeschlagen.";
            }
        },

        async deleteMedia(media) {
            if (!confirm("Möchten Sie dieses Medium wirklich löschen?")) return;
            try {
                await axios.delete(`/api/media/${media.id}`, { withCredentials: true });
                this.pendingMedia = this.pendingMedia.filter(m => m.id !== media.id);
            } catch (err) {
                console.error(err);
                this.error = err.response?.data?.message || "Fehler beim Löschen.";
            }
        },

        async exportAlbum() {
            try {
                await axios.post(`/api/albums/${this.albumId}/export`, {}, { withCredentials: true });
                this.exportProgress = 0;
                this.trackExportProgress();
            } catch (err) {
                console.error(err);
                this.error = err.response?.data?.message || "Export fehlgeschlagen.";
            }
        },

        trackExportProgress() {
            if (this.exportInterval) clearInterval(this.exportInterval);
            this.exportInterval = setInterval(async () => {
                try {
                    const res = await axios.get(`/api/albums/${this.albumId}/export/progress`, {
                        withCredentials: true,
                    });
                    this.exportProgress = res.data.progress ?? 0;

                    if (this.exportProgress >= 100) {
                        clearInterval(this.exportInterval);
                        this.exportInterval = null;
                        alert("ZIP-Datei wurde erfolgreich erstellt!");
                    }
                } catch (err) {
                    console.error(err);
                    clearInterval(this.exportInterval);
                    this.exportInterval = null;
                }
            }, 1000);
        },
    },
};
</script>

<style scoped>
.font-lila {
    font-family: "Lila", sans-serif;
}
</style>

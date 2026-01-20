<template>
    <div class="font-lila bg-purple-500-100 min-h-screen">
        <div class="max-w-6xl mx-auto p-6">
            <h1 class="text-4xl mb-8 text-center text-purple-800">Medienfreigabe</h1>

            <div v-if="error" class="text-red-500 mb-4">{{ error }}</div>

            <div v-if="pendingMedia" class="grid grid-cols-3 gap-4 mb-6">
                <div v-for="media in pendingMedia" :key="media.id" class="border p-2 rounded relative"
                     :class="media.approved ? 'border-blue-600 border-4' : 'border-gray-300'">

                    <img v-if="media.type === 'image'"
                         :src="media.url" class="w-full h-32 object-cover rounded" alt="image"/>
                    <video v-else controls class="w-full h-32 rounded">
                        <source :src="media.url" :type="media.mime"/>
                    </video>

                    <div class="flex justify-between mt-2 ">
                        <button class="bg-purple-600 text-white px-2 py-1 rounded text-sm"
                                @click ="deleteMedia(media)">
                            X
                        </button>
                        <button class="bg-blue-600 text-white px-2 py-1 rounded text-sm"
                                @click="approveMedia(media)" :disabled="media.approved">
                            OK
                        </button>
                    </div>
                </div>
            </div>
            <div v-else class="text-gray-600 mb-4">
                Keine Medien zur Genehmigung.
            </div>

            <div class="mb-4 text-center">
                <button class="px-5 py-2 rounded-lg
                bg-gradient-to-r from-purple-600 to-pink-500 text-white
                hover:from-pink-500 hover:to-purple-600 hover:scale-105
                focus:outline-none focus:ring-2 focus:ring-purple-400 focus:ring-offset-2"
                        @click="exportAlbum"
                        :disabled="exportProgress > 0 && exportProgress < 100">
                    ZIP Export starten
                </button>


                <div v-if="exportProgress > 0"
                     class="mt-3 w-full max-w-md mx-auto bg-gray-200 h-4 rounded-full overflow-hidden">
                    <div class="h-4 bg-purple-600 transition-all duration-300"
                         :style="{ width: exportProgress + '%' }"></div>
                </div>


                <p v-if="exportProgress > 0" class="text-sm text-gray-700 mt-1">
                    Exportfortschritt: {{ exportProgress }}%
                </p>
            </div>


        </div>
    </div>
</template>
<script>
export default {
    name: "MediaApproval",
    props: {
        albumId: {type: Number, required: true}
    },


    data() {
        return {
            pendingMedia: [],
            error: "",
            exportProgress: 0,
            exportInterval: null,
        };
    },


    mounted() {
        this.loadPendingMedia();
    },
    watch: {
        albumId: {
            immediate: true,
            handler() {
                this.loadPendingMedia();
            }
        }
    },


    beforeUnmount() {
        if (this.exportInterval) clearInterval(this.exportInterval);
    },
    methods: {
        async loadPendingMedia() {
            try {
                const res = await this.$axios.get(`/api/media/pending/${this.albumId}`, {withCredentials: true});
                this.pendingMedia = res.data.media.map(item => ({
                    ...item,
                    mime: item.mime_type ?? "video/mp4",
                    url: `/api/media/${item.id}/stream`,
                    approved: item.approved ?? false,
                }));
            } catch (err) {
                console.error(err);
                this.error = "Fehler beim Laden der Medien.";
            }
        },
        async approveMedia(media) {
            try {
                await this.$axios.post(`/api/media/${media.id}/approve`, {}, {withCredentials: true});

                const index = this.pendingMedia.findIndex(m => m.id === media.id);
                if (index !== -1) this.pendingMedia[index].approved = true;

                this.$emit('approved', media.id);
            } catch (err) {
                console.error(err);
                this.error = "Fehler beim Genehmigen.";
            }
        },
        async deleteMedia(media) {
            try {
                await this.$axios.delete(`/api/media/${media.id}`, {withCredentials: true});
                this.pendingMedia = this.pendingMedia.filter(m => m.id !== media.id);
                this.$emit('deleted', media.id);
            } catch {
                this.error = "Fehler beim Löschen.";
            }
        },
        async exportAlbum() {
            try {
                await this.$axios.post(`/api/albums/${this.albumId}/export`, {withCredentials: true});
                this.exportProgress = 0;
                this.trackExportProgress();
            } catch (err) {
                this.error = err.response?.data?.message || "Export fehlgeschlagen.";
            }
        },
        trackExportProgress() {
            if (this.exportInterval) clearInterval(this.exportInterval);

            this.exportInterval = setInterval(async () => {
                try {
                    const res = await this.$axios.get(
                        `/api/albums/${this.albumId}/export/progress`, {withCredentials: true});
                    this.exportProgress = res.data.progress ?? 0;

                    if (this.exportProgress >= 100) {
                        clearInterval(this.exportInterval);
                        this.exportInterval = null;
                        alert("ZIP-Datei wurde erfolgreich erstellt!");
                    }
                } catch (err) {
                    console.error("Fehler beim Abfragen des Exportfortschritts:", err);
                    clearInterval(this.exportInterval);
                    this.exportInterval = null;
                }
            }, 1000);
        },

    }
}
</script>


<style scoped>
.font-lila {
    font-family: "Lila", sans-serif;
}
</style>

<template>
    <div class="font-lila bg-purple-500-100 min-h-screen">
        <div class="max-w-6xl mx-auto p-6 bg-purple-300">
            <h1 class="text-4xl mb-8 text-center text-purple-800">Medienfreigabe</h1>


            <div v-if="error" class="text-red-500 mb-4">{{ error }}</div>


            <div v-if="pendingMedia.length === 0" class="text-gray-600 mb-4">
                Keine Medien zur Genehmigung.
            </div>


            <div v-else class="grid grid-cols-3 gap-4 mb-6">
                <div v-for="media in pendingMedia" :key="media.id" class="border p-2 rounded relative"
                     :class="media.approved ? 'border-green-600 border-4' : 'border-gray-300'">


                    <div v-if="media.approved"
                         class="absolute top-1 right-1 bg-green-600 text-white rounded-full px-2 py-1 text-xs"></div>


                    <img v-if="media.type === 'image'"
                         :src="media.url" class="w-full h-32 object-cover rounded" alt="image"/>
                    <video v-else controls class="w-full h-32 rounded">
                        <source :src="media.url" :type="media.mime"/>
                    </video>


                    <div class="flex justify-between mt-2">
                        <button class="bg-red-600 text-white px-2 py-1 rounded text-sm"
                                @click="deleteMedia(media)">
                            X
                        </button>
                        <button class="bg-blue-600 text-white px-2 py-1 rounded text-sm"
                                @click="approveMedia(media)">
                            OK
                        </button>
                    </div>
                </div>
            </div>


            <div class="mb-4 text-center">
                <button class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700 disabled:opacity-50"
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


                <button v-if="exportProgress === 100" @click="downloadZip"
                        class="mt-4 inline-block bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">
                    ZIP herunterladen
                </button>
            </div>


        </div>
    </div>
</template>


<script>
import axios from "axios";


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
                await axios.get("/sanctum/csrf-cookie", {withCredentials: true});
                const res = await axios.get(`/api/media/pending/${this.albumId}`, {withCredentials: true});
                this.pendingMedia = res.data.media.map(item => ({
                    ...item,
                    mime: item.mime_type ?? "video/mp4",
                    url: `/api/media/${item.id}/stream`,
                }));
            } catch (err) {
                console.error(err);
                this.error = "Fehler beim Laden.";
            }
        },


        async approveMedia(media) {
            try {
                await axios.get("/sanctum/csrf-cookie", { withCredentials: true });

                await axios.post(`/api/media/${media.id}/approve`, {}, { withCredentials: true });

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
                await axios.get("/sanctum/csrf-cookie", {withCredentials: true});
                await axios.delete(`/api/media/${media.id}`, {withCredentials: true});
                this.pendingMedia = this.pendingMedia.filter(m => m.id !== media.id);
                this.$emit('deleted', media.id);
            } catch {
                this.error = "Fehler beim Löschen.";
            }
        },


        async exportAlbum() {
            try {
                await axios.get("/sanctum/csrf-cookie", {withCredentials: true});
                await axios.post(`/api/albums/${this.albumId}/export`, {withCredentials: true});
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
                    const config = {};


                    // CSRF nur für Owner
                    if (!this.guestToken) {
                        await axios.get("/sanctum/csrf-cookie", {withCredentials: true});
                        config.withCredentials = true;
                    } else {
                        config.headers = {"Guest-Token": this.guestToken};
                    }


                    const res = await axios.get(
                        `/api/albums/${this.albumId}/export/progress`,
                        config
                    );


                    this.exportProgress = res.data.progress ?? 0;


                    if (this.exportProgress >= 100) {
                        clearInterval(this.exportInterval);
                        this.exportInterval = null;
                        // Statt alert kann man UI-Elemente nutzen
                        alert("ZIP-Datei wurde erfolgreich erstellt!");
                    }
                } catch (err) {
                    console.error("Fehler beim Abfragen des Exportfortschritts:", err);
                    // Intervall stoppen, wenn Fehler schwerwiegend ist
                    clearInterval(this.exportInterval);
                    this.exportInterval = null;
                }
            }, 1000);
        },
        /*
                downloadZip() {
                    const endpoint = this.guestToken
                        ? `/api/albums/${this.albumId}/guest/download`
                        : `/api/albums/${this.albumId}/export/download`;

                    const config = this.guestToken
                        ? {headers: {"Guest-Token": this.guestToken}}
                        : {withCredentials: true};

                    // Für Gäste und Owner robuster Download via fetch
                    fetch(endpoint, config)
                        .then(async (response) => {
                            if (!response.ok) {
                                alert("ZIP-Datei ist nicht verfügbar.");
                                return;
                            }
                            const blob = await response.blob();
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement("a");
                            a.href = url;
                            a.download = `album_${this.albumId}.zip`;
                            a.click();
                            URL.revokeObjectURL(url);
                        })
                        .catch((err) => console.error("Fehler beim Herunterladen der ZIP-Datei:", err));
                }*/


    }
}
</script>


<style scoped>
.font-lila {
    font-family: "Lila", sans-serif;
}
</style>

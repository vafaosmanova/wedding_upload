<template>
    <div class="text-center mt-6">
        <button
            class="px-5 py-2 rounded-lg
           bg-gradient-to-r from-blue-700 to-blue-500 text-white
           hover:from-blue-500 hover:to-blue-700 hover:scale-105
           focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2"
            @click="startDownload"
            :disabled="loading"
        >
            <span v-if="!loading">ZIP herunterladen</span>
            <span v-else>Lädt...</span>
        </button>

        <p v-if="error" class="text-red-600 mt-2 text-sm">
            {{ error }}
        </p>
    </div>
</template>

<script>
export default {
    name: "AlbumZipDownload",

    props: {
        albumId: {type: Number, required: true},
        isOwner: {type: Boolean, required: true},
        guestToken: {type: String, default: null}
    },

    data() {
        return {
            loading: false,
            error: ""
        };
    },

    methods: {
        async startDownload() {
            this.error = "";
            this.loading = true;

            try {
                const endpoint = this.isOwner
                    ? `/api/albums/${this.albumId}/export/download`
                    : `/api/albums/${this.albumId}/guest/download`;

                const headers = {};
                if (!this.isOwner && this.guestToken) {
                    headers["Guest-Token"] = this.guestToken;
                }

                const response = await fetch(endpoint, {headers});

                if (!response.ok) {
                    this.error = "ZIP-Datei ist nicht verfügbar.";
                    this.loading = false;
                    return;
                }

                const blob = await response.blob();
                const url = URL.createObjectURL(blob);
                const link = document.createElement("a");

                link.href = url;
                link.download = `album_${this.albumId}.zip`;
                link.click();

                URL.revokeObjectURL(url);
            } catch (e) {
                this.error = "Fehler beim Herunterladen.";
            } finally {
                this.loading = false;
            }
        }
    }
};
</script>

<style scoped>
</style>

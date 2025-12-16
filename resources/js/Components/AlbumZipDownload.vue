<template>
    <div class="text-center mt-6">
        <button
            class="px-5 py-2 rounded-lg
                   bg-gradient-to-r from-blue-700 to-blue-500 text-white
                   hover:from-blue-500 hover:to-blue-700 hover:scale-105
                   focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2"
            @click="startDownload"
            :disabled="loading || (!isOwner && !guestToken)"
        >
            <span v-if="!loading">ZIP herunterladen</span>
            <span v-else>Lädt...</span>
        </button>

        <p v-if="error" class="text-red-600 mt-2 text-sm">{{ error }}</p>
    </div>
</template>

<script>
export default {
    name: "AlbumZipDownload",

    props: {
        albumId: { type: Number, required: true },
        isOwner: { type: Boolean, required: true },
        guestToken: { type: String, default: true}
    },

    data() {
        return {
            loading: false,
            error: ""
        };
    },

    watch: {
        guestToken(newToken) {
            if (!this.isOwner && newToken) {
                this.error = "";
            }
        }
    },

    methods: {
        async startDownload() {
            this.error = "";
            this.loading = true;
            try {
                const endpoint = this.isOwner
                    ? `/api/albums/${this.albumId}/export/download`
                    : `/api/guest/albums/${this.albumId}/guest/download`;
                const headers = {};
                if (!this.isOwner && this.guestToken) {
                    headers["Guest-Token"] = this.guestToken;
                }
                const response = await fetch(endpoint, {
                    headers,
                    credentials: this.isOwner ? "include" : undefined
                });
                if (!response.ok) {
                    if (response.status === 404) this.error = "ZIP-Datei ist noch nicht verfügbar.";
                    else if (response.status === 401 || response.status === 403) this.error = "Nicht autorisiert.";
                    else this.error = "Fehler beim Herunterladen.";
                    return;
                }
                const blob = await response.blob();
                const url = URL.createObjectURL(blob);
                const link = document.createElement("a");
                link.href = url;
                link.download = `album_${this.albumId}.zip`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            } catch (e) {
                console.error(e);
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

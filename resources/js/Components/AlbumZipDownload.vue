<template>
    <div class="text-center mt-6">
        <button
            class="px-5 py-2 rounded-lg
                   bg-gradient-to-r from-blue-700 to-blue-600 text-white
                   hover:from-blue-600 hover:to-blue-700 hover:scale-105"
            @click="startDownload"
            :disabled="loading || (!isUser && !gastToken)"
        >
            <span v-if="!loading">Album herunterladen</span>
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
        isUser: { type: Boolean, required: true },
        gastToken: { type: String, default: ""}
    },

    data() {
        return {
            loading: false,
            error: ""
        };
    },

    watch: {
        gastToken(newToken) {
            if (!this.isUser && newToken) {
                this.error = "";
            }
        }
    },

    methods: {
        async startDownload() {
            this.error = "";
            this.loading = true;
            try {
                const endpoint = this.isUser
                    ? `/api/albums/${this.albumId}/export/download`
                    : `/api/gast/albums/${this.albumId}/gast/download`;
                const headers = {};
                if (!this.isUser && this.gastToken) {
                    headers["Gast-Token"] = this.gastToken;
                }
                const response = await fetch(endpoint, {
                    headers,
                    credentials: this.isUser ? "include" : undefined
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

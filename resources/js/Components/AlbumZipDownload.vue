<template>
    <div class="text-center mt-6">
        <button
            class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 disabled:opacity-50"
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

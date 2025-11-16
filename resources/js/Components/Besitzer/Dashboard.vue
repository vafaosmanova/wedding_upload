<template>
    <section class="bg-gradient-to-r from-purple-700 to-blue-500 text-white text-center py-24 px-5">
        <div class="font-lila bg-gray-100 min-h-screen">
            <div class="max-w-6xl mx-auto p-6">
                <h1 class="text-4xl mb-8 text-center text-purple-600">Meine Alben</h1>

                <AlbumForm @albumCreated="addAlbum" />

                <AlbumsList
                    v-if="albums.length"
                    :albums="albums"
                    @aktualisiert="loadAlbums"
                    @select-album="selectAlbum"
                />

                <p v-if="error" class="text-red-500 mt-4">{{ error }}</p>

                <div v-if="selectedAlbumId" class="mt-8">
                    <h2 class="text-2xl text-purple-600 mb-4">Album: {{ selectedAlbumTitle }}</h2>

                    <MediaGallery
                        :album-id="selectedAlbumId"
                        @uploaded="onMediaUploaded"
                        ref="centralGallery"
                    />

                    <div class="mt-6">
                        <MediaApproval :album-id="selectedAlbumId" @approved="onMediaApproved" />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import axios from "axios";
import AlbumForm from "./AlbumForm.vue";
import AlbumsList from "./AlbumsList.vue";
import MediaGallery from "../Gast/MediaGallery.vue";
import MediaApproval from "./MediaApproval.vue";

export default {
    name: "Dashboard",
    components: { AlbumForm, AlbumsList, MediaGallery, MediaApproval },
    data() {
        return {
            albums: [],
            error: "",
            selectedAlbumId: null,
        };
    },
    computed: {
        selectedAlbumTitle() {
            const a = this.albums.find(x => x.id === this.selectedAlbumId);
            return a ? a.title || a.name || `#${a.id}` : "";
        }
    },
    async mounted() {
        await this.loadAlbums();
    },
    methods: {
        async loadAlbums() {
            try {
                await axios.get("/sanctum/csrf-cookie", { withCredentials: true });
                const res = await axios.get("/api/albums", { withCredentials: true });
                this.albums = Array.isArray(res.data) ? res.data : [];

                for (const album of this.albums) {
                    try {
                        const qr = await axios.get(`/api/albums/${album.id}/qrcode`, { withCredentials: true });
                        album.qrCodeSvg = qr.data.qr_code || "";
                        album.pin = qr.data.pin || "";
                        album.exportProgress = 0;
                    } catch {
                        album.qrCodeSvg = "";
                        album.pin = "";
                    }
                }
            } catch (err) {
                console.error("Fehler beim Laden der Alben:", err);
                this.error = err.response?.data?.message || "Fehler beim Laden der Alben";
            }
        },

        addAlbum(payload) {
            const { album, qr_code, pin } = payload;
            const item = {
                ...album,
                qrCodeSvg: qr_code || "",
                pin: pin || "",
                exportProgress: 0
            };
            this.albums.unshift(item);
        },

        selectAlbum(albumId) {
            this.selectedAlbumId = albumId;
            this.$nextTick(() => {
                this.$refs.centralGallery?.loadMedia?.();
            });
        },

        onMediaUploaded() {
            this.$refs.centralGallery?.loadMedia?.();
        },

        onMediaApproved() {
            this.loadAlbums();
        }
    }
};
</script>

<style scoped>
.font-lila { font-family: "Lila", sans-serif; }
</style>

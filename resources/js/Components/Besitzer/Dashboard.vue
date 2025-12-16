<template>
    <section class="relative bg-gradient-to-r from-purple-700 to-blue-500 py-16 px-6 min-h-screen flex justify-center items-start">
        <div class="w-full max-w-7xl bg-white/25 backdrop-blur-xl border border-white/30
                rounded-3xl shadow-2xl p-10 text-gray-900">
            <h1 class="text-6xl font-script text-center text-white drop-shadow-lg mb-12">
                Meine Hochzeitsalben
            </h1>

            <div class="mb-12">
                <AlbumForm @albumCreated="addAlbum" />
            </div>

            <div v-if="albums && albums.length" class="mb-16">
                <AlbumsList
                    :albums="albums"
                    @aktualisiert="loadAlbums"
                    @select-album="selectAlbum"
                />
            </div>
            <p v-if="error" class="text-red-200 text-center text-xl mt-6">
                {{ error }}
            </p>
            <div v-if="selectedAlbumId" class="mt-20">
                <h2 class="text-4xl font-script text-purple-100 text-center mb-10 drop-shadow-md">
                    Album: {{ selectedAlbumTitle }}
                </h2>
                <div class="bg-white/30 backdrop-blur-xl border border-white/20 rounded-3xl shadow-xl p-10">

                    <MediaGallery
                        :album-id="selectedAlbumId"
                        @uploaded="onMediaUploaded"
                        ref="centralGallery"
                    />
                    <AlbumZipDownload
                        :album-id="selectedAlbumId"
                        :isOwner="true"
                    />
                    <div class="flex flex-col md:flex-row gap-8 mt-12 justify-center">
                        <MediaApproval
                            :album-id="selectedAlbumId"
                            @approved="onMediaApproved"
                        />
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
import AlbumZipDownload from "../AlbumZipDownload.vue";
export default {
    name: "Dashboard",
    components: {
        AlbumZipDownload,
        AlbumForm,
        AlbumsList,
        MediaGallery,
        MediaApproval
    },
    data() {
        return {
            albums: [],
            error: "",
            selectedAlbumId: null
        };
    },
    computed: {
        selectedAlbumTitle() {
            const a = this.albums.find(x => x.id === this.selectedAlbumId);
            return a ? a.title ?? a.name ?? `#${a.id}` : "";
        }
    },
    async mounted() {
        await this.loadAlbums();
    },
    methods: {
        async loadAlbums() {
            try {
                await axios.get("/sanctum/csrf-cookie", {withCredentials: true});


                const res = await axios.get("/api/albums", {withCredentials: true});
                this.albums = Array.isArray(res.data) ? res.data : [];


                await Promise.all(this.albums.map(async album => {
                    try {
                        const qrRes = await axios.get(`/api/albums/${album.id}/qrcode`, {
                            withCredentials: true
                        });
                        album.qrCodeSvg = qrRes.data.qr_code ?? "";
                        album.pin = qrRes.data.pin ?? "";
                    } catch {
                        album.qrCodeSvg = "";
                        album.pin = "";
                    }
                    album.exportProgress = 0;
                }));
            } catch (err) {
                console.error("Fehler beim Laden der Alben:", err);
                this.error = err.response?.data?.message || "Fehler beim Laden der Alben";
            }
        },
        addAlbum(payload) {
            const {album, qr_code, pin} = payload;
            const item = {
                ...album,
                qrCodeSvg: qr_code ?? "",
                pin: pin ?? "",
                exportProgress: 0,
            };
            this.albums.unshift(item);
            this.selectedAlbumId = item.id;
        },
        selectAlbum(albumId) {
            this.selectedAlbumId = albumId;
        },
        onMediaUploaded() {
            this.newMediaAvailable = true;
        },
        onMediaApproved() {
            this.loadAlbums();
        }
    }
};
</script>
<style scoped>
</style>

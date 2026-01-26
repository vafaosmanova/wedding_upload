<template>
    <section class="min-h-screen py-12">
        <div id="top">
            <h1 class="text-6xl font-script text-center text-white drop-shadow-lg mb-2">
                Meine Hochzeitsalben
            </h1>

            <div class="mb-12">
                <AlbumForm @albumCreated="addAlbum"/>
            </div>

            <div v-if="albums && albums.length" class="mb-2">
                <AlbumsList
                    :albums="albums"
                    @aktualisiert="loadAlbums"
                    @select-album="selectAlbum"
                />
            </div>
            <p v-if="error" class="text-red-200 text-center text-xl mt-2">
                {{ error }}
            </p>
            <div v-if="selectedAlbumId">
                <h2 class="text-4xl font-script text-purple-100 text-center mb-2 drop-shadow-md">
                    Album: {{ selectedAlbumTitle }}
                </h2>
                <div>
                    <MediaGallery
                        :album-id="selectedAlbumId"
                        @uploaded="onMediaUploaded"
                        @new-media="onNewMedia"
                        ref="centralGallery"
                    />
                    <div class="md:flex-row gap-8 mt-2 justify-center">
                        <MediaApproval
                            :album-id="selectedAlbumId"
                            @approved="onMediaApproved"
                        />
                        <AlbumZipDownload
                            :album-id="selectedAlbumId"
                            :isUser="true"
                        />
                    </div>
                </div>
            </div>
        </div>
        <a href="#top"
           class="fixed bottom-6 right-6
           w-10 h-10 rounded-full
           bg-gray-400 text-white
           flex items-center justify-center
           shadow-lg hover:bg-gray-500"
           aria-label="Back to top"
        >
            ↑
        </a>
    </section>
</template>
<script>
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
                const res = await this.$axios.get("/api/albums", {withCredentials: true});
                this.albums = Array.isArray(res.data) ? res.data : [];


                await Promise.all(this.albums.map(async album => {
                    try {
                        const qrRes = await this.$axios.get(`/api/albums/${album.id}/qrcode`, {
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
            this.$nextTick(()=>{
                this.selectedAlbumId = item.id;
            });

        },
        selectAlbum(albumId) {
            this.selectedAlbumId = albumId;
        },
        onMediaUploaded() {
            this.newMediaAvailable = true;
        },
        onMediaApproved() {
            this.loadAlbums();
        },
        onNewMedia() {
            this.loadAlbums();
        }
    }
};
</script>
<style scoped>
</style>

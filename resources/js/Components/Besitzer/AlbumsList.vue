<template>
    <div>
        <div v-for="album in albums" :key="album.id" class="relative bg-white p-4 rounded-lg shadow mb-4 overflow-hidden">
            <!-- Background overlay -->
            <div class="absolute inset-0">
                <img src="/assets/images/img.png" alt="Album Background" class="w-full h-full object-cover opacity-20">
            </div>

            <div class="relative">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-xl font-semibold">{{ album.title }}</h3>
                    <span class="text-gray-500 font-mono">PIN: {{ album.pin }}</span>
                </div>

                <div v-html="album.qrCodeSvg" class="bg-white p-2 inline-block rounded mb-2"></div>

                <!-- Export progress bar -->
                <div v-if="album.exportProgress > 0" class="mb-2">
                    <div class="w-full bg-gray-200 h-4 rounded-full overflow-hidden">
                        <div class="h-4 bg-purple-600 transition-all" :style="{ width: album.exportProgress + '%' }"></div>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">{{ album.exportProgress }}%</p>
                </div>

                <!-- Owner actions -->
                <div class="flex gap-2 mt-2">
                    <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600" @click="bearbeiteAlbum(album.id)">Bearbeiten</button>
                    <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" @click="loescheAlbum(album.id)">Löschen</button>
                    <button class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700"
                            @click="exportiereAlbum(album)"
                            :disabled="album.exportProgress > 0 && album.exportProgress < 100">
                        ZIP Export
                    </button>
                </div>

                <!-- Upload Section -->
                <UploadSection
                    :album-id="album.id"
                    @uploaded="loadMedia(album)"
                />

                <!-- Media Gallery -->
                <AlbumGallery
                    :album-id="album.id"
                    :show-pending="true"
                    ref="galleryRefs"
                />

            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import UploadSection from '../Gast/UploadSection.vue';
import AlbumGallery from '../Gast/AlbumGallery.vue';

export default {
    props: {
        albums: { type: Array, default: () => [] }
    },
    components: { UploadSection, AlbumGallery },
    mounted() {
        this.loadQRCodes();
    },
    methods: {
        async loadQRCodes() {
            if (!Array.isArray(this.albums) || this.albums.length === 0) return;

            for (let album of this.albums) {
                try {
                    const res = await axios.get(`/api/albums/${album.id}/qrcode`, { withCredentials: true });
                    album.qrCodeSvg = res.data.qr_code;
                    album.pin = res.data.pin;
                    album.exportProgress = 0;

                    // Initial media load
                    await this.loadMedia(album);
                } catch (err) {
                    console.error('Fehler beim Laden des QR-Codes:', err);
                }
            }
        },

        async loadMedia(album) {
            try {
                const res = await axios.get(`/api/media`, { withCredentials: true, params: { album_id: album.id } });
                // Update gallery reference
                const gallery = this.$refs.galleryRefs;
                if (gallery) {
                    const g = Array.isArray(gallery) ? gallery.find(ref => ref.albumId === album.id) : gallery;
                    if (g) {
                        g.mediaList = res.data.photos.concat(res.data.videos).map(item => ({
                            ...item,
                            type: item.type === 'image' ? 'image/' + item.filename.split('.').pop().toLowerCase()
                                : 'video/' + item.filename.split('.').pop().toLowerCase()
                        }));
                    }
                }
            } catch (err) {
                console.error('Fehler beim Laden der Medien:', err);
            }
        },

        async bearbeiteAlbum(albumId) {
            const neuerTitle = prompt('Neuer Albumname (leer lassen, wenn unverändert):');
            const neuerPin = prompt('Neue PIN (leer lassen, wenn unverändert):');

            if (!neuerTitle && !neuerPin) return;

            const daten = {};
            if (neuerTitle) daten.title = neuerTitle;
            if (neuerPin) daten.pin = neuerPin;

            try {
                await axios.put(`/api/albums/${albumId}`, daten, { withCredentials: true });
                this.$emit('aktualisiert');
            } catch (err) {
                console.error('Fehler beim Bearbeiten:', err);
            }
        },

        async loescheAlbum(albumId) {
            if (!confirm('Möchten Sie dieses Album wirklich löschen?')) return;

            try {
                await axios.delete(`/api/albums/${albumId}`, { withCredentials: true });
                this.$emit('aktualisiert');
            } catch (err) {
                console.error('Fehler beim Löschen:', err);
            }
        },

        async exportiereAlbum(album) {
            try {
                await axios.post(`/api/albums/${album.id}/export`, {}, { withCredentials: true });
                this.trackExportProgress(album);
            } catch (err) {
                console.error('Fehler beim Exportieren:', err);
            }
        },

        trackExportProgress(album) {
            album.exportProgress = 0;
            const intervalId = setInterval(async () => {
                try {
                    const res = await axios.get(`/api/albums/${album.id}/progress`, { withCredentials: true });
                    album.exportProgress = res.data.progress;
                    if (album.exportProgress >= 100) clearInterval(intervalId);
                } catch (err) {
                    console.error('Fehler beim Überwachen des Exports:', err);
                }
            }, 1000);
        }
    }
};
</script>

<template>
    <div>
        <div v-for="album in alben" :key="album.id" class="relative bg-white p-4 rounded-lg shadow mb-4 overflow-hidden">
            <!-- Фоновая картинка с прозрачностью -->
            <div class="absolute inset-0">
                <img src="/assets/images/img.png" alt="Album Background" class="w-full h-full object-cover opacity-20">
            </div>

            <!-- Контент поверх фона -->
            <div class="relative">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-xl font-semibold">{{ album.title }}</h3>
                    <span class="text-gray-500 font-mono">PIN: {{ album.pin }}</span>
                </div>

                <!-- QR-код -->
                <div v-html="album.qrCodeSvg" class="bg-white p-2 inline-block rounded mb-2"></div>

                <!-- Прогресс экспорта -->
                <div v-if="album.exportProgress > 0" class="mb-2">
                    <div class="w-full bg-gray-200 h-4 rounded-full overflow-hidden">
                        <div class="h-4 bg-purple-600 transition-all" :style="{ width: album.exportProgress + '%' }"></div>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">{{ album.exportProgress }}%</p>
                </div>

                <!-- Кнопки -->
                <div class="flex gap-2 mt-2">
                    <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600" @click="bearbeiteAlbum(album.id)">Bearbeiten</button>
                    <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" @click="loescheAlbum(album.id)">Löschen</button>
                    <button class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700" @click="exportiereAlbum(album)" :disabled="album.exportProgress > 0 && album.exportProgress < 100">ZIP Export</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        alben: { type: Array, default: () => [] }
    },
    async mounted() {
        // Загружаем QR-коды и PIN для каждого альбома владельца
        await this.loadQRCodes();
    },
    methods: {
        async loadQRCodes() {
            if (!Array.isArray(this.alben) || this.alben.length === 0) return;

            for (let album of this.alben) {
                try {
                    const res = await axios.get(`/api/albums/${album.id}/qrcode`, { withCredentials: true });
                    album.qrCodeSvg = res.data.qrCode;
                    album.pin = res.data.pin;
                    album.exportProgress = 0;
                } catch (err) {
                    console.error('Fehler beim Laden des QR-Codes:', err);
                }
            }
        },

        async bearbeiteAlbum(albumId) {
            const neuerTitle = prompt('Neuer Albumname:');
            const neuerPin = prompt('Neue PIN:');
            if (!neuerTitle || !neuerPin) return;

            try {
                await axios.put(`/api/albums/${albumId}`, { title: neuerTitle, pin: neuerPin }, { withCredentials: true });
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

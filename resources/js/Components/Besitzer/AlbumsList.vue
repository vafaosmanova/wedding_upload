<template>
    <div>
        <div v-for="album in albums" :key="album.id" class="relative bg-slate-300 p-6 rounded-lg shadow mb-6 overflow-hidden">
            <div class="absolute inset-0">
                <img src="/assets/images/img.png" alt="Album Background" class="w-full h-full object-cover opacity-20" />
            </div>

            <div class="relative">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-xl font-semibold">{{ album.title || album.name }}</h3>
                    <span class="text-gray-500 font-mono">PIN: {{ album.pin }}</span>
                </div>

                <div v-html="album.qrCodeSvg" class="bg-white p-2 inline-block rounded mb-2"></div>

                <div class="flex gap-2 mt-2">
                    <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600" @click="bearbeiteAlbum(album.id)">Bearbeiten</button>
                    <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" @click="loescheAlbum(album.id)">Löschen</button>
                    <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" @click="$emit('select-album', album.id)">Medien anzeigen</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "AlbumsList",
    props: { albums: { type: Array, default: () => [] } },
    methods: {
        async bearbeiteAlbum(albumId) {
            const neuerTitle = prompt("Neuer Albumname (leer lassen, wenn unverändert):");
            const neuerPin = prompt("Neue PIN (leer lassen, wenn unverändert):");
            if (!neuerTitle && !neuerPin) return;

            const daten = {};
            if (neuerTitle) daten.title = neuerTitle;
            if (neuerPin) daten.pin = neuerPin;

            try {
                await axios.put(`/api/albums/${albumId}`, daten, { withCredentials: true });
                this.$emit("aktualisiert"); // tell parent to reload albums
            } catch (err) {
                console.error("Fehler beim Bearbeiten:", err);
            }
        },

        async loescheAlbum(albumId) {
            if (!confirm("Möchten Sie dieses Album wirklich löschen?")) return;
            try {
                await axios.delete(`/api/albums/${albumId}`, { withCredentials: true });
                this.$emit("aktualisiert"); // tell parent to reload albums
            } catch (err) {
                console.error("Fehler beim Löschen:", err);
            }
        }
    }
};
</script>

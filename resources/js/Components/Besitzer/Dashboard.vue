<template>
    <div class="font-lila bg-gray-100 min-h-screen">

        <div class="max-w-6xl mx-auto p-6">
            <h1 class="text-4xl mb-8 text-center text-purple-600">Meine Alben</h1>

            <AlbumForm @albumCreated="addAlbum" />

            <AlbumsList
                v-if="albums.length"
                :albums="albums"
                @aktualisiert="loadAlbums"
            />
            <UploadSection album-id="album.id"/>

            <p v-if="error" class="text-red-500 mt-4">{{ error }}</p>
            <p v-else-if="!albums.length" class="text-gray-500 mt-4">Keine Alben gefunden.</p>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import AlbumForm from './AlbumForm.vue';
import AlbumsList from './AlbumsList.vue';
import UploadSection from "../Gast/UploadSection.vue";

export default {
    components: { AlbumForm, AlbumsList, UploadSection },
    data() {
        return {
            albums: [],
            error: ''
        };
    },
    async mounted() {
        await this.loadAlbums();
    },
    methods: {
        async loadAlbums() {
            try {
                await axios.get('/api/test');
                await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
                const res = await axios.get('/api/albums');
                this.albums = Array.isArray(res.data) ? res.data : [];
            } catch (err) {
                console.error('Fehler beim Laden der Alben:', err);
                this.error = err.response?.data?.message || 'Fehler beim Laden der Alben';
            }
        },
        addAlbum(newAlbum) {
            this.albums.unshift(newAlbum);
        }
    }
};
</script>

<style scoped>
.font-lila {
    font-family: 'Lila', sans-serif;
}
</style>

<template>
    <div class="font-lila bg-gray-100 min-h-screen">
        <div class="max-w-6xl mx-auto p-6">
            <h1 class="text-4xl mb-8 text-center text-purple-600">
                {{ album.title || 'Album' }}
            </h1>

            <!-- PIN Verification -->
            <PinVerification
                v-if="!verified"
                :album-id="albumId"
                @verified="handleVerified"
                @set-token="setGuestToken"
                class="max-w-sm mx-auto"
            />

            <!-- Upload & Gallery for verified guests -->
            <div v-else>
                <UploadSection :album-id="albumId" :guest-token="guestToken" />
                <AlbumGallery :album-id="albumId" :guest-token="guestToken" />
            </div>

            <p v-if="error" class="text-red-500 mt-4">{{ error }}</p>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import PinVerification from './PinVerification.vue';
import UploadSection from './UploadSection.vue';
import AlbumGallery from './AlbumGallery.vue';

export default {
    components: { PinVerification, UploadSection, AlbumGallery },
    props: ['albumId'], // согласовано с маршрутом :album_id
    data() {
        return {
            album: {},
            verified: false,
            guestToken: null,
            error: ''
        };
    },
    async mounted() {
        try {
            await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
            const res = await axios.get(`/api/guest/${this.albumId}`);
            this.album = res.data;
        } catch (err) {
            console.error(err);
            this.error = err.response?.data?.message || 'Album nicht gefunden.';
        }
    },
    methods: {
        handleVerified() {
            this.verified = true;
        },
        setGuestToken(token) {
            this.guestToken = token;
        }
    }
};
</script>

<style lang="css">
.font-lila {
    font-family: 'Lila', sans-serif;
}
</style>

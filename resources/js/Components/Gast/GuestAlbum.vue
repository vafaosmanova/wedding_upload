<template>
    <section class="relative bg-gradient-to-r from-purple-700 to-blue-500 py-20 px-6 min-h-screen flex justify-center items-start">
        <div class="w-full max-w-5xl bg-white/20 backdrop-blur-xl border border-white/30
                    rounded-3xl shadow-2xl p-10 text-gray-900">
            <PinVerification
                v-if="!pinVerified"
                :album-id="albumId"
                @verified="onPinVerified"
            />
            <div v-else>
                <h1 class="text-6xl font-script text-center text-white drop-shadow-lg mb-12">
                    Gästebereich – Album {{ albumId }}
                </h1>
                <div class="bg-white/30 backdrop-blur-xl border border-white/20 rounded-3xl shadow-xl p-10">

                    <MediaGallery
                        ref="gallery"
                        :album-id="albumId"
                        :guest-token="guestToken"
                        @uploaded="onGuestUpload"
                        @new-media="onNewMedia"
                    />

                    <div class="mt-10 flex justify-center">
                        <AlbumZipDownload
                            :album-id="albumId"
                            :isOwner="false"
                            :guestToken="guestToken"
                        />
                    </div>

                </div>

            </div>
        </div>

    </section>
</template>

<script>
import PinVerification from './PinVerification.vue';
import MediaGallery from "./MediaGallery.vue";
import AlbumZipDownload from "../AlbumZipDownload.vue";

export default {
    props: { albumId: { type: [String, Number], required: true } },
    components: { MediaGallery, PinVerification, AlbumZipDownload },
    data() {
        return {
            pinVerified: false,
            guestToken: null,
            newMediaAvailable: false
        };
    },
    methods: {
        onPinVerified(token) {
            this.pinVerified = true;
            this.guestToken = token;
            this.$nextTick(() => this.refreshGallery());
        },
        onGuestUpload() {
            this.refreshGallery();
        },
        onNewMedia() {
            this.newMediaAvailable = true;
        },
        refreshGallery() {
            this.$refs.gallery?.refreshGallery?.();
            this.newMediaAvailable = false;
        }
    }
};
</script>

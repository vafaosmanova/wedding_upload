<template>
    <section class="min-h-screen">

            <div class="mt-20" id="top">
                <PinVerification
                    v-if="!pinVerified"
                    :album-id="albumId"
                    @verified="onPinVerified"
                />
                <div v-else>
                    <h1 class="text-6xl font-script text-center text-white drop-shadow-lg mb-12">
                        Album: Album {{ albumId }}
                    </h1>

                    <div class="bg-white/30 backdrop-blur-xl border border-white/20 rounded-3xl shadow-xl p-10">

                        <MediaGallery
                            ref="gallery"
                            :album-id="albumId"
                            :gast-token="gastToken"
                            @uploaded="onGastUpload"
                            @new-media="onNewMedia"
                        />

                        <div class="mt-10 flex justify-center">
                            <AlbumZipDownload
                                :album-id="albumId"
                                :isUser="false"
                                :gastToken="gastToken"
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
import PinVerification from './PinVerification.vue';
import MediaGallery from "./MediaGallery.vue";
import AlbumZipDownload from "../AlbumZipDownload.vue";

export default {
    props: {albumId: {type: [String, Number], required: true}},
    components: {MediaGallery, PinVerification, AlbumZipDownload},
    data() {
        return {
            pinVerified: false,
            gastToken: null,
            newMediaAvailable: false
        };
    },
    methods: {
        onPinVerified(token) {
            this.pinVerified = true;
            this.gastToken = token;
            this.$nextTick(() => this.refreshGallery());
        },
        onGastUpload() {
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

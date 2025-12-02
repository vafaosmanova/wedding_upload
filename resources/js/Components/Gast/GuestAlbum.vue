<template>
    <section class="bg-gradient-to-r from-purple-700 to-blue-500 text-white text-center py-24 px-5">
        <div class="font-lila bg-gray-300 min-h-screen">

            <PinVerification
                v-if="!pinVerified"
                :album-id="albumId"
                @verified="onPinVerified"
            />
            <div v-else class="max-w-6xl mx-auto p-6">
                <h1 class="text-4xl mb-8 text-center text-purple-600">
                    Album #{{ albumId }}
                </h1>
                <div class="gap-2 mt-2">
                    <MediaGallery
                        :album-id="albumId"
                        :guest-token="guestToken"
                        ref="gallery"
                        @uploaded="refreshGallery"
                    />
                </div>
                <div class="relative bg-slate-100 p-6 rounded-lg shadow mt-10 overflow-hidden">

                    <div class="absolute inset-0">
                        <img
                            src="/assets/images/img.png"
                            alt="Album Background"
                            class="w-full h-full object-cover opacity-20"
                        />
                    </div>

                    <div class="relative">

                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-xl font-semibold">
                                Album #{{ albumId }}
                            </h3>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </section>
</template>
<script>
import PinVerification from './PinVerification.vue';
import MediaGallery from "./MediaGallery.vue";

export default {
    props: {
        albumId: {type: [String, Number], required: true}
    },
    components: {
        MediaGallery,
        PinVerification,
    },
    data() {
        return {
            pinVerified: false,
            guestToken: null
        };
    },
    methods: {
        onPinVerified(token) {
            this.pinVerified = true;
            this.guestToken = token;

            this.$nextTick(() => this.refreshGallery());
        },

        refreshGallery() {
            const g = this.$refs.gallery;
            if (g && typeof g.loadMedia === "function") {
                g.loadMedia();
            }
        },
        onMediaApproved() {
            this.refreshGallery();
        }
    }

};
</script>
<style scoped>
.font-lila {
    font-family: "Lila", sans-serif;
}
</style>

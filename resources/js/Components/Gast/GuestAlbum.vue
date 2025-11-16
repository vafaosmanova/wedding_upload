<template>
    <section class="bg-gradient-to-r from-purple-700 to-blue-500 text-center py-24 px-5">
        <div class="font-lila bg-gray-100">
        <PinVerification
            v-if="!pinVerified"
            :album-id="albumId"
            @verified="onPinVerified"
        />

        <!-- Once verified, show uploads and gallery -->
        <div v-else>

        </div>
        </div>
    </section>
</template>

<script>
import PinVerification from './PinVerification.vue';


export default {
    props: {
        albumId: { type: [String, Number], required: true }
    },
    components: { PinVerification },
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

            // Load gallery initially
            this.$nextTick(() => this.refreshGallery());
        },

        refreshGallery() {
            const gallery = this.$refs.gallery;
            if (gallery && gallery.loadMedia) {
                gallery.loadMedia();
            }
        },

        // Add this method to handle media approval events
        onMediaApproved() {
            this.refreshGallery();
        }
    }

};
</script>

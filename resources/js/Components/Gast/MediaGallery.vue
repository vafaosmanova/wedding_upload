<template>
    <div class="p-4 font-lila">
        <h3 class="text-2xl text-purple-600 mb-4">Mediengalerie</h3>

        <input
            type="file"
            id="mediaFiles"
            name="mediaFiles"
            multiple
            @change="handleUpload"
            class="mb-4 border p-2 rounded"
        />

        <button
            @click="submitUpload"
            :disabled="!uploadFiles.length"
            class="bg-green-600 text-white px-4 py-2 rounded disabled:opacity-50"
        >
            Hochladen
        </button>

        <div class="grid grid-cols-3 gap-4 mt-4" v-if="mediaList.length">
            <div v-for="m in mediaList" :key="m.id" class="border p-2 rounded relative"
                 :class="m.approved ? 'border-green-600 border-4' : 'border-gray-300'">
                <div v-if="m.approved"
                     class="absolute top-1 right-1 bg-green-600 text-white rounded-full px-2 py-1 text-xs">
                </div>
                <img
                    v-if="m.type === 'image'"
                    :src="m.url"
                    class="w-full h-32 object-cover rounded"
                    alt="image"
                />

                <video v-else controls class="w-full h-32 rounded">
                    <source :src="m.url" :type="m.mime"/>
                </video>

            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: {
        albumId: {type: [String, Number], required: true},
        guestToken: {type: String, default: null}
    },

    data() {
        return {
            mediaList: [],
            uploadFiles: []
        };
    },

    computed: {
        mediaEndpoint() {
            return this.guestToken
                ? `/api/guest/${this.albumId}/media`
                : `/api/albums/${this.albumId}/media`;
        },

        uploadEndpoint() {
            return this.guestToken
                ? `/api/guest/${this.albumId}/upload`
                : `/api/albums/${this.albumId}/upload`;
        }
    },

    mounted() {
        this.loadMedia();
    },

    methods: {
        async loadMedia() {
            try {
                const config = {
                    headers: this.guestToken ? {"Guest-Token": this.guestToken} : {}
                };

                const res = await axios.get(this.mediaEndpoint, config);
                this.mediaList = res.data.media.map(item => ({
                    ...item,
                    approved: item.approved ?? false,
                    mime: item.mime_type,
                    url: `/api/media/${item.id}/stream`
                }));
            } catch (error) {
                console.error("Fehler beim Laden der Medien:", error);
            }
        },

        handleUpload(e) {
            this.uploadFiles = Array.from(e.target.files || []);
        },

        async submitUpload() {
            if (!this.uploadFiles.length) return;
            const fd = new FormData();
            this.uploadFiles.forEach((file) => {
                if (file.type.startsWith("image")) {
                    fd.append("photos[]", file);
                } else {
                    fd.append("videos[]", file);
                }
            });
            try {
                const config = {
                    headers: this.guestToken ? {"Guest-Token": this.guestToken} : {}
                };

                await axios.post(this.uploadEndpoint, fd, config);
                await this.loadMedia();
                this.$emit('uploaded');

            } catch (error) {
                console.error("Fehler beim Upload:", error);
            }
        },

    }
};
</script>
<style scoped>
.font-lila {
    font-family: "Lila", sans-serif;
}
</style>

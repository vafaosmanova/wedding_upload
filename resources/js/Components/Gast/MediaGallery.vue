<template>
    <section class="py-12 px-5 justify-center items-center">
        <button
            @click="triggerFileDialog"
            class="px-5 py-2 rounded-lg m-1
           bg-gradient-to-r from-purple-600 to-pink-600 text-white
           hover:from-pink-600 hover:to-purple-600 hover:scale-105
           focus:outline-none focus:ring-2 focus:ring-purple-400 focus:ring-offset-2"
        >
            Photo Hochladen
        </button>
        <button
            v-if="newMediaAvailable"
            @click="refreshGallery"
            class="px-5 py-2 rounded-lg m-1
           bg-gradient-to-r from-blue-700 to-blue-600 text-white
           hover:from-blue-600 hover:to-blue-700 hover:scale-105
           focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2"
        >
            Liste aktualisieren
            <span class="ml-2 text-xs bg-white text-blue-500 px-1 rounded-full">neu</span>
        </button>
        <input
            type="file"
            multiple
            id="hiddenFileInput"
            name="mediaFiles"
            @change="onFilesSelected"
            class="hidden"
        />
        <div class="grid grid-cols-3 gap-4 mt-4" v-if="mediaList.length">
            <div
                v-for="m in mediaList"
                :key="m.id"
            >
                <img
                    v-if="m.type === 'image'"
                    :src="m.url"
                    class="w-full h-64 object-cover rounded cursor-pointer"
                    alt="image"
                    @click="openModal(m.url)"
                />
                <video v-else controls class="w-full h-32 rounded">
                    <source :src="m.url" :type="m.mime"/>
                </video>
            </div>
        </div>
            <div
                v-if="modalUrl"
                class="fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center z-50"
                @click="modalUrl=null">
                <img
                    :src="modalUrl"
                    class="max-w-[90vw] max-h-[90vh] rounded shadow-lg"
                    @click.stop
                />
        </div>
    </section>
</template>
<script>
export default {
    props: {
        albumId: {type: [String, Number], required: true},
        gastToken: {type: String, default: null}
    },
    data() {
        return {
            mediaList: [],
            uploadFiles: [],
            newMediaAvailable: false,
            modalUrl: null,
        };
    },
    computed: {
        mediaEndpoint() {
            return this.gastToken
                ? `/api/gast/${this.albumId}/media`
                : `/api/albums/${this.albumId}/media`;
        },
        uploadEndpoint() {
            return this.gastToken
                ? `/api/gast/${this.albumId}/upload`
                : `/api/albums/${this.albumId}/upload`;
        }
    },
    mounted() {
        this.loadMedia();
    },
    watch: {
        albumId: {
            immediate: true,
            handler() {
                this.loadMedia();
            }
        }
    },
    methods: {
        triggerFileDialog() {
            document.getElementById("hiddenFileInput").click();
        },
        async onFilesSelected(event) {
            this.uploadFiles = Array.from(event.target.files || []);
            if (!this.uploadFiles.length) return;
            const confirmed = confirm("Upload starten?");
            if (confirmed) {
                await this.submitUpload();
            } else {
                event.target.value = "";
                this.uploadFiles = [];
            }
        },
        async loadMedia() {
            if (!this.albumId) return;
            try {
                const config = this.gastToken ? {headers: {"Gast-Token": this.gastToken}} : {};
                const res = await this.$axios.get(this.mediaEndpoint, config);

                this.mediaList = res.data.media.map(item => ({
                    ...item,
                    approved: item.approved ?? false,
                    mime: item.mime_type ?? "video/mp4",
                    url: `/api/media/${item.id}/stream`,
                    type: item.type ?? (item.mime_type?.startsWith("image") ? "image" : "video")
                }));
            } catch (err) {
                console.error("Fehler beim Laden der Medien:", err);
            }
            this.newMediaAvailable = true;
            this.$emit("new-media");
        },
        async submitUpload() {
            const fd = new FormData();
            this.uploadFiles.forEach(file => {
                if (file.type.startsWith("image")) fd.append("photos[]", file);
                else fd.append("videos[]", file);
            });
            try {
                const config = this.gastToken ? {headers: {"Gast-Token": this.gastToken}} : {};
                await this.$axios.post(this.uploadEndpoint, fd, config);
                await this.loadMedia();
                this.$emit("uploaded");
                this.uploadFiles = [];
                document.getElementById("hiddenFileInput").value = "";
            } catch (err) {
                console.error("Fehler beim Upload:", err);
            }
        },
        refreshGallery() {
            this.loadMedia();
            this.newMediaAvailable = false;
        },
        openModal(url) {
            this.modalUrl = url;
        },
        closeModal() {
            this.modalUrl = null;
        }
    }
};
</script>
<style scoped>
</style>

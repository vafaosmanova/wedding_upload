<template>
    <div class="mt-6">
        <h3 class="text-xl font-semibold mb-2 text-purple-600">Fotos/Videos hochladen</h3>
        <input type="file" multiple @change="handleFiles" accept="image/*,video/*"/>
        <button
            class="bg-purple-600 text-white px-4 py-2 rounded mt-2 hover:bg-purple-700"
            @click="uploadFiles"
            :disabled="!files.length"
        >
            Upload starten
        </button>

        <div v-if="uploadProgress > 0" class="w-full bg-gray-200 rounded h-4 mt-2">
            <div class="bg-purple-600 h-4 rounded" :style="{ width: uploadProgress + '%' }"></div>
        </div>

        <p v-if="message" class="text-green-600 mt-2">{{ message }}</p>
        <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
    </div>
</template>

<script>
import axios from "axios";

export default {
    props: ["album_id"],
    data() {
        return {
            files: [],
            message: "",
            error: "",
            uploadProgress: 0
        };
    },
    methods: {
        handleFiles(event) {
            this.files = Array.from(event.target.files).map(file => {
                file.preview = URL.createObjectURL(file);
                return file;
            });
            this.message = "";
            this.error = "";
        },
        async uploadFiles() {
            if (!this.files.length) return;

            this.message = "";
            this.error = "";
            this.uploadProgress = 0;

            const formData = new FormData();
            this.files.forEach(file => {
                if (file.type.startsWith("image/")) {
                    formData.append("photos[]", file);
                } else if (file.type.startsWith("video/"))  {
                    formData.append("videos[]", file);
                }
            });

            try {
                await axios.post(`/api/guest/${this.album_id}/upload`, formData, {
                    withCredentials: true,
                    headers: {"Content-Type": "multipart/form-data"},
                    onUploadProgress: progressEvent => {
                        this.uploadProgress = Math.round(
                            (progressEvent.loaded * 100) / progressEvent.total
                        );
                    }
                });
                this.message = "Upload erfolgreich!";
                this.files.forEach(file => URL.revokeObjectURL(file.preview));
                this.files = [];
                this.uploadProgress = 0;
            } catch (err) {
                this.error = err.response?.data?.message || "Upload fehlgeschlagen";
                this.uploadProgress = 0;
            }
        }
    }
};
</script>

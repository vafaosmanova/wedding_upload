<template>
    <div class="mt-6">
        <h3 class="text-xl font-semibold mb-2 text-purple-600">Fotos/Videos hochladen</h3>

        <!-- File input -->
        <input type="file" multiple @change="handleFiles" accept="image/*,video/*" />

        <!-- Selected file previews -->
        <div v-if="previews.length" class="mt-4 grid grid-cols-4 gap-2">
            <div v-for="(file, index) in previews" :key="index" class="border p-1 rounded">
                <img v-if="file.type.startsWith('image/')" :src="file.preview" class="w-full h-24 object-cover rounded" alt ="image" />
                <video v-else controls class="w-full h-24 rounded">
                    <source :src="file.preview" :type="file.type" />
                </video>
            </div>
        </div>

        <!-- Upload button -->
        <button
            class="bg-purple-600 text-white px-4 py-2 rounded mt-2 hover:bg-purple-700"
            @click="uploadFiles"
            :disabled="!files.length || uploading"
        >
            {{ uploading ? 'Upload läuft...' : 'Upload starten' }}
        </button>

        <!-- Progress bar -->
        <div v-if="uploadProgress > 0" class="w-full bg-gray-200 rounded h-4 mt-2">
            <div class="bg-purple-600 h-4 rounded transition-all" :style="{ width: uploadProgress + '%' }"></div>
        </div>

        <!-- Messages -->
        <p v-if="message" class="text-green-600 mt-2">{{ message }}</p>
        <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        albumId: { type: [String, Number], required: true },
        guestToken: { type: String, default: null }
    },
    data() {
        return {
            files: [],
            previews: [],
            uploading: false,
            uploadProgress: 0,
            message: '',
            error: ''
        };
    },
    methods: {
        handleFiles(event) {
            this.files = Array.from(event.target.files);
            this.previews = this.files.map(file => {
                return {
                    ...file,
                    preview: URL.createObjectURL(file)
                };
            });
            this.message = '';
            this.error = '';
        },
        async uploadFiles() {
            if (!this.files.length) return;

            this.uploading = true;
            this.uploadProgress = 0;
            this.message = '';
            this.error = '';

            const formData = new FormData();
            this.files.forEach(file => {
                if (file.type.startsWith('image/')) formData.append('photos[]', file);
                else if (file.type.startsWith('video/')) formData.append('videos[]', file);
            });

            try {
                const headers = { 'Content-Type': 'multipart/form-data' };
                if (this.guestToken) headers['X-Gast-Token'] = this.guestToken;

                await axios.post(`/api/${this.guestToken ? 'guest' : 'albums'}/${this.albumId}/upload`, formData, {
                    withCredentials: true,
                    headers,
                    onUploadProgress: progressEvent => {
                        this.uploadProgress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    }
                });

                this.message = 'Upload erfolgreich!';
                this.files.forEach(file => URL.revokeObjectURL(file.preview));
                this.files = [];
                this.previews = [];
                this.uploadProgress = 0;
                this.$emit('uploaded'); // Notify parent to refresh media

            } catch (err) {
                this.error = err.response?.data?.message || 'Upload fehlgeschlagen';
            } finally {
                this.uploading = false;
            }
        }
    }
};
</script>

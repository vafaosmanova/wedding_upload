<template>
    <div class="bg-slate-300 p-6 rounded-lg shadow mb-6">
        <h2 class="text-2xl font-semibold mb-4 text-purple-600">Neues Album erstellen</h2>

        <div class="flex flex-col gap-3">
            <input v-model.trim="title" type="text" placeholder="Albumname" class="border p-2 w-64 text-gray-800 bg-white rounded" />
            <input v-model.trim="pin" type="text" placeholder="PIN" class="border p-2 w-64 text-gray-800 bg-white rounded" />

            <button
                class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 disabled:opacity-50"
                :disabled="loading || !title"
                @click="createAlbum"
            >
                <span v-if="!loading">Album erstellen</span>
                <span v-else>Wird erstellt...</span>
            </button>
        </div>

        <p v-if="message" class="text-green-600 mt-2">{{ message }}</p>
        <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "AlbumForm",
    data() {
        return { title: "", pin: "", message: "", error: "", loading: false };
    },
    methods: {
        async createAlbum() {
            this.message = "";
            this.error = "";

            if (!this.title.trim()) {
                this.error = "Bitte geben Sie einen Albumnamen ein.";
                return;
            }

            this.loading = true;
            try {
                await axios.get("/sanctum/csrf-cookie", { withCredentials: true });
                const res = await axios.post("/api/albums", { title: this.title, pin: this.pin }, { withCredentials: true });
                const payload = res.data;

                let albumObj = payload.album ?? payload;
                let qr_code = payload.qr_code ?? null;
                let pin = payload.pin ?? this.pin ?? null;

                if (!qr_code && albumObj && albumObj.id) {
                    try {
                        const qrRes = await axios.get(`/api/albums/${albumObj.id}/qrcode`, { withCredentials: true });
                        qr_code = qrRes.data.qr_code;
                        pin = qrRes.data.pin ?? pin;
                    } catch (e) {
                    }
                }

                this.message = "Album erfolgreich erstellt!";

                this.$emit("albumCreated", { album: albumObj, qr_code, pin });

                this.title = "";
                this.pin = "";
            } catch (err) {
                console.error("Album erstellen Fehler:", err);
                this.error = err.response?.data?.message || "Fehler beim Erstellen des Albums. Bitte versuchen Sie es erneut.";
            } finally {
                this.loading = false;
            }
        }
    }
};
</script>

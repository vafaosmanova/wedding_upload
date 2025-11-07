<template>
    <div class="mt-6 grid grid-cols-3 gap-4">
        <div v-for="media in mediaList" :key="media.id" class="border p-2 rounded">
            <img v-if="media.type.startsWith('image')" :src="media.url" class="w-full h-32 object-cover rounded" />
            <video v-else controls class="w-full h-32 rounded">
                <source :src="media.url" :type="media.type" />
            </video>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    props: ['albumId','guestToken'],
    data() {
        return {
            mediaList: []
        };
    },
    async mounted() {
        try {
            const res = await axios.get(`/api/guest/${this.albumId}/media`, {
                headers: {
                    'X-Gast-Token': this.guestToken
                }
            });

            this.mediaList = res.data.media.map(item => ({
                ...item,
                type: item.type === 'image'
                    ? 'image/' + item.name.split('.').pop().toLowerCase()
                    : 'video/' + item.name.split('.').pop().toLowerCase()
            }));

        } catch (err) {
            console.error('Fehler beim Laden der Medien:', err);
        }
    }
};
</script>

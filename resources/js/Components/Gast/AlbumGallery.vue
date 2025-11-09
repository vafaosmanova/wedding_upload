<template>
    <div class="mt-6 grid grid-cols-3 gap-4">
        <div v-for="media in mediaList" :key="media.id" class="border p-2 rounded relative">
            <img v-if="media.type.startsWith('image')" :src="media.url" class="w-full h-32 object-cover rounded" alt="image"/>
            <video v-else controls class="w-full h-32 rounded">
                <source :src="media.url" :type="media.type" />
            </video>

            <span v-if="showPending && !media.approved" class="absolute top-1 left-1 bg-yellow-500 text-white px-1 text-xs rounded">
                Nicht genehmigt
            </span>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        albumId: { type: [String, Number], required: true },
        showPending: { type: Boolean, default: false }
    },
    data() {
        return {
            mediaList: []
        };
    }
};
</script>

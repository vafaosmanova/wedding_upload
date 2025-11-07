<template>
    <section class="max-w-5xl mx-auto my-16 px-4">
        <h2 class="text-3xl font-bold text-center mb-10">Unsere Features</h2>

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="(feature, index) in features"
                :key="index"
                class="border rounded-xl p-6 hover:shadow-lg transition-shadow duration-300"
            >
                <div class="text-blue-600 text-4xl mb-4">
                    <i :class="feature.icon"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">{{ feature.title }}</h3>
                <p class="text-gray-700">{{ feature.description }}</p>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: "FeatureSection",
    data() {
        return {
            features: [
                {
                    icon: "fas fa-camera",
                    title: "Hochwertige Fotos",
                    description:
                        "Speichern und teilen Sie Ihre besten Momente in hoher Qualität.",
                },
                {
                    icon: "fas fa-video",
                    title: "Professionelle Videos",
                    description:
                        "Videoaufnahmen einfach hochladen, ansehen und teilen.",
                },
                {
                    icon: "fas fa-qrcode",
                    title: "QR-Code Zugang",
                    description:
                        "Erstellen Sie QR-Codes für den einfachen Zugriff auf Ihre Alben.",
                },
                {
                    icon: "fas fa-shield-alt",
                    title: "Sichere Speicherung",
                    description:
                        "Ihre Daten sind geschützt und nur für autorisierte Benutzer zugänglich.",
                },
                {
                    icon: "fas fa-cloud-upload-alt",
                    title: "Upload & Verwaltung",
                    description:
                        "Laden Sie Fotos und Videos hoch und verwalten Sie Alben bequem.",
                },
                {
                    icon: "fas fa-mobile-alt",
                    title: "Mobilfreundlich",
                    description:
                        "Alle Funktionen sind auf Smartphones und Tablets verfügbar.",
                },
            ],
        };
    },
};
</script>

<style scoped>
/* Tailwind-Klassen für Eingabevalidierung und allgemeines Styling */
</style>

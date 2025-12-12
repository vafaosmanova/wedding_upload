<template>
    <section class="bg-gradient-to-r from-purple-700 to-blue-500 text-white text-center py-24 px-5">
        <div class="max-w-6xl mx-auto px-5">
            <h2 class="text-4xl font-bold mb-4">Preise</h2>
            <p class="text-gray-600 mb-12">
                Wähle das passende Paket für deine Veranstaltung – klar, einfach und transparent.
            </p>

            <div class="flex flex-wrap justify-center gap-10">

                <div
                    class="bg-gray-100 rounded-2xl w-80 p-8 transition-transform transform hover:scale-105 hover:shadow-lg"
                >
                    <h3 class="text-2xl font-semibold mb-2">Basic</h3>
                    <p class="text-gray-600 mb-4">Ideal für kleine Feiern</p>
                    <p class="text-4xl font-bold text-blue-600 mb-6">29 €</p>

                    <ul class="text-gray-700 mb-6 space-y-2">
                        <li>✔ 500 Fotos hochladen</li>
                        <li>✔ 3 Monate Speicherzeit</li>
                        <li>✔ ZIP-Download aller Fotos</li>
                    </ul>

                    <button
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition"
                        @click="$emit('selectPlan', 'Basic')"
                    >
                        Auswählen
                    </button>
                </div>

                <div
                    class="bg-purple-100 rounded-2xl w-80 p-8 transition-transform transform hover:scale-105 hover:shadow-lg border-2 border-purple-300"
                >
                    <h3 class="text-2xl font-semibold mb-2">Premium</h3>
                    <p class="text-gray-600 mb-4">Für große Hochzeiten</p>
                    <p class="text-4xl font-bold text-purple-600 mb-6">59 €</p>

                    <ul class="text-gray-700 mb-6 space-y-2">
                        <li>✔ Unbegrenzte Fotos</li>
                        <li>✔ 1 Jahr Speicherzeit</li>
                        <li>✔ QR-Code Zugang</li>
                        <li>✔ ZIP-Download + Galerie-Export</li>
                    </ul>

                    <button
                        class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition"
                        @click="$emit('selectPlan', 'Premium')"
                    >
                        Auswählen
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: "PricingSection",
    emits: ["selectPlan"]
}
</script>

<template>
    <section class="bg-gradient-to-r from-purple-700 to-blue-500 text-white text-center py-24 px-5">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-5xl font-dancing mb-5">
                Die Foto-App für deine Hochzeitsgäste
            </h1>
            <p class="text-xl mb-8">
                Alle Bilder deiner Hochzeit einfach teilen und sichern – ganz ohne Stress.
            </p>

            <!-- Main CTA -->
            <router-link
                to="/register"
                class="bg-white text-blue-600 font-bold px-6 py-3 rounded-lg hover:bg-purple-700 hover:text-white transition duration-300"
            >
                Jetzt Album erstellen
            </router-link>

            <!-- Login link for returning owners -->
            <p class="mt-6 text-sm">
                Bereits registriert?
                <router-link to="/login" class="underline hover:text-yellow-300">Zum Login</router-link>
            </p>
        </div>
        <FeatureSection/>
        <PricingSection/>
        <ContactSection/>
    </section>
</template>

<script>
import FeatureSection from "./FeaturesSection.vue";
import PricingSection from "./PricingSection.vue";
import ContactSection from "./ContactSection.vue";


export default {
    components: {PricingSection, ContactSection, FeatureSection}
};
</script>

<template>
  <section class="bg-gradient-to-r from-purple-700 to-blue-500 text-white text-center py-24 px-5">
    <div class="max-w-5xl mx-auto">

      <h1 class="text-5xl font-dancing mb-5 drop-shadow-lg">
        Die Foto-App für deine Hochzeitsgäste
      </h1>
      <p class="text-xl mb-10 opacity-90">
        Alle Bilder deiner Hochzeit einfach teilen und sichern – ganz ohne Stress.
      </p>

      <div class="bg-white text-gray-800 rounded-2xl shadow-xl p-8 max-w-md mx-auto">
        <h2 class="text-2xl mb-6 font-semibold text-purple-700">
          Neues Album erstellen
        </h2>
        <Register />
      </div>

      <p class="mt-8 text-sm">
        Bereits registriert?
        <router-link
          to="/login"
          class="underline hover:text-yellow-300 transition duration-300"
        >
          Zum Login
        </router-link>
      </p>
    </div>

    <FeaturesSection class="mt-24" />
    <PricingSection class="mt-16" />
    <ContactSection class="mt-16" />
  </section>
</template>

<script>
import Register from "./Auth/Register.vue";
import FeaturesSection from "./FeaturesSection.vue";
import PricingSection from "./PricingSection.vue";
import ContactSection from "./ContactSection.vue";

export default {
  components: {Register, FeaturesSection, PricingSection, ContactSection,
  },
};
</script>

<template>
    <section class="bg-gradient-to-r from-purple-700 to-blue-500 text-white text-center py-24 px-5">
        <h2 class="font-script text-5xl font-bold text-center mb-10">Kontakt</h2>

        <div class="max-w-md mx-auto mt-10 p-6 rounded-xl shadow bg-gradient-to-b from-purple-200 to-purple-800">
            ">

            <form @submit.prevent="submitForm" class="card">
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Name"
                    class="input-field mb-4"
                />
                <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>

                <input
                    v-model="form.email"
                    type="email"
                    placeholder="E-Mail"
                    class="input-field mb-4"
                />
                <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>

                <input
                    v-model="form.subject"
                    type="text"
                    placeholder="Betreff"
                    class="input-field mb-4"
                />
                <p v-if="errors.subject" class="text-red-500 text-sm mt-1">{{ errors.subject }}</p>

                <textarea
                    v-model="form.message"
                    placeholder="Nachricht"
                    class="input-field mb-4"
                    rows="4"
                ></textarea>
                <p v-if="errors.message" class="text-red-500 text-sm mt-1">{{ errors.message }}</p>

                <button
                    type="submit"
                    class="btn-primary w-full"
                >
                    Nachricht senden
                </button>
            </form>

            <p v-if="successMessage" class="text-green-500 text-center mt-4">{{ successMessage }}</p>
        </div>
    </section>
</template>

<script>

export default {
    name: "ContactSection",
    data() {
        return {
            form: {
                name: '',
                email: '',
                subject: '',
                message: ''
            },
            errors: {},
            successMessage: ''
        };
    },
    methods: {
        validEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        async submitForm() {
            this.errors = {};
            this.successMessage = '';

            // Validierung
            if (!this.form.name) this.errors.name = 'Name ist erforderlich';
            if (!this.form.email) this.errors.email = 'E-Mail ist erforderlich';
            else if (!this.validEmail(this.form.email)) this.errors.email = 'E-Mail ist ungültig';
            if (!this.form.subject) this.errors.subject = 'Betreff ist erforderlich';
            if (!this.form.message) this.errors.message = 'Nachricht ist erforderlich';

            if (Object.keys(this.errors).length) return;

            try {
                await this.$axios.post('/api/contact', this.form);
                this.successMessage = 'Nachricht erfolgreich gesendet!';
                this.form = {name: '', email: '', subject: '', message: ''};
            } catch (error) {
                console.error(error);
                alert('Fehler beim Senden der Nachricht.');
            }
        }
    }
};
</script>

<style lang="css">

</style>

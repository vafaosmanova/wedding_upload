<template>
    <section class="bg-gradient-to-r from-purple-700 to-blue-500 text-white text-center py-24 px-5">
        <h2 class="text-3xl font-bold mb-4">Kontakt</h2>
        <p class="text-gray-600">Hier erscheinen später die Kontaktinformationen.</p>

        <div class="max-w-md mx-auto mt-10 bg-white p-6 rounded-xl shadow">
            <h2 class="text-2xl font-bold mb-4 text-center">Kontakt</h2>

            <form @submit.prevent="submitForm">
                <!-- Name -->
                <div class="w-full border rounded-lg px-3 py-2 mb-3 focus:outline-none focus:ring-2 focus:ring-blue-500" >
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Name"
                        :class="inputClass('name')"
                    />
                    <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="E-Mail"
                        :class="inputClass('email')"
                    />
                    <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
                </div>

                <!-- Subject -->
                <div class="mb-3">
                    <input
                        v-model="form.subject"
                        type="text"
                        placeholder="Betreff"
                        :class="inputClass('subject')"
                    />
                    <p v-if="errors.subject" class="text-red-500 text-sm mt-1">{{ errors.subject }}</p>
                </div>

                <!-- Message -->
                <div class="mb-3">
        <textarea
            v-model="form.message"
            placeholder="Nachricht"
            :class="inputClass('message')"
            rows="4"
        ></textarea>
                    <p v-if="errors.message" class="text-red-500 text-sm mt-1">{{ errors.message }}</p>
                </div>

                <button
                    type="submit"
                    class="bg-blue-600 text-white py-2 rounded-lg w-full hover:bg-blue-700"
                >
                    Nachricht senden
                </button>
            </form>

            <p v-if="successMessage" class="text-green-500 text-center mt-4">{{ successMessage }}</p>
        </div>
    </section>
    </template>

    <script>
        import axios from 'axios';

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
                inputClass(field) {
                    return [
                        'w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500',
                        this.errors[field] ? 'border-red-500' : 'border-gray-300'
                    ];
                },
                validEmail(email) {
                    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return re.test(email);
                },
                async submitForm() {
                    this.errors = {};
                    this.successMessage = '';

                    // Валидация
                    if (!this.form.name) this.errors.name = 'Name ist erforderlich';
                    if (!this.form.email) this.errors.email = 'E-Mail ist erforderlich';
                    else if (!this.validEmail(this.form.email)) this.errors.email = 'E-Mail ist ungültig';
                    if (!this.form.subject) this.errors.subject = 'Betreff ist erforderlich';
                    if (!this.form.message) this.errors.message = 'Nachricht ist erforderlich';

                    if (Object.keys(this.errors).length) return;

                    try {
                        // Пример POST запроса на бэкенд
                        await axios.post('/api/contact', this.form);

                        this.successMessage = 'Nachricht erfolgreich gesendet!';
                        this.form = { name: '', email: '', subject: '', message: '' };
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

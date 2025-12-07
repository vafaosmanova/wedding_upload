<template>
    <section class="relative py-12 px-5 min-h-screen flex flex-col justify-center items-center bg-gradient-to-b from-purple-200 to-purple-800">

        <!-- Floating ornaments -->
        <div class="absolute inset-0 pointer-events-none">
            <span class="heart animate-float absolute bg-pink-300 w-2 h-2 rounded-full top-12 left-16"></span>
            <span class="heart animate-float absolute bg-white w-2 h-2 rounded-full top-28 right-14"></span>
            <span class="heart animate-float absolute bg-pink-200 w-1.5 h-1.5 rounded-full bottom-24 left-20"></span>
            <span class="heart animate-float absolute bg-white w-1.5 h-1.5 rounded-full bottom-16 right-18"></span>
        </div>

        <div class="card relative z-10 text-center">
            <h2 class="text-3xl mb-6 text-purple-700 font-script">PIN eingeben</h2>

            <input v-model="pin" type="text" placeholder="PIN" class="input-field mb-4" />

            <button class="btn-primary" @click="verifyPin" :disabled="loading || !pin">
                {{ loading ? 'Überprüfen...' : 'Bestätigen' }}
            </button>

            <p v-if="error" class="error-text">{{ error }}</p>
        </div>
    </section>
</template>

<script>
        import axios from 'axios';

        export default {
            props: {
                albumId: { type: [String, Number], required: true }
            },
            data() {
                return {
                    pin: '',
                    error: '',
                    loading: false
                };
            },
            methods: {
                async verifyPin() {
                    this.loading = true;
                    this.error = '';

                    try {
                        const res = await axios.post(`/api/guest/${this.albumId}/verify-pin`, {
                            pin: this.pin.toString()
                      });
                        if (res.data.success && res.data.token) {
                            this.$emit('verified', res.data.token);
                        } else {
                            this.error = res.data.message || 'Falsche PIN';
                        }

                    } catch (err) {
                        this.error = 'Fehler bei der PIN-Überprüfung';
                  console.error(err);
                    }
                }
            }
        };
    </script>

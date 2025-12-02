<template>
    <div class="font-lila bg-gray-200 p-6 rounded-lg shadow max-w-sm mx-auto text-center">
        <h2 class="text-2xl mb-4 text-purple-600">PIN eingeben</h2>
        <input v-model="pin" type="text" placeholder="PIN" class="border border-gray-400 rounded p-2 w-48 mb-4 text-black bg-white"/>
        <button class="bg-purple-600 px-4 py-2 rounded hover:bg-purple-700 text-gray-700"
            @click="verifyPin"
            :disabled="loading || !pin" >
            {{ loading ? 'Überprüfen...' : ' Bestätigen' }}
        </button>
        <p v-if="error" class="text-red-500 mt-2">{{ error }}</p>
    </div>

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

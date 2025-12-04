<template>
    <section class="bg-gradient-to-r from-purple-700 to-blue-500 text-white text-center py-24 px-5">
        <div class="flex flex-col items-center justify-center mt-12">
            <form
                @submit.prevent="login"
                class="bg-gray-100 text-gray-800 rounded-2xl shadow-xl p-8 w-full max-w-sm flex flex-col gap-4"
            >
                <h2 class="text-2xl text-purple-700 mb-4 font-semibold text-center">
                    Login
                </h2>

                <input
                    v-model="form.email"
                    type="email"
                    placeholder="E-Mail"
                    class="input-field"
                />
                <input
                    v-model="form.password"
                    type="password"
                    placeholder="Passwort"
                    class="input-field"
                />

                <button
                    type="submit"
                    class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-xl shadow-md transition duration-300"
                >
                    Login
                </button>

                <p v-if="error" class="text-red-500 text-sm mt-2 text-center">
                    {{ error }}
                </p>
            </form>
        </div>
    </section>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            form: {email: "", password: ""},
            error: "",
        };
    },
    methods: {
        async login() {
            this.error = "";
            try {
                await axios.get("/sanctum/csrf-cookie", {withCredentials: true});

                const res = await axios.post("/api/login", {
                    email: this.form.email,
                    password: this.form.password,
                });

                if (res.data.success) {
                    console.log("Login success für: " + res.data.success);
                    this.$router.push("/dashboard");
                } else {
                    this.error = res.data.message || "Unbekannter Fehler.";
                }
            } catch (err) {
                console.error("Axios error:", err);
                if (err.response) {
                    if (err.response.status === 422) {
                        this.error = Object.values(err.response.data.errors)
                            .flat()
                            .join(", ");
                    } else if (err.response.status === 401) {
                        this.error = "E-Mail oder Passwort ist falsch.";
                    } else {
                        this.error = `Fehler: ${err.response.status}`;
                    }
                } else {
                    this.error = "Keine Antwort vom Server erhalten.";
                }
            }
        },
    },
};
</script>

<style scoped>
.input-field {
    @apply w-full
    px-4 py-3 border
    border-gray-300
    rounded-xl
    focus:ring-2
    focus:ring-purple-500
    focus:outline-none
    transition duration-200;
}
</style>

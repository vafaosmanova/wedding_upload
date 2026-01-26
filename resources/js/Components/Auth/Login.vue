<template>
    <section class="relative py-12 px-5 min-h-screen flex flex-col justify-center items-center">

        <div class="absolute inset-0 pointer-events-none">
            <span class="heart animate-float absolute bg-pink-300 w-2 h-2 rounded-full top-8 left-14"></span>
            <span class="heart animate-float absolute bg-white w-2 h-2 rounded-full top-24 right-12"></span>
            <span class="heart animate-float absolute bg-pink-200 w-1.5 h-1.5 rounded-full bottom-20 left-24"></span>
            <span class="heart animate-float absolute bg-white w-1.5 h-1.5 rounded-full bottom-12 right-16"></span>
        </div>
        <div class="card relative z-10">
            <h2 class="font-script text-purple-700 text-5xl font-bold mb-10">Login</h2>
            <form @submit.prevent="login" class="flex flex-col gap-4">
                <input v-model="form.email" type="email" placeholder="E-Mail" class="input-field" />
                <input v-model="form.password" type="password" placeholder="Passwort" class="input-field" />

                <button type="submit" class="btn-primary">Login</button>
                <p v-if="error" class="error-text">{{ error }}</p>
            </form>
        </div>
    </section>
</template>
<script>
export default {
    data() {
        return {
            form: {
                email: "",
                password: ""
            },
            error: "",
        };
    },
    methods: {
        async login() {
            this.error = "";
            try {
                const res = await this.$axios.post("/api/login", {
                    email: this.form.email,
                    password: this.form.password,
                }, {withCredentials: true});

                if (res.data.success) {
                    this.$router.push("/dashboard");
                } else {
                    this.error = res.data.message;
                }
            } catch (err) {
                console.error("Axios error:", err);
                if (err.response) {
                    if (err.response.status === 422) {
                        const e = err.response.data.errors;

                        if (e && typeof e === "object") {
                            this.error = Object.values(e).flat().join(", ");
                        } else {
                            this.error = "Ungültige Eingabedaten.";
                        }
                    }
                } else {
                    this.error = "Keine Antwort vom Server erhalten.";
                }
            }
        },
    },
};
</script>

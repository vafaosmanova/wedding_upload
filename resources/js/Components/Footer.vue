<template>
    <footer class="bg-gray-900 text-white p-4 text-center font-sans mt-12 shadow-inner relative overflow-hidden">

        <!-- Decorative hearts -->
        <div class="absolute top-0 left-0 w-full h-full pointer-events-none">
            <span class="heart animate-float absolute bg-pink-400 w-1.5 h-1.5 rounded-full"></span>
            <span class="heart animate-float absolute bg-white w-1 h-1 rounded-full"></span>
            <span class="heart animate-float absolute bg-pink-200 w-1 h-1 rounded-full"></span>
        </div>

        <p class="text-sm md:text-base z-10 relative font-sans">
            &copy; 2025 Hochzeits-Album-Plattform. Alle Rechte vorbehalten.
        </p>
        <p class="text-xs md:text-sm text-pink-200 mt-1 md:mt-2 z-10 relative font-script">
            Entwickelt mit ❤️ von Ihrem Team
        </p>
    </footer>
</template>

<script>
export default {};
</script>

<style scoped>
/* Reuse float animation */
@keyframes float {
    0% { transform: translateY(0) scale(1); opacity: 1; }
    50% { transform: translateY(-15px) scale(1.2); opacity: 0.7; }
    100% { transform: translateY(0) scale(1); opacity: 1; }
}

.heart {
    animation: float 4s ease-in-out infinite;
}

.heart:nth-child(1) { top: 15%; left: 10%; animation-delay: 0s; }
.heart:nth-child(2) { top: 50%; left: 60%; animation-delay: 1.5s; }
.heart:nth-child(3) { top: 80%; left: 30%; animation-delay: 3s; }
</style>

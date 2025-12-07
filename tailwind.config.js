/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    safelist: [
        'border-red-500',
        'border-gray-300',
    ],
    theme: {
        extend: {
            fontFamily: {
                script: ['Dancing Script', 'cursive'],
                sans: ['Roboto', 'sans-serif'],
            },
        },
    },
    plugins: [],
};

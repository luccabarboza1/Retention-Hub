/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    safelist: [
        'w-16', 'w-64', 'rotate-180',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50:  '#f5f3ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9',
                    800: '#5b21b6',
                    900: '#4c1d95',
                },
                accent: {
                    emerald: '#10b981',
                    amber:   '#f59e0b',
                    rose:    '#f43f5e',
                    indigo:  '#6366f1',
                    purple:  '#a855f7',
                    cyan:    '#06b6d4',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                mono: ['Fira Code', 'Courier New', 'monospace'],
            },
            boxShadow: {
                'premium':       '0 8px 30px rgb(0 0 0 / 0.04)',
                'premium-hover': '0 20px 40px -10px rgb(124 58 237 / 0.08)',
                'glow-brand':    '0 0 20px rgba(124, 58, 237, 0.15)',
            },
        },
    },
    plugins: [],
}

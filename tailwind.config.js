import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                arabic: ['Amiri', 'serif'],
                display: ['Playfair Display', 'Georgia', 'serif'],
            },
            colors: {
                // Ruang Sanad Brand Palette
                rs: {
                    teal:       '#0F6E56',
                    'teal-light': '#1D9E75',
                    'teal-dark':  '#085041',
                    'teal-dim':   '#0D6E6E',
                    gold:       '#C9972A',
                    'gold-light': '#FAC775',
                    'gold-dim':   '#C4A44A',
                    cream:      '#FAF7F2',
                    sand:       '#F0EBE0',
                    brown:      '#3D2B1F',
                    // Dark mode surfaces
                    'dark-base': '#070F0E',
                    'dark-card': '#0B1817',
                    'dark-hover': '#101F1E',
                },
            },
            backgroundImage: {
                'rs-gradient': 'linear-gradient(135deg, #0F6E56 0%, #085041 100%)',
                'rs-gold-gradient': 'linear-gradient(135deg, #C9972A 0%, #FAC775 100%)',
            },
        },
    },

    plugins: [forms],
};

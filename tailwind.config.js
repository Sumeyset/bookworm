import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                burgundy: {
                    DEFAULT: '#6D1A2B',
                    hover: '#8B2A3D',
                    deep: '#4A1220',
                    dark: '#A3324A',
                },
                leather: '#3B2418',
                walnut: '#5A3A2E',
                tan: '#8A6A55',
                ivory: '#F7F1EA',
                paper: '#FFFDF9',
                parchment: '#EFE3D3',
                warmborder: '#E6D9D0',
                gold: {
                    DEFAULT: '#B8893B',
                    dark: '#D1A24E',
                },
                ink: '#2A1A1D',
                wineblack: '#1A0F12',
                raised: '#271519',
                rosetint: '#F1DDE0',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['Lora', 'Georgia', ...defaultTheme.fontFamily.serif],
                hand: ['Caveat', 'cursive'],
                handwritten: ['Caveat', 'cursive'],
            },
            boxShadow: {
                bookplate: '0 12px 32px -22px rgb(74 18 32 / 35%), inset 0 0 0 1px rgb(184 137 59 / 9%)',
                shelf: 'inset 0 10px 24px rgb(26 15 18 / 45%), 0 18px 34px -24px rgb(26 15 18 / 65%)',
            },
            backgroundImage: {
                'paper-grain': 'url("data:image/svg+xml,%3Csvg viewBox=%270 0 180 180%27 xmlns=%27http://www.w3.org/2000/svg%27%3E%3Cfilter id=%27n%27%3E%3CfeTurbulence type=%27fractalNoise%27 baseFrequency=%270.88%27 numOctaves=%274%27 stitchTiles=%27stitch%27/%3E%3C/filter%3E%3Crect width=%27100%25%27 height=%27100%25%27 filter=%27url(%23n)%27/%3E%3C/svg%3E")',
                'wine-header': 'linear-gradient(112deg, #4A1220 0%, #6D1A2B 56%, #8B2A3D 100%)',
                'shelf-wood': 'repeating-linear-gradient(176deg, #503326 0px, #62412f 4px, #56382b 8px, #6b4935 13px)',
            },
        },
    },

    plugins: [forms],
};

import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                cream: '#FAFAFA',
                charcoal: '#0F0F0F',
                ink: '#0F0F0F',
                surface: {
                    DEFAULT: '#F3F2F6',
                    header: '#EAE6F4',
                    bar: '#F0EBE6',
                    catalog: '#EEEAF5',
                },
                coral: {
                    DEFAULT: '#FF5A36',
                    soft: '#FF7A5C',
                    muted: '#FFE8E3',
                },
                violet: {
                    DEFAULT: '#7C5CFC',
                    soft: '#9B82FD',
                    muted: '#EDE8FF',
                },
                frost: '#8A8F98',
                brand: {
                    DEFAULT: '#FF5A36',
                    50: '#FFE8E3',
                    100: '#FFD0C7',
                    600: '#FF5A36',
                    700: '#E04A28',
                    800: '#C93D1F',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Space Grotesk"', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                card: '0 1px 0 rgba(15,15,15,0.06), 0 8px 24px rgba(15,15,15,0.04)',
                lift: '0 12px 40px rgba(15,15,15,0.08)',
            },
        },
    },

    plugins: [forms],
};

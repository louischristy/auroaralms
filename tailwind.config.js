/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                primary: 'var(--color-primary, #2B4C7E)',
                secondary: 'var(--color-secondary, #3A7BD5)',
                accent: 'var(--color-accent, #5BC0EB)',
                neutral: 'var(--color-neutral, #A8A9AD)',
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [],
};

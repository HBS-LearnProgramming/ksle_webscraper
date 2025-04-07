/** @type {import('tailwindcss').Config} */
import tailwindcss from "tailwindcss";
export default {
    content: [
        "./resources/views/**/*.blade.php", 
        "./resources/**/*.{vue,js,ts,jsx,tsx}"
    ],
    theme: {
        extend: {
            colors: {
                primary: "#262161",
                bg_white: "eff7f9",
                bg_color: "#d7eaea",
                text_color: "#375e77",
                black: "#000",
            },
            backgroundImage: {
                "bg-image":
                    "linear-gradient(299deg, rgba(38,33,97,0.8120040252429097) 0%, rgba(38,33,97,0.13693399723170518) 0%, rgba(175,171,215,0.1285306358871674) 100%)",
            },
        },
    },
    css: {
        postcss: {
            plugins: [tailwindcss],
        },
    },
    plugins: [],
};
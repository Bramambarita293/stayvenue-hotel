<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    background: "#F7F4EF",
                    surface: "#FFFFFF",
                    "surface-muted": "#F0ECE3",
                    "surface-deep": "#E9E3D6",
                    ink: "#161513",
                    stone: "#6B675F",
                    line: "#E7E2D9",
                    gold: "#C9A24B",
                    "gold-soft": "#A9823C",
                    onyx: "#141414",
                    successful: "#1F7A4D",
                    pending: "#B7791F",
                    danger: "#B42318",
                },
                fontFamily: {
                    display: ["Playfair Display", "serif"],
                    body: ["Inter", "ui-sans-serif", "system-ui", "sans-serif"],
                },
                borderRadius: {
                    DEFAULT: "0.5rem",
                    lg: "0.75rem",
                    xl: "1.125rem",
                    "2xl": "1.5rem",
                },
                maxWidth: {
                    container: "80rem",
                },
                boxShadow: {
                    card: "0 1px 2px rgba(22,21,19,0.05), 0 8px 24px rgba(22,21,19,0.06)",
                    cardhover: "0 2px 4px rgba(22,21,19,0.06), 0 18px 40px rgba(22,21,19,0.10)",
                    nav: "0 1px 0 rgba(22,21,19,0.06)",
                },
            },
        },
    };
</script>
<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.22em;
        color: #C9A24B;
    }
    .img-cover {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
<link href="https://fonts.googleapis.com" rel="preconnect" />
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
<link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Inter:wght@400;500;600;700&display=swap"
    rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
    rel="stylesheet" />
<?php
// src/includes/theme.php
?>
<!-- Mobile Meta Tags -->
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="theme-color" content="#1e1b4b">
<meta name="format-detection" content="telephone=no">

<!-- Tailwind CSS (CDN) -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Custom Config for App Feel -->
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    // Midnight Navy Palette
                    primary: '#1e1b4b', // Indigo 950
                    primaryLight: '#312e81', // Indigo 900

                    // Neon Violet Accents
                    accent: '#8b5cf6', // Violet 500
                    accentHover: '#7c3aed', // Violet 600

                    // Functional Colors
                    secondary: '#64748b', // Slate 500
                    success: '#10b981', // Emerald 500
                    danger: '#ef4444', // Red 500

                    // Backgrounds
                    background: '#f8fafc', // Slate 50
                    surface: '#ffffff',

                    // Typography
                    textMain: '#0f172a', // Slate 900
                    textMuted: '#64748b', // Slate 500
                    textLight: '#f8fafc', // Slate 50
                },
                fontFamily: {
                    sans: ['-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'Helvetica', 'Arial', 'sans-serif'],
                },
                safeArea: {
                    'top': 'env(safe-area-inset-top)',
                    'bottom': 'env(safe-area-inset-bottom)',
                },
                animation: {
                    'fade-in-down': 'fadeInDown 0.5s ease-out',
                    'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                },
                keyframes: {
                    fadeInDown: {
                        '0%': { opacity: '0', transform: 'translateY(-10px)' },
                        '100%': { opacity: '1', transform: 'translateY(0)' },
                    }
                }
            }
        }
    }
</script>

<style>
    /* Safe Area Utilities */
    .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    .pt-safe { padding-top: env(safe-area-inset-top); }

    /* App Shell Logic */
    html, body {
        height: 100%;
        width: 100%;
        overflow: hidden; /* Prevent body scroll */
        -webkit-user-select: none; /* Disable selection */
        user-select: none;
        -webkit-touch-callout: none;
        touch-action: manipulation;
    }

    body {
        background-color: #f8fafc;
        display: flex;
        flex-direction: column;
    }

    /* Scrollable Content Area */
    .app-content {
        flex: 1;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-y: none; /* Prevent pull-to-refresh */
        position: relative;
        width: 100%;
    }

    /* Text Inputs should be selectable */
    input, textarea {
        -webkit-user-select: text;
        user-select: text;
    }

    /* Hide Scrollbar but keep functionality */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Tap Highlight Removal */
    * {
        -webkit-tap-highlight-color: transparent;
    }

    /* Button Press Effect */
    .active-scale:active {
        transform: scale(0.97);
        transition: transform 0.1s;
    }
</style>

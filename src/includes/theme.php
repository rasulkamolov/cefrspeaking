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
                    primary: '#1e1b4b', // Indigo 950
                    primaryLight: '#312e81', // Indigo 900
                    accent: '#8b5cf6', // Violet 500
                    accentHover: '#7c3aed', // Violet 600
                    secondary: '#64748b', // Slate 500
                    success: '#10b981', // Emerald 500
                    danger: '#ef4444', // Red 500
                    background: '#f8fafc', // Slate 50
                    surface: '#ffffff',
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
                height: {
                    'screen-dvh': '100dvh',
                },
                minHeight: {
                    'screen-dvh': '100dvh',
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
    html {
        height: 100dvh; /* Use Dynamic Viewport Height */
        width: 100%;
        overflow: hidden;
    }

    body {
        height: 100dvh;
        width: 100%;
        overflow: hidden; /* Prevent body scroll, handle in app-content */
        -webkit-user-select: none;
        user-select: none;
        -webkit-touch-callout: none;
        touch-action: manipulation;
        background-color: #f8fafc;
        display: flex;
        flex-direction: column;
        position: fixed; /* Lock body in place */
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }

    /* Scrollable Content Area */
    .app-content {
        flex: 1;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior-y: none;
        position: relative;
        width: 100%;
        height: 100%;
    }

    /* Text Inputs should be selectable */
    input, textarea {
        -webkit-user-select: text;
        user-select: text;
    }

    /* Hide Scrollbar */
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

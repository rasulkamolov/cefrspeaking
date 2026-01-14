<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#2563eb', // blue-600
                    primaryHover: '#1d4ed8', // blue-700
                    secondary: '#475569', // slate-600
                    accent: '#06b6d4', // cyan-500
                    success: '#22c55e', // green-500
                    danger: '#ef4444', // red-500
                    background: '#f8fafc', // slate-50
                    surface: '#ffffff', // white
                    textMain: '#0f172a', // slate-900
                    textMuted: '#64748b', // slate-500
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                }
            }
        }
    }
</script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
</style>

/* ============================================
   Theme Toggle (dark / light)
   ============================================ */
(function () {
    const STORAGE_KEY = 'dulinan-theme';

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
    }

    const saved = localStorage.getItem(STORAGE_KEY);
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initial = saved || (systemDark ? 'dark' : 'light');
    applyTheme(initial);

    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('themeToggle');
        if (!btn) return;

        btn.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            localStorage.setItem(STORAGE_KEY, next);

            btn.style.transform = 'scale(0.9)';
            setTimeout(() => { btn.style.transform = ''; }, 120);
        });
    });
})();
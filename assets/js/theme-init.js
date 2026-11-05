(function() {
    try {
        const saved = localStorage.getItem('gamehub-theme');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = saved || (systemDark ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
    } catch (e) {}
})();
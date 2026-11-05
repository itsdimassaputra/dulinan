/* ============================================
   Theme Toggle (dark / light)
   ============================================ */
(function () {
    const STORAGE_KEY = 'gamehub-theme';

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

/* ============================================
   Search & Filter (homepage)
   ============================================ */
(function () {
    const searchInput = document.getElementById('searchInput');
    const chips = document.querySelectorAll('.chip[data-filter]');
    const cards = document.querySelectorAll('.game-card');
    const emptyState = document.getElementById('emptyState');

    if (!searchInput && chips.length === 0) return;

    let activeFilter = 'all';
    let searchTerm = '';

    function applyFilters() {
        let visible = 0;
        cards.forEach((card) => {
            const name = (card.dataset.name || '').toLowerCase();
            const cat = card.dataset.category || '';
            const matchSearch = !searchTerm || name.includes(searchTerm);
            const matchFilter = activeFilter === 'all' || cat === activeFilter;
            const show = matchSearch && matchFilter;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (emptyState) {
            emptyState.style.display = visible === 0 ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchTerm = e.target.value.trim().toLowerCase();
            applyFilters();
        });
    }

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            chips.forEach((c) => c.classList.remove('active'));
            chip.classList.add('active');
            activeFilter = chip.dataset.filter;
            applyFilters();
        });
    });
})();

/* ============================================
   Stagger animation
   ============================================ */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.game-card').forEach((card, i) => {
        card.style.animationDelay = `${i * 40}ms`;
    });
});
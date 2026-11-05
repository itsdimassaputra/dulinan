/* ============================================
   Home — Render grid game dari games.json
   ============================================ */
(function () {
    const gameGrid   = document.getElementById('gameGrid');
    const filterChips = document.getElementById('filterChips');
    const emptyState = document.getElementById('emptyState');
    const searchInput = document.getElementById('searchInput');
    const gameCount  = document.getElementById('gameCount');

    if (!gameGrid) return;

    let allGames = [];
    let allCategories = [];
    let activeFilter = 'all';
    let searchTerm = '';

    // Fetch kedua JSON paralel
    Promise.all([
        fetch('data/games.json').then(r => r.json()),
        fetch('data/categories.json').then(r => r.json())
    ])
    .then(([games, categories]) => {
        allGames = games.filter(g => g.active !== false);
        allCategories = categories;
        gameCount.textContent = allGames.length;
        renderChips();
        renderGrid();
    })
    .catch(err => {
        console.error('Gagal memuat data:', err);
        gameGrid.innerHTML = `
            <div class="empty-state">
                <h3>Gagal memuat game</h3>
                <p>Pastikan file <code>data/games.json</code> bisa diakses.</p>
            </div>`;
    });

    function renderChips() {
        const counts = { all: allGames.length };
        allCategories.forEach(cat => {
            counts[cat.id] = allGames.filter(g => g.category === cat.id).length;
        });

        filterChips.innerHTML = `
            <button class="chip active" data-filter="all">Semua (${counts.all})</button>
            ${allCategories.map(cat => `
                <button class="chip" data-filter="${cat.id}">
                    ${escapeHtml(cat.name)} (${counts[cat.id] || 0})
                </button>
            `).join('')}
        `;

        filterChips.querySelectorAll('.chip').forEach(chip => {
            chip.addEventListener('click', () => {
                filterChips.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                activeFilter = chip.dataset.filter;
                renderGrid();
            });
        });
    }

    function renderGrid() {
        const filtered = allGames.filter(game => {
            const matchSearch = !searchTerm || game.name.toLowerCase().includes(searchTerm);
            const matchFilter = activeFilter === 'all' || game.category === activeFilter;
            return matchSearch && matchFilter;
        });

        if (filtered.length === 0) {
            gameGrid.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }
        emptyState.style.display = 'none';

        gameGrid.innerHTML = filtered.map((game, i) => {
            const cat = allCategories.find(c => c.id === game.category);
            const catName = cat ? cat.name : 'Umum';
            return `
                <a href="games/${escapeHtml(game.slug)}.html"
                   class="game-card"
                   data-name="${escapeHtml(game.name)}"
                   data-category="${escapeHtml(game.category)}"
                   style="animation-delay: ${i * 40}ms;">
                    <span class="play-badge">▶ Mainkan</span>
                    <div class="game-thumb" style="background: ${escapeHtml(game.color)}1a;">
                        <span class="icon">${escapeHtml(game.icon)}</span>
                    </div>
                    <div class="game-info">
                        <div class="game-name">${escapeHtml(game.name)}</div>
                        <div class="game-meta">
                            ${escapeHtml(catName)} · ${Number(game.plays || 0).toLocaleString('id-ID')} dimainkan
                        </div>
                    </div>
                </a>
            `;
        }).join('');
    }

    // Search
    if (searchInput) {
        searchInput.addEventListener('input', e => {
            searchTerm = e.target.value.trim().toLowerCase();
            renderGrid();
        });
    }

    // Helper
    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[m]));
    }
})();
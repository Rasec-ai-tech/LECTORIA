const resultsGrid = document.querySelector('.results-grid');

if (resultsGrid) {
    resultsGrid.addEventListener('click', (event) => {
        const clickableCard = event.target.closest('.book-card--clickable');
        if (!clickableCard) {
            return;
        }

        const href = clickableCard.dataset.href || clickableCard.getAttribute('href');
        if (href) {
            window.location.assign(href);
        }
    });

    resultsGrid.addEventListener('keydown', (event) => {
        const clickableCard = event.target.closest('.book-card--clickable');
        if (!clickableCard) {
            return;
        }

        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            const href = clickableCard.dataset.href || clickableCard.getAttribute('href');
            if (href) {
                window.location.assign(href);
            }
        }
    });
}

const loadMoreButton = document.querySelector('#load-more-button');

if (loadMoreButton) {
    const toastHost = document.createElement('div');
    toastHost.className = 'toast-host';
    document.body.appendChild(toastHost);

    const showToast = (message, isError = false) => {
        const toast = document.createElement('div');
        toast.className = `toast ${isError ? 'toast-error' : 'toast-success'}`;
        toast.textContent = message;
        toastHost.appendChild(toast);

        window.setTimeout(() => {
            toast.classList.add('is-visible');
        }, 10);

        window.setTimeout(() => {
            toast.classList.remove('is-visible');
            window.setTimeout(() => toast.remove(), 200);
        }, 2800);
    };

    let page = Number(loadMoreButton.dataset.page || 2);
    let isLoading = false;

    const updateLoadingState = (loading) => {
        isLoading = loading;
        loadMoreButton.disabled = loading;
        loadMoreButton.innerHTML = loading
            ? '<span class="spinner" aria-hidden="true"></span> Chargement…'
            : 'Charger plus';
    };

    const disableLoadMore = () => {
        loadMoreButton.disabled = true;
        loadMoreButton.textContent = 'Plus de résultats';
        loadMoreButton.dataset.hasNext = '0';
    };

    loadMoreButton.addEventListener('click', async () => {
        if (isLoading || !resultsGrid) {
            return;
        }

        const q = new URLSearchParams(window.location.search).get('q') || '';
        updateLoadingState(true);

        try {
            const response = await fetch(`results.php?q=${encodeURIComponent(q)}&page=${page}&ajax=1`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Impossible de charger plus de résultats pour le moment.');
            }

            const hasMore = response.headers.get('X-Has-More') === '1';
            const html = await response.text();
            const fragment = document.createRange().createContextualFragment(html);
            const nextGrid = fragment.querySelector('.results-grid');
            const innerHtml = nextGrid ? nextGrid.innerHTML : html;
            resultsGrid.insertAdjacentHTML('beforeend', innerHtml);

            page += 1;
            loadMoreButton.dataset.page = String(page);

            if (!hasMore) {
                disableLoadMore();
            }
        } catch (error) {
            showToast(error instanceof Error ? error.message : 'Une erreur réseau est survenue.', true);
        } finally {
            updateLoadingState(false);
        }
    });
}

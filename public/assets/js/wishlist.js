document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('#remove-modal');
    const modalMessage = document.querySelector('#remove-modal-message');
    const confirmButton = document.querySelector('#confirm-remove');
    const cancelButton = document.querySelector('#cancel-remove');
    let pendingForm = null;

    const openModal = (form) => {
        pendingForm = form;
        if (modal && modalMessage) {
            const titleElement = form.closest('.book-row')?.querySelector('.row-info h3');
            const title = titleElement?.textContent?.trim() || 'ce livre';
            modalMessage.textContent = `Voulez-vous vraiment retirer "${title}" de votre liste de lecture ?`;
            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
        }
    };

    const closeModal = () => {
        pendingForm = null;
        if (modal) {
            modal.classList.add('hidden');
            modal.setAttribute('aria-hidden', 'true');
        }
    };

    const showToast = (message, isError = false) => {
        let toastHost = document.querySelector('.toast-host');
        if (!toastHost) {
            toastHost = document.createElement('div');
            toastHost.className = 'toast-host';
            document.body.appendChild(toastHost);
        }

        const toast = document.createElement('div');
        toast.className = `toast ${isError ? 'toast-error' : 'toast-success'}`;
        toast.textContent = message;
        toastHost.appendChild(toast);

        window.setTimeout(() => toast.classList.add('is-visible'), 10);
        window.setTimeout(() => {
            toast.classList.remove('is-visible');
            window.setTimeout(() => toast.remove(), 200);
        }, 2800);
    };

    const applyLateState = () => {
        document.querySelectorAll('.book-row[data-date-emprunt]').forEach((row) => {
            const dateValue = row.dataset.dateEmprunt;
            if (!dateValue) {
                return;
            }

            const dateEmprunt = new Date(dateValue);
            if (Number.isNaN(dateEmprunt.getTime())) {
                return;
            }

            const difference = Date.now() - dateEmprunt.getTime();
            const days = Math.floor(difference / 86400000);

            if (days > 21) {
                row.classList.add('late');
                const statusPill = row.querySelector('.status-pill');
                if (statusPill) {
                    statusPill.classList.remove('ongoing');
                    statusPill.classList.add('late');
                    statusPill.textContent = 'En retard';
                }
            }
        });
    };

    applyLateState();

    const addToWishlistForm = document.querySelector('form[action="add_to_wishlist.php"]');
    if (addToWishlistForm) {
        const submitButton = addToWishlistForm.querySelector('button[type="submit"]');
        const stockElement = document.querySelector('.meta-row .v.stock');
        const csrfToken = addToWishlistForm.querySelector('input[name="csrf_token"]')?.value ?? '';
        const idLivre = addToWishlistForm.querySelector('input[name="id_livre"]')?.value ?? '';

        addToWishlistForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!submitButton || submitButton.disabled) {
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = 'Ajout…';

            try {
                const response = await fetch('add_to_wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new URLSearchParams({
                        id_livre: idLivre,
                        csrf_token: csrfToken,
                    }).toString(),
                });

                const payload = await response.json().catch(() => null);
                if (!response.ok) {
                    throw new Error(payload?.message || 'Le stock a été mis à jour entre-temps.');
                }

                const nouveauStock = Number(payload?.nouveauStock ?? 0);
                if (stockElement) {
                    stockElement.textContent = `${nouveauStock} exemplaire${nouveauStock > 1 ? 's' : ''} disponible${nouveauStock > 1 ? 's' : ''}`;
                }

                showToast(payload?.message || 'Ajouté à votre liste de lecture');

                if (nouveauStock <= 0) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'Stock épuisé';
                }
            } catch (error) {
                showToast(error instanceof Error ? error.message : 'Une erreur réseau a empêché l’ajout.', true);
            } finally {
                if (submitButton && !submitButton.disabled) {
                    submitButton.textContent = 'Ajouter à ma liste de lecture';
                }
            }
        });
    }

    const removeForms = document.querySelectorAll('.reading-list form');
    removeForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            openModal(form);
        });
    });

    confirmButton?.addEventListener('click', async () => {
        if (!pendingForm) {
            closeModal();
            return;
        }

        const row = pendingForm.closest('.book-row');
        const csrfToken = pendingForm.querySelector('input[name="csrf_token"]')?.value ?? '';
        const idLivre = pendingForm.querySelector('input[name="id_livre"]')?.value ?? '';

        try {
            const response = await fetch('remove_from_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams({
                    id_livre: idLivre,
                    csrf_token: csrfToken,
                }).toString(),
            });

            const payload = await response.json().catch(() => null);
            if (!response.ok) {
                throw new Error(payload?.message || 'Le retrait a échoué.');
            }

            if (row) {
                row.classList.add('is-removing');
                window.setTimeout(() => {
                    row.remove();
                    const totalCounter = document.querySelector('#tracker-total');
                    const lateCounter = document.querySelector('#tracker-late');
                    if (totalCounter) {
                        const totalValue = Number.parseInt(totalCounter.dataset.count || '0', 10) - 1;
                        totalCounter.dataset.count = String(Math.max(totalValue, 0));
                        totalCounter.textContent = `${Math.max(totalValue, 0)} livre${Math.max(totalValue, 0) > 1 ? 's' : ''} enregistré${Math.max(totalValue, 0) > 1 ? 's' : ''}`;
                    }
                    if (lateCounter && row.classList.contains('late')) {
                        const lateValue = Number.parseInt(lateCounter.dataset.count || '0', 10) - 1;
                        lateCounter.dataset.count = String(Math.max(lateValue, 0));
                        lateCounter.textContent = `${Math.max(lateValue, 0)} en retard`;
                    }
                }, 220);
            }

            showToast(payload?.message || 'Livre retiré de votre liste de lecture.');
        } catch (error) {
            showToast(error instanceof Error ? error.message : 'Une erreur réseau a empêché le retrait.', true);
        } finally {
            closeModal();
        }
    });

    cancelButton?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
});

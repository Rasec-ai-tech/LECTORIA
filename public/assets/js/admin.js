const forms = document.querySelectorAll('.form-card');

const createFieldErrorNode = (fieldNode) => {
    let errorNode = fieldNode.parentElement?.querySelector('.field-error');
    if (!errorNode) {
        errorNode = document.createElement('span');
        errorNode.className = 'field-error';
        fieldNode.parentElement.appendChild(errorNode);
    }
    return errorNode;
};

const validateField = (fieldNode) => {
    if (fieldNode.type === 'hidden') {
        return true;
    }

    const fieldName = fieldNode.name;
    const value = fieldNode.value.trim();
    const errorNode = createFieldErrorNode(fieldNode);

    if (fieldName === 'titre') {
        if (value === '') {
            errorNode.textContent = 'Le titre est obligatoire.';
            return false;
        }
        if (value.length > 255) {
            errorNode.textContent = 'Le titre ne doit pas dépasser 255 caractères.';
            return false;
        }
    }

    if (fieldName === 'auteur') {
        if (value === '') {
            errorNode.textContent = 'L\'auteur est obligatoire.';
            return false;
        }
        if (value.length > 255) {
            errorNode.textContent = 'L\'auteur ne doit pas dépasser 255 caractères.';
            return false;
        }
    }

    if (fieldName === 'maison_edition' && value.length > 255) {
        errorNode.textContent = 'La maison d\'édition ne doit pas dépasser 255 caractères.';
        return false;
    }

    if (fieldName === 'description' && value.length > 2000) {
        errorNode.textContent = 'La description ne doit pas dépasser 2000 caractères.';
        return false;
    }

    if (fieldName === 'nombre_exemplaire') {
        const numericValue = Number(value);
        if (!Number.isFinite(numericValue) || !Number.isInteger(numericValue) || numericValue < 0) {
            errorNode.textContent = 'Le nombre d\'exemplaires doit être un entier positif.';
            return false;
        }
    }

    errorNode.textContent = '';
    return true;
};

const validateForm = (form) => {
    const fields = Array.from(form.querySelectorAll('input, textarea'));
    let valid = true;

    for (const field of fields) {
        if (field.type === 'hidden') {
            continue;
        }

        const fieldValid = validateField(field);
        if (!fieldValid) {
            valid = false;
        }
    }

    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = !valid;
    }

    return valid;
};

forms.forEach((form) => {
    const fields = form.querySelectorAll('input, textarea');
    fields.forEach((field) => {
        field.addEventListener('input', () => validateForm(form));
        field.addEventListener('blur', () => validateForm(form));
    });

    form.addEventListener('submit', (event) => {
        if (!validateForm(form)) {
            event.preventDefault();
        }
    });

    validateForm(form);
});

const deleteButtons = document.querySelectorAll('.btn-delete[data-book-title]');
const modal = document.querySelector('#delete-modal');
const modalMessage = document.querySelector('#delete-modal-message');
const confirmButton = document.querySelector('#confirm-delete');
const cancelButton = document.querySelector('#cancel-delete');
let pendingForm = null;

const openDeleteModal = (title, form) => {
    if (!modal || !modalMessage || !confirmButton || !cancelButton) {
        return;
    }

    pendingForm = form;
    modalMessage.innerHTML = `Voulez-vous vraiment supprimer <strong>${title}</strong> ?`;
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
};

const closeDeleteModal = () => {
    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    pendingForm = null;
};

deleteButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
        event.preventDefault();
        const form = button.closest('form');
        const title = button.dataset.bookTitle || 'ce livre';
        openDeleteModal(title, form);
    });
});

confirmButton?.addEventListener('click', () => {
    if (pendingForm) {
        pendingForm.submit();
    }
    closeDeleteModal();
});

cancelButton?.addEventListener('click', closeDeleteModal);
modal?.addEventListener('click', (event) => {
    if (event.target === modal) {
        closeDeleteModal();
    }
});

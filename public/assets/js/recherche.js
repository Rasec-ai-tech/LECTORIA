document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('#search-input');
    const searchGroup = searchInput?.closest('.search-input-group');

    if (!searchInput || !searchGroup) {
        return;
    }

    const dropdown = document.createElement('ul');
    dropdown.className = 'autocomplete-list';
    dropdown.hidden = true;
    searchGroup.appendChild(dropdown);

    const statusMessage = document.createElement('div');
    statusMessage.className = 'autocomplete-status';
    statusMessage.hidden = true;
    searchGroup.appendChild(statusMessage);

    let debounceId = null;
    let selectedIndex = -1;
    let suggestions = [];

    const closeSuggestions = () => {
        dropdown.hidden = true;
        dropdown.innerHTML = '';
        selectedIndex = -1;
    };

    const setStatus = (message) => {
        if (!message) {
            statusMessage.hidden = true;
            statusMessage.textContent = '';
            return;
        }

        statusMessage.textContent = message;
        statusMessage.hidden = false;
    };

    const renderSuggestions = (items) => {
        suggestions = items;
        dropdown.innerHTML = '';

        if (!items.length) {
            closeSuggestions();
            return;
        }

        items.forEach((item, index) => {
            const option = document.createElement('li');
            option.className = index === selectedIndex ? 'is-active' : '';
            option.dataset.id = String(item.id);

            const title = document.createElement('span');
            title.textContent = item.titre;
            const author = document.createElement('small');
            author.textContent = item.auteur;

            option.appendChild(title);
            option.appendChild(author);
            option.addEventListener('mousedown', (event) => {
                event.preventDefault();
                window.location.href = `details.php?id=${item.id}`;
            });

            dropdown.appendChild(option);
        });

        dropdown.hidden = false;
    };

    const runAutocomplete = async () => {
        const value = searchInput.value.trim();

        if (value === '') {
            closeSuggestions();
            setStatus('');
            return;
        }

        try {
            const response = await fetch(`autocomplete.php?q=${encodeURIComponent(value)}`);
            if (!response.ok) {
                throw new Error('La recherche automatique n’a pas pu être chargée.');
            }

            const payload = await response.json();
            renderSuggestions(Array.isArray(payload) ? payload : []);
            setStatus('');
        } catch (error) {
            closeSuggestions();
            setStatus('La recherche automatique est indisponible pour le moment.');
            window.console?.error(error);
        }
    };

    searchInput.addEventListener('input', () => {
        window.clearTimeout(debounceId);
        const value = searchInput.value.trim();

        if (value === '') {
            closeSuggestions();
            setStatus('');
            return;
        }

        debounceId = window.setTimeout(runAutocomplete, 300);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (dropdown.hidden) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, suggestions.length - 1);
            renderSuggestions(suggestions);
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, 0);
            renderSuggestions(suggestions);
        }

        if (event.key === 'Enter' && selectedIndex >= 0) {
            event.preventDefault();
            const suggestion = suggestions[selectedIndex];
            if (suggestion) {
                window.location.href = `details.php?id=${suggestion.id}`;
            }
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeSuggestions();
        }
    });

    document.addEventListener('click', (event) => {
        if (!searchGroup.contains(event.target)) {
            closeSuggestions();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSuggestions();
        }
    });
});

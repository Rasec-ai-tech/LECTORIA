document.addEventListener('DOMContentLoaded', () => {
    const navbar = document.querySelector('.navbar');

    if (!navbar) {
        return;
    }

    const navWrap = navbar.querySelector('.wrap') || navbar;
    const navLinks = navWrap.querySelector('.nav-links');

    const toggleButton = document.createElement('button');
    toggleButton.type = 'button';
    toggleButton.className = 'nav-toggle';
    toggleButton.setAttribute('aria-expanded', 'false');
    toggleButton.setAttribute('aria-label', 'Ouvrir le menu');
    toggleButton.innerHTML = '<span></span><span></span><span></span>';

    const insertTarget = navLinks || navWrap.firstElementChild;
    if (insertTarget) {
        navWrap.insertBefore(toggleButton, insertTarget);
    }

    const closeMenu = () => {
        navbar.classList.remove('menu-open');
        toggleButton.setAttribute('aria-expanded', 'false');
        toggleButton.setAttribute('aria-label', 'Ouvrir le menu');
    };

    const toggleMenu = () => {
        const isOpen = navbar.classList.toggle('menu-open');
        toggleButton.setAttribute('aria-expanded', String(isOpen));
        toggleButton.setAttribute('aria-label', isOpen ? 'Fermer le menu' : 'Ouvrir le menu');
    };

    toggleButton.addEventListener('click', toggleMenu);

    document.addEventListener('click', (event) => {
        if (!navbar.contains(event.target)) {
            closeMenu();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            closeMenu();
        }
    });
});

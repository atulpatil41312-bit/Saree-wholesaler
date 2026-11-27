(() => {
    'use strict';

    const boot = () => {
        const root = document.querySelector('.wm-marketplace');

        if (!root) {
            return;
        }

        const menuButton = root.querySelector('[data-wm-menu]');
        const navigation = root.querySelector('.wm-nav');

        if (menuButton && navigation) {
            menuButton.addEventListener('click', () => {
                const isOpen = navigation.classList.toggle('is-open');
                menuButton.setAttribute('aria-expanded', String(isOpen));
                menuButton.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Toggle navigation');
            });

            navigation.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => {
                    navigation.classList.remove('is-open');
                    menuButton.setAttribute('aria-expanded', 'false');
                    menuButton.setAttribute('aria-label', 'Toggle navigation');
                });
            });
        }

        document.documentElement.classList.add('wm-market-motion');

        const revealTargets = root.querySelectorAll('[data-wm-reveal]');

        if (!('IntersectionObserver' in window)) {
            revealTargets.forEach((target) => target.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries, instance) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                instance.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

        revealTargets.forEach((target) => observer.observe(target));
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();

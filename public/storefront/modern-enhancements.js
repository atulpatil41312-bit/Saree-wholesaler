(function () {
    'use strict';

    const boot = function () {
        if (!document.body.classList.contains('wm-storefront-page')) {
            return;
        }

        document.documentElement.classList.add('wm-motion-ready');

        const fallbackSlides = Array.isArray(window.WM_FALLBACK_SLIDES)
            ? window.WM_FALLBACK_SLIDES.filter(Boolean)
            : [];
        const heroSelectors = [
            '.hero-image',
            '.stp-hero-visual',
            '.ve-hero-image',
            '.wta-hero-art',
            '.iw-hero-image',
            '.le-hero-image',
            '.wm .hero-image',
        ];
        const productSelectors = [
            '.product-image img',
            '.stp-product-photo img',
            '.ve-product-image img',
            '.wta-product-image img',
            '.iw-product-photo img',
            '.le-product-image img',
            '.wm .card img',
        ];
        const productSlides = Array.from(document.querySelectorAll(productSelectors.join(',')))
            .map((image) => image.currentSrc || image.src)
            .filter(Boolean);

        const unique = (values) => values.filter((value, index) => value && values.indexOf(value) === index);

        const buildCarousel = (hero, index) => {
            if (hero.dataset.wmCarouselReady === 'true') {
                return;
            }

            const original = hero.querySelector(':scope > img') || hero.querySelector('img');
            if (!original) {
                return;
            }

            const sources = unique([
                original.currentSrc || original.src,
                ...productSlides,
                ...fallbackSlides,
            ]).slice(0, 5);

            if (sources.length < 2) {
                return;
            }

            hero.dataset.wmCarouselReady = 'true';
            hero.classList.add('wm-hero-carousel');
            original.classList.add('wm-hero-original');

            const slides = document.createElement('div');
            slides.className = 'wm-hero-carousel__slides';
            slides.setAttribute('aria-label', 'Featured saree images');

            sources.forEach((source, slideIndex) => {
                const image = document.createElement('img');
                image.className = 'wm-hero-carousel__slide' + (slideIndex === 0 ? ' is-active' : '');
                image.src = source;
                image.alt = slideIndex === 0 ? (original.alt || 'Featured saree') : '';
                image.loading = slideIndex === 0 ? 'eager' : 'lazy';
                image.decoding = 'async';
                slides.appendChild(image);
            });

            const controls = document.createElement('div');
            controls.className = 'wm-hero-carousel__controls';
            controls.setAttribute('aria-label', 'Featured saree slideshow controls');

            const previous = document.createElement('button');
            previous.type = 'button';
            previous.setAttribute('aria-label', 'Previous featured image');
            previous.textContent = '‹';

            const counter = document.createElement('span');
            counter.className = 'wm-hero-carousel__counter';
            counter.setAttribute('aria-live', 'polite');

            const next = document.createElement('button');
            next.type = 'button';
            next.setAttribute('aria-label', 'Next featured image');
            next.textContent = '›';

            controls.append(previous, counter, next);
            hero.prepend(slides, controls);

            const slideElements = Array.from(slides.children);
            let activeIndex = 0;
            let timer = null;

            const render = (nextIndex) => {
                activeIndex = (nextIndex + slideElements.length) % slideElements.length;
                slideElements.forEach((slide, slideIndex) => {
                    slide.classList.toggle('is-active', slideIndex === activeIndex);
                });
                counter.textContent = String(activeIndex + 1).padStart(2, '0') + ' / ' + String(slideElements.length).padStart(2, '0');
            };

            const stop = () => {
                if (timer !== null) {
                    window.clearInterval(timer);
                    timer = null;
                }
            };

            const start = () => {
                stop();
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }
                timer = window.setInterval(() => render(activeIndex + 1), 5600);
            };

            previous.addEventListener('click', () => {
                render(activeIndex - 1);
                start();
            });
            next.addEventListener('click', () => {
                render(activeIndex + 1);
                start();
            });
            hero.addEventListener('mouseenter', stop);
            hero.addEventListener('mouseleave', start);
            hero.addEventListener('focusin', stop);
            hero.addEventListener('focusout', start);
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stop();
                } else {
                    start();
                }
            });

            render(index % slideElements.length);
            start();
        };

        unique(heroSelectors).forEach((selector, index) => {
            document.querySelectorAll(selector).forEach((hero) => buildCarousel(hero, index));
        });

        const revealTargets = document.querySelectorAll('main > section, .wm-site-footer');
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries, instance) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        instance.unobserve(entry.target);
                    }
                });
            }, { threshold: .12 });
            revealTargets.forEach((target) => {
                target.classList.add('wm-reveal');
                observer.observe(target);
            });
        } else {
            revealTargets.forEach((target) => target.classList.add('is-visible'));
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();

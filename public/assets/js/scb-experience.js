(() => {
    const qs = (selector, context = document) => context.querySelector(selector);
    const qsa = (selector, context = document) => [...context.querySelectorAll(selector)];
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    qsa('.hero').forEach(hero => requestAnimationFrame(() => hero.classList.add('is-ready')));

    const iconForLabel = label => {
        const normalized = label.trim().toLowerCase();
        const icons = {
            dashboard: 'layout-dashboard',
            pages: 'files',
            news: 'newspaper',
            events: 'calendar-days',
            galleries: 'images',
            gallery: 'images',
            downloads: 'download',
            staff: 'users-round',
            programmes: 'book-open-check',
            admissions: 'clipboard-list',
            messages: 'mail',
            'media library': 'image',
            users: 'user-cog',
            'roles & permissions': 'shield-check',
            'site settings': 'settings-2',
            'audit log': 'history',
            'view website': 'external-link',
            'sign out': 'log-out',
            'school calendar': 'calendar-days',
            'staff resources': 'folder-down',
            'news contributions': 'file-pen-line',
            'staff directory': 'contact-round',
            'my profile': 'user-round-cog',
        };

        return icons[normalized] ?? null;
    };

    qsa('.admin-nav a, .staff-nav a').forEach(link => {
        const icon = iconForLabel(link.textContent);
        const holder = qs('.nav-ico, i', link);
        if (icon && holder) holder.innerHTML = '<i data-lucide="' + icon + '" aria-hidden="true"></i>';
    });

    qsa('.icon-btn[title]').forEach(button => {
        const title = button.getAttribute('title').toLowerCase();
        let icon = null;
        let tone = null;

        if (/(delete|remove)/.test(title)) [icon, tone] = ['trash-2', 'danger'];
        else if (/(view|preview|open)/.test(title)) [icon, tone] = ['eye', 'view'];
        else if (/(save|edit)/.test(title)) [icon, tone] = [title.startsWith('save') ? 'check' : 'pencil', 'edit'];
        else if (/publish/.test(title)) [icon, tone] = ['send', 'publish'];
        else if (/archive/.test(title)) [icon, tone] = ['archive', 'publish'];
        else if (/duplicate/.test(title)) [icon, tone] = ['copy-plus', 'edit'];
        else if (/move up/.test(title)) icon = 'arrow-up';
        else if (/move down/.test(title)) icon = 'arrow-down';
        else if (/note/.test(title)) [icon, tone] = ['message-square-plus', 'edit'];
        else if (/role/.test(title)) [icon, tone] = ['shield-check', 'publish'];

        if (!icon) return;
        button.innerHTML = '<i data-lucide="' + icon + '" aria-hidden="true"></i>';
        if (tone) button.classList.add('action-' + tone);
    });

    const metricIcons = {
        'published pages': 'files',
        'upcoming events': 'calendar-clock',
        'new enquiries': 'message-circle-more',
        'media files': 'images',
    };
    qsa('.metric').forEach(metric => {
        const label = qs('.metric-top span', metric)?.textContent.trim().toLowerCase();
        const holder = qs('.metric .icon', metric);
        if (holder && metricIcons[label]) holder.innerHTML = '<i data-lucide="' + metricIcons[label] + '" aria-hidden="true"></i>';
    });

    const loader = qs('[data-site-loader]');
    const loaderStartedAt = performance.now();

    const hideLoader = () => {
        if (!loader) return;

        const remaining = Math.max(0, 420 - (performance.now() - loaderStartedAt));
        window.setTimeout(() => {
            loader.classList.add('is-leaving');
            window.setTimeout(() => loader.setAttribute('hidden', ''), 420);
        }, remaining);
    };

    const showLoader = () => {
        if (!loader) return;
        loader.removeAttribute('hidden');
        requestAnimationFrame(() => loader.classList.remove('is-leaving'));
    };

    if (document.readyState === 'complete') hideLoader();
    else window.addEventListener('load', hideLoader, { once: true });
    window.addEventListener('pageshow', event => {
        if (event.persisted) hideLoader();
    });

    qsa('form').forEach(form => form.addEventListener('submit', () => {
        if (form.checkValidity()) showLoader();
    }));

    qsa('a[href]').forEach(link => link.addEventListener('click', event => {
        if (event.defaultPrevented || event.button !== 0 || link.target === '_blank' || link.hasAttribute('download')) return;

        const url = new URL(link.href, window.location.href);
        const sameDocumentHash = url.pathname === location.pathname && url.search === location.search && url.hash;
        if (url.origin === location.origin && !sameDocumentHash) showLoader();
    }));

    qsa('[data-toggle-details]').forEach(button => button.addEventListener('click', () => {
        const panel = document.getElementById(button.dataset.toggleDetails);
        if (!(panel instanceof HTMLDetailsElement)) return;

        panel.open = true;
        panel.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
        window.setTimeout(() => qs('input, textarea, select', panel)?.focus(), 250);
    }));

    if (location.hash) {
        const target = document.getElementById(location.hash.slice(1));
        if (target instanceof HTMLDetailsElement) target.open = true;
    }

    const revealTargets = qsa('.section-head, .card, .feature-card, .event-row, .gallery-item, .admin-panel, .metric, .staff-card');
    revealTargets.forEach((element, index) => {
        element.dataset.reveal = '';
        element.style.setProperty('--reveal-delay', `${Math.min(index % 4, 3) * 70}ms`);
    });

    if ('IntersectionObserver' in window && !reducedMotion) {
        document.documentElement.classList.add('motion-ready');
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: .08, rootMargin: '0px 0px -35px' });
        revealTargets.forEach(element => observer.observe(element));
    } else {
        revealTargets.forEach(element => element.classList.add('is-visible'));
    }

    const filterElements = (query, elements) => {
        const normalized = query.trim().toLowerCase();
        elements.forEach(element => {
            element.toggleAttribute('data-client-hidden', normalized !== '' && !element.textContent.toLowerCase().includes(normalized));
        });
    };

    const adminSearch = qs('.admin-search input');
    if (adminSearch) {
        adminSearch.setAttribute('aria-label', 'Search visible admin records');
        adminSearch.addEventListener('input', () => {
            filterElements(adminSearch.value, qsa('.admin-content tbody tr, .admin-content .media-item, .admin-content .permission-group, .admin-content .activity-item'));
        });
    }

    const staffSearch = qs('[data-staff-search]');
    if (staffSearch) {
        staffSearch.addEventListener('input', () => filterElements(staffSearch.value, qsa('.staff-list-item')));
    }

    qsa('.admin-panel .toolbar').forEach(toolbar => {
        const button = qsa('button', toolbar).find(item => item.textContent.trim().toLowerCase() === 'filter');
        const input = qs('input', toolbar);
        const select = qs('select', toolbar);
        if (!button || (!input && !select)) return;

        button.type = 'button';
        const apply = () => {
            const panel = toolbar.closest('.admin-panel');
            const rows = qsa('tbody tr', panel);
            const query = input?.value.trim().toLowerCase() ?? '';
            const status = select?.selectedIndex > 0 ? select.options[select.selectedIndex].text.trim().toLowerCase() : '';

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.toggleAttribute('data-client-hidden', (query !== '' && !text.includes(query)) || (status !== '' && !text.includes(status)));
            });
        };

        button.addEventListener('click', apply);
        input?.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                apply();
            }
        });
    });

    qsa('table').forEach(table => {
        const master = qs('thead input[type="checkbox"]', table);
        if (!master) return;
        const rows = qsa('tbody input[type="checkbox"]', table);
        master.addEventListener('change', () => rows.forEach(checkbox => checkbox.checked = master.checked));
        rows.forEach(checkbox => checkbox.addEventListener('change', () => {
            master.checked = rows.length > 0 && rows.every(item => item.checked);
            master.indeterminate = !master.checked && rows.some(item => item.checked);
        }));
    });

    qsa('[data-hero-carousel]').forEach(carousel => {
        const slides = qsa('[data-carousel-slide]', carousel);
        const dots = qsa('[data-carousel-dot]', carousel);
        if (slides.length < 2) return;

        let index = Math.max(0, slides.findIndex(slide => slide.classList.contains('is-active')));
        let timer = null;
        let touchStart = null;

        const show = nextIndex => {
            index = (nextIndex + slides.length) % slides.length;
            slides.forEach((slide, slideIndex) => {
                const active = slideIndex === index;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
            });
            dots.forEach((dot, dotIndex) => {
                const active = dotIndex === index;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-current', active ? 'true' : 'false');
            });
        };

        const stop = () => {
            if (timer) window.clearInterval(timer);
            timer = null;
        };
        const start = () => {
            stop();
            if (!reducedMotion && !document.hidden) timer = window.setInterval(() => show(index + 1), 6500);
        };

        qs('[data-carousel-previous]', carousel)?.addEventListener('click', () => {
            show(index - 1);
            start();
        });
        qs('[data-carousel-next]', carousel)?.addEventListener('click', () => {
            show(index + 1);
            start();
        });
        dots.forEach(dot => dot.addEventListener('click', () => {
            show(Number(dot.dataset.carouselDot));
            start();
        }));
        carousel.addEventListener('mouseenter', stop);
        carousel.addEventListener('mouseleave', start);
        carousel.addEventListener('focusin', stop);
        carousel.addEventListener('focusout', start);
        carousel.addEventListener('touchstart', event => {
            touchStart = event.changedTouches[0]?.clientX ?? null;
        }, { passive: true });
        carousel.addEventListener('touchend', event => {
            if (touchStart === null) return;
            const distance = (event.changedTouches[0]?.clientX ?? touchStart) - touchStart;
            if (Math.abs(distance) > 45) show(index + (distance < 0 ? 1 : -1));
            touchStart = null;
            start();
        }, { passive: true });
        document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
        start();
    });

    qsa('[data-featured-image-picker]').forEach(picker => {
        const select = qs('[data-featured-image-select]', picker);
        const file = qs('[data-featured-image-file]', picker);
        const preview = qs('[data-featured-image-preview]', picker);
        const empty = qs('[data-featured-image-empty]', picker);
        const name = qs('[data-featured-image-name]', picker);
        const state = qs('[data-featured-image-state]', picker);
        const alt = qs('[data-featured-image-alt]', picker);
        const safeguard = qs('input[name="featured_image_safeguarding_confirmed"]', picker);
        let objectUrl = null;

        const releaseObjectUrl = () => {
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        };

        const showPreview = (source, alternativeText) => {
            if (!preview || !empty) return;

            if (!source) {
                preview.removeAttribute('src');
                preview.hidden = true;
                empty.hidden = false;
                return;
            }

            preview.src = source;
            preview.alt = alternativeText || '';
            preview.hidden = false;
            empty.hidden = true;
        };

        select?.addEventListener('change', () => {
            releaseObjectUrl();
            if (file) file.value = '';
            if (alt) alt.required = false;
            if (safeguard) safeguard.required = false;

            const option = select.selectedOptions[0];
            showPreview(option?.dataset.preview || '', option?.dataset.alt || '');
            if (name) name.textContent = option?.value ? option.textContent.split(' · ')[0] : 'No featured image selected';
            if (state) state.textContent = option?.dataset.state || 'Optional';
        });

        file?.addEventListener('change', () => {
            releaseObjectUrl();
            const uploadedFile = file.files?.[0];
            const hasUpload = uploadedFile instanceof File;
            if (alt) alt.required = hasUpload;
            if (safeguard) safeguard.required = hasUpload;
            if (!hasUpload) {
                select?.dispatchEvent(new Event('change'));
                return;
            }

            objectUrl = URL.createObjectURL(uploadedFile);
            showPreview(objectUrl, alt?.value || 'New image preview');
            if (name) name.textContent = uploadedFile.name;
            if (state) state.textContent = 'New upload · In review';
        });

        alt?.addEventListener('input', () => {
            if (preview && file?.files?.length) preview.alt = alt.value;
        });

        window.addEventListener('pagehide', releaseObjectUrl, { once: true });
    });

    qsa('input[type="file"][data-upload-max-mb]').forEach(input => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            const maximumMegabytes = Number(input.dataset.uploadMaxMb || 0);
            const exceedsLimit = file && maximumMegabytes > 0
                && file.size > maximumMegabytes * 1024 * 1024;
            const message = exceedsLimit
                ? `${file.name} is too large. Choose a file no larger than ${maximumMegabytes} MB.`
                : '';

            input.setCustomValidity(message);
            if (message) input.reportValidity();
        });
    });

    qsa('[data-media-upload-file]').forEach(input => {
        const form = input.closest('form');
        const alternativeText = form ? qs('[data-media-upload-alt]', form) : null;

        input.addEventListener('change', () => {
            if (alternativeText) {
                alternativeText.required = input.files?.[0]?.type.startsWith('image/') || false;
            }
        });
    });

    window.lucide?.createIcons({
        attrs: {
            'aria-hidden': 'true',
            'stroke-width': 1.9,
        },
    });
})();

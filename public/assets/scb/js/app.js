const qs = (selector, context = document) => context.querySelector(selector);
const qsa = (selector, context = document) => [...context.querySelectorAll(selector)];

const navToggle = qs('.nav-toggle');
const mainNav = qs('.main-nav');

if (navToggle && mainNav) {
    navToggle.setAttribute('aria-expanded', 'false');
    navToggle.addEventListener('click', () => {
        const open = mainNav.classList.toggle('open');
        navToggle.setAttribute('aria-expanded', String(open));
        navToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    });

    document.addEventListener('click', event => {
        if (!mainNav.classList.contains('open') || mainNav.contains(event.target) || navToggle.contains(event.target)) return;
        mainNav.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
        navToggle.setAttribute('aria-label', 'Open menu');
    });
}

qsa('.accordion button').forEach(button => {
    const content = button.nextElementSibling;
    button.setAttribute('aria-expanded', String(button.parentElement.classList.contains('open')));
    if (content) {
        content.id ||= `accordion-${Math.random().toString(36).slice(2)}`;
        button.setAttribute('aria-controls', content.id);
    }

    button.addEventListener('click', () => {
        const open = button.parentElement.classList.toggle('open');
        button.setAttribute('aria-expanded', String(open));
    });
});

qsa('[data-filter]').forEach(button => button.addEventListener('click', () => {
    qsa('[data-filter]').forEach(item => item.classList.remove('active'));
    button.classList.add('active');
    const filter = button.dataset.filter;
    qsa('[data-category]').forEach(item => {
        item.style.display = filter === 'all' || item.dataset.category === filter ? 'block' : 'none';
    });
}));

const galleryItems = qsa('[data-gallery-item]');
const galleryFilters = qsa('[data-gallery-filter]');
const galleryCategory = qs('[data-gallery-category]');
const galleryCount = qs('[data-gallery-count]');
const galleryEmpty = qs('[data-gallery-empty]');
let selectedGallery = 'all';

const applyGalleryFilters = () => {
    const category = galleryCategory?.value || 'all';
    let visibleCount = 0;

    galleryItems.forEach(item => {
        const visible = (selectedGallery === 'all' || item.dataset.album === selectedGallery)
            && (category === 'all' || item.dataset.category === category);
        item.hidden = !visible;
        if (visible) visibleCount += 1;
    });

    if (galleryCount) galleryCount.textContent = `${visibleCount} ${visibleCount === 1 ? 'photograph' : 'photographs'}`;
    if (galleryEmpty) galleryEmpty.hidden = visibleCount !== 0;
};

galleryFilters.forEach(button => button.addEventListener('click', () => {
    selectedGallery = button.dataset.galleryFilter || 'all';
    galleryFilters.forEach(filter => {
        const active = filter === button;
        filter.classList.toggle('active', active);
        filter.setAttribute('aria-pressed', String(active));
    });
    applyGalleryFilters();
}));
galleryCategory?.addEventListener('change', applyGalleryFilters);

const modal = qs('[data-gallery-modal]');
const modalImage = modal ? qs('img', modal) : null;
const modalCaption = modal ? qs('[data-gallery-modal-caption]', modal) : null;
let lastGalleryTrigger = null;

const closeModal = () => {
    if (!modal || modal.hidden) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    modal.hidden = true;
    document.body.classList.remove('gallery-modal-open');
    lastGalleryTrigger?.focus();
};

qsa('[data-gallery-open]').forEach(button => {
    button.addEventListener('click', () => {
        if (!modal || !modalImage) return;
        const item = button.closest('[data-gallery-item]');
        const image = qs('img', button);
        if (!item || !image) return;

        lastGalleryTrigger = button;
        modalImage.src = item.dataset.full || image.currentSrc || image.src;
        modalImage.srcset = image.srcset;
        modalImage.sizes = '100vw';
        modalImage.alt = image.alt || item.dataset.label || 'Expanded gallery photograph';
        if (modalCaption) modalCaption.textContent = item.dataset.label || image.alt || '';
        modal.hidden = false;
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('gallery-modal-open');
        qs('.modal-close', modal)?.focus();
    });
});

qsa('.modal-close', modal || document).forEach(button => button.addEventListener('click', closeModal));
modal?.addEventListener('click', event => {
    if (event.target === modal) closeModal();
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closeModal();
    if (event.key === 'Tab' && modal && !modal.hidden) {
        event.preventDefault();
        qs('.modal-close', modal)?.focus();
    }
});

qsa('[data-tab]').forEach(button => button.addEventListener('click', () => {
    const root = button.closest('[data-tabs-root]') || document;
    qsa('[data-tab]', root).forEach(item => item.classList.remove('active'));
    qsa('.tab-pane', root).forEach(item => item.classList.remove('active'));
    button.classList.add('active');
    qs(`#${button.dataset.tab}`, root)?.classList.add('active');
}));

qsa('[data-toast]').forEach(button => button.addEventListener('click', () => {
    const toast = qs('.toast');
    if (!toast) return;
    toast.textContent = button.dataset.toast || 'Saved successfully';
    toast.classList.add('show');
    window.setTimeout(() => toast.classList.remove('show'), 2600);
}));

qsa('[data-admin-menu]').forEach(button => {
    button.type = 'button';
    button.setAttribute('aria-expanded', 'false');
    button.addEventListener('click', () => {
        const open = qs('.admin-sidebar')?.classList.toggle('open') ?? false;
        button.setAttribute('aria-expanded', String(open));
    });
});

const cookie = qs('.cookie');
const cookieKey = 'scb-cookie';
const readCookiePreference = () => {
    try {
        return window.localStorage.getItem(cookieKey);
    } catch {
        return null;
    }
};
const writeCookiePreference = value => {
    try {
        window.localStorage.setItem(cookieKey, value);
    } catch {
        // The preference remains session-only when storage is unavailable.
    }
};
const removeCookiePreference = () => {
    try {
        window.localStorage.removeItem(cookieKey);
    } catch {
        // The dialog can still be reopened for this page view.
    }
};
const setCookieDialogOpen = open => {
    if (!cookie) return;
    cookie.classList.toggle('show', open);
    cookie.setAttribute('aria-hidden', open ? 'false' : 'true');
};
const loadAnalytics = () => {
    const configElement = document.getElementById('analytics-config');
    if (!configElement || document.querySelector('script[data-scb-analytics]')) return;

    try {
        const config = JSON.parse(configElement.textContent);
        if (config.provider !== 'plausible' || !config.siteId) return;

        const script = document.createElement('script');
        script.defer = true;
        script.dataset.domain = config.siteId;
        script.dataset.scbAnalytics = 'plausible';
        script.src = 'https://plausible.io/js/script.js';
        document.head.appendChild(script);
    } catch {
        // Invalid analytics settings fail closed without affecting the website.
    }
};

const cookiePreference = readCookiePreference();
if (cookiePreference === 'accepted') loadAnalytics();
if (cookie && !cookiePreference) window.setTimeout(() => setCookieDialogOpen(true), 500);

qsa('[data-cookie]').forEach(button => button.addEventListener('click', () => {
    const preference = button.dataset.cookie;
    writeCookiePreference(preference);
    setCookieDialogOpen(false);
    if (preference === 'accepted') loadAnalytics();
}));

qsa('[data-cookie-reset]').forEach(button => button.addEventListener('click', () => {
    removeCookiePreference();
    setCookieDialogOpen(true);
    qs('[data-cookie="accepted"]', cookie)?.focus();
}));

const newsAlert = qs('[data-recent-news-alert]');
if (newsAlert) {
    const alertKey = `scb-dismissed-${newsAlert.dataset.alertId}`;
    try {
        if (window.localStorage.getItem(alertKey) === '1') newsAlert.hidden = true;
    } catch {
        // The alert remains visible when preference storage is unavailable.
    }

    qs('[data-news-alert-dismiss]', newsAlert)?.addEventListener('click', () => {
        newsAlert.hidden = true;
        try {
            window.localStorage.setItem(alertKey, '1');
        } catch {
            // Dismissal still applies for the current page view.
        }
    });
}

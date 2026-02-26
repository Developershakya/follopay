/**
 * ============================================================
 *  AppIconLoader — Reusable App Icon System
 *  js/app-icon-loader.js
 *
 *  Kaise use karo:
 *
 *  1. Script include karo:
 *     <script src="js/app-icon-loader.js"></script>
 *
 *  2. Kisi bhi <img> tag pe data-pkg attribute lagao:
 *     <img data-pkg="com.whatsapp" class="app-icon" src="">
 *
 *  3. Ek baar init karo (page load pe):
 *     AppIconLoader.init();
 *
 *  Ya manually kisi ek image load karo:
 *     AppIconLoader.load(imgElement, 'com.whatsapp');
 *
 *  Ya URL se automatically detect karo:
 *     AppIconLoader.fromUrl(imgElement, 'https://play.google.com/store/apps/details?id=com.whatsapp');
 * ============================================================
 */

const AppIconLoader = (() => {

    // ── Config ──
    const API_URL   = 'ajax/app_icon.php';   // apna backend endpoint
    const FALLBACK  = 'https://www.google.com/s2/favicons?sz=128&domain=play.google.com';

    // ── Runtime cache (session ke andar dobara fetch nahi) ──
    const cache = {};

    // ── Known non-PlayStore domains → seedha favicon ──
    const DOMAIN_MAP = {
        'youtube.com':    'https://www.google.com/s2/favicons?sz=128&domain=youtube.com',
        'youtu.be':       'https://www.google.com/s2/favicons?sz=128&domain=youtube.com',
        'instagram.com':  'https://www.google.com/s2/favicons?sz=128&domain=instagram.com',
        'facebook.com':   'https://www.google.com/s2/favicons?sz=128&domain=facebook.com',
        'twitter.com':    'https://www.google.com/s2/favicons?sz=128&domain=x.com',
        'x.com':          'https://www.google.com/s2/favicons?sz=128&domain=x.com',
        'maps.google':    'https://www.google.com/s2/favicons?sz=128&domain=maps.google.com',
        'maps.app':       'https://www.google.com/s2/favicons?sz=128&domain=maps.google.com',
        'whatsapp.com':   'https://www.google.com/s2/favicons?sz=128&domain=whatsapp.com',
        'snapchat.com':   'https://www.google.com/s2/favicons?sz=128&domain=snapchat.com',
        'linkedin.com':   'https://www.google.com/s2/favicons?sz=128&domain=linkedin.com',
        'spotify.com':    'https://www.google.com/s2/favicons?sz=128&domain=spotify.com',
        'amazon.in':      'https://www.google.com/s2/favicons?sz=128&domain=amazon.in',
        'amazon.com':     'https://www.google.com/s2/favicons?sz=128&domain=amazon.com',
        'flipkart.com':   'https://www.google.com/s2/favicons?sz=128&domain=flipkart.com',
        'phonepe.com':    'https://www.google.com/s2/favicons?sz=128&domain=phonepe.com',
        'paytm.com':      'https://www.google.com/s2/favicons?sz=128&domain=paytm.com',
        'swiggy.com':     'https://www.google.com/s2/favicons?sz=128&domain=swiggy.com',
        'zomato.com':     'https://www.google.com/s2/favicons?sz=128&domain=zomato.com',
    };

    // ============================================================
    //  PUBLIC API
    // ============================================================

    /**
     * Page pe saare [data-pkg] aur [data-app-url] images auto-load karo
     * Call karo: AppIconLoader.init()
     */
    function init() {
        // data-pkg wale images (package name directly diya hai)
        document.querySelectorAll('img[data-pkg]').forEach(img => {
            load(img, img.dataset.pkg);
        });

        // data-app-url wale images (Play Store ya koi bhi URL)
        document.querySelectorAll('img[data-app-url]').forEach(img => {
            fromUrl(img, img.dataset.appUrl);
        });
    }

    /**
     * Ek image ke liye package name se icon load karo
     * @param {HTMLImageElement} imgEl  - target <img> element
     * @param {string}           pkg    - e.g. "com.whatsapp"
     */
    function load(imgEl, pkg) {
        if (!imgEl || !pkg) return;

        showShimmer(imgEl);

        // Cache hit
        if (cache[pkg]) { applyIcon(imgEl, cache[pkg]); return; }

        fetch(`${API_URL}?pkg=${encodeURIComponent(pkg)}`)
            .then(r => r.json())
            .then(d => {
                const icon = (d.success && d.icon) ? d.icon : FALLBACK;
                cache[pkg] = icon;
                applyIcon(imgEl, icon);
            })
            .catch(() => applyIcon(imgEl, FALLBACK));
    }

    /**
     * URL se automatically detect karke icon load karo
     * Play Store URL → package extract → backend fetch
     * Doosra URL → domain favicon
     * @param {HTMLImageElement} imgEl
     * @param {string}           url   - app link (Play Store ya koi bhi)
     */
    function fromUrl(imgEl, url) {
        if (!imgEl || !url) { applyIcon(imgEl, FALLBACK); return; }

        const lower = url.toLowerCase();

        // ── Play Store ──
        if (lower.includes('play.google.com')) {
            const pkg = extractPackage(url);
            if (pkg) { load(imgEl, pkg); return; }
        }

        // ── Known domain map ──
        for (const [domain, iconUrl] of Object.entries(DOMAIN_MAP)) {
            if (lower.includes(domain)) {
                applyIcon(imgEl, iconUrl);
                return;
            }
        }

        // ── Generic domain favicon ──
        try {
            const u      = new URL(url.startsWith('http') ? url : 'https://' + url);
            const domain = u.hostname.replace('www.', '');
            applyIcon(imgEl, `https://www.google.com/s2/favicons?sz=128&domain=${domain}`);
        } catch(e) {
            applyIcon(imgEl, FALLBACK);
        }
    }

    /**
     * Batch load — ek saath multiple packages fetch karo (1 API call)
     * @param {Array} items  - [{ imgEl, pkg }, ...]
     */
    function loadBatch(items) {
        if (!items || !items.length) return;

        // Already cached ones — seedha apply karo
        const toFetch = [];
        items.forEach(({ imgEl, pkg }) => {
            showShimmer(imgEl);
            if (cache[pkg]) {
                applyIcon(imgEl, cache[pkg]);
            } else {
                toFetch.push({ imgEl, pkg });
            }
        });

        if (!toFetch.length) return;

        const pkgList = [...new Set(toFetch.map(i => i.pkg))].join(',');

        fetch(`${API_URL}?batch=${encodeURIComponent(pkgList)}`)
            .then(r => r.json())
            .then(data => {
                toFetch.forEach(({ imgEl, pkg }) => {
                    const icon = data[pkg] || FALLBACK;
                    cache[pkg] = icon;
                    applyIcon(imgEl, icon);
                });
            })
            .catch(() => {
                toFetch.forEach(({ imgEl }) => applyIcon(imgEl, FALLBACK));
            });
    }

    // ============================================================
    //  INTERNAL HELPERS
    // ============================================================

    /** Show shimmer placeholder jab tak icon load na ho */
    function showShimmer(imgEl) {
        if (!imgEl) return;
        imgEl.style.opacity = '0';
        // Parent pe shimmer class lagao agar nahi hai
        const parent = imgEl.parentElement;
        if (parent && !parent.querySelector('.ail-shimmer')) {
            const sh = document.createElement('div');
            sh.className = 'ail-shimmer';
            sh.style.cssText = [
                'position:absolute', 'inset:0', 'border-radius:inherit',
                'background:linear-gradient(90deg,#f3f4f6 25%,#e5e7eb 50%,#f3f4f6 75%)',
                'background-size:200% 100%',
                'animation:ailShimmer 1.2s infinite',
            ].join(';');
            parent.style.position = 'relative';
            parent.appendChild(sh);
        }
    }

    /** Icon set karo, shimmer hatao */
    function applyIcon(imgEl, iconUrl) {
        if (!imgEl) return;

        const cleanup = () => {
            imgEl.style.opacity = '1';
            const shimmer = imgEl.parentElement?.querySelector('.ail-shimmer');
            if (shimmer) shimmer.remove();
        };

        imgEl.onerror = () => {
            imgEl.onerror = null;
            imgEl.src = FALLBACK;
            cleanup();
        };
        imgEl.onload = cleanup;
        imgEl.src    = iconUrl;
    }

    /** Play Store URL se package name nikalo */
    function extractPackage(url) {
        try {
            const m = url.match(/[?&]id=([a-zA-Z0-9._]+)/);
            return m ? m[1] : null;
        } catch(e) { return null; }
    }

    // Shimmer CSS inject (ek baar)
    (function injectCSS() {
        if (document.getElementById('ail-style')) return;
        const style = document.createElement('style');
        style.id = 'ail-style';
        style.textContent = `
            @keyframes ailShimmer {
                0%   { background-position: -200% 0; }
                100% { background-position:  200% 0; }
            }
        `;
        document.head.appendChild(style);
    })();

    // Public methods expose karo
    return { init, load, fromUrl, loadBatch };

})();
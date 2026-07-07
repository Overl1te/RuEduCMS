// RuEduCMS Premium Theme JS
document.addEventListener('DOMContentLoaded', function() {
    initPageLoader();
    initMobileMenu();
    initA11y();
    initCookieBanner();
    initScrollEffects();
    initScrollReveal();
    initParallax();
    initCounterAnimation();
    initAjaxForms();
    initCaptcha();
    initMapFrames();
    initRevealFooter();
    updateHeaderHeight();
    window.addEventListener('resize', function() {
        updateHeaderHeight();
        initRevealFooter();
    });
});

function initRevealFooter() {
    const footer = document.querySelector('.site-footer');
    if (!footer) return;

    const height = footer.offsetHeight;
    document.documentElement.style.setProperty('--footer-reveal-height', height + 'px');
}

window.addEventListener('load', initRevealFooter);

function initPageLoader() {
    const loader = document.getElementById('pageLoader');
    if (!loader) return;

    const hide = function() {
        loader.classList.add('is-done');
        document.body.classList.remove('is-loading');
    };

    if (document.readyState === 'complete') {
        setTimeout(hide, 400);
    } else {
        window.addEventListener('load', function() { setTimeout(hide, 300); });
    }

    setTimeout(hide, 3000);
}

function initMobileMenu() {
    const toggle = document.getElementById('menuToggle');
    const nav = document.getElementById('mainNav');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', function() {
        const open = nav.classList.toggle('open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    nav.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            nav.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
        });
    });
}

function initA11y() {
    const a11yToggle = document.getElementById('a11yToggle');
    if (a11yToggle) {
        a11yToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleA11y();
        });
    }

    const fontSize = localStorage.getItem('a11y_font');
    const colorScheme = localStorage.getItem('a11y_color');
    if (fontSize) setFontSize(fontSize);
    if (colorScheme) setColorScheme(colorScheme);
}

function initCookieBanner() {
    if (!localStorage.getItem('cookies_accepted')) {
        const banner = document.getElementById('cookieBanner');
        if (banner) banner.style.display = 'block';
    }
}

function initScrollEffects() {
    const header = document.querySelector('.site-header');
    const progress = document.getElementById('scrollProgress');
    const backToTop = document.getElementById('backToTop');
    let ticking = false;

    function onScroll() {
        const scrollY = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;

        if (header) {
            header.classList.toggle('is-scrolled', scrollY > 40);
        }

        if (progress && docHeight > 0) {
            progress.style.width = (scrollY / docHeight * 100) + '%';
        }

        if (backToTop) {
            backToTop.classList.toggle('is-visible', scrollY > 500);
        }

        ticking = false;
    }

    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(onScroll);
            ticking = true;
        }
    }, { passive: true });

    if (backToTop) {
        backToTop.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    onScroll();
}

function initScrollReveal() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('[data-animate]').forEach(function(el) {
            el.classList.add('is-visible');
        });
        return;
    }

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px'
    });

    document.querySelectorAll('[data-animate]').forEach(function(el) {
        observer.observe(el);
    });
}

function initParallax() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const orbs = document.querySelectorAll('[data-parallax]');
    if (!orbs.length) return;

    let ticking = false;

    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(function() {
                const scrollY = window.scrollY;
                orbs.forEach(function(orb) {
                    const speed = parseFloat(orb.getAttribute('data-parallax')) || 0.1;
                    orb.style.transform = 'translateY(' + (scrollY * speed) + 'px)';
                });
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
}

function initCounterAnimation() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    const counters = document.querySelectorAll('[data-count]');
    if (!counters.length) return;

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (!entry.isIntersecting) return;

            const el = entry.target;
            const target = parseInt(el.getAttribute('data-count'), 10);
            if (isNaN(target)) return;

            const duration = 1500;
            const start = performance.now();

            function tick(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.round(target * eased) + '+';
                if (progress < 1) requestAnimationFrame(tick);
            }

            requestAnimationFrame(tick);
            observer.unobserve(el);
        });
    }, { threshold: 0.5 });

    counters.forEach(function(el) { observer.observe(el); });
}

function updateHeaderHeight() {
    const header = document.querySelector('.site-header');
    if (header) {
        document.documentElement.style.setProperty('--header-height', header.offsetHeight + 'px');
    }
}

function initMapFrames() {
    document.querySelectorAll('.map-container__embed iframe').forEach(function(frame) {
        frame.style.removeProperty('display');
        frame.style.removeProperty('visibility');
        if (window.getComputedStyle(frame).display === 'none') {
            frame.style.setProperty('display', 'block', 'important');
        }
    });
}

function initAjaxForms() {
    document.querySelectorAll('[data-ajax-form]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const btn = form.querySelector('[type="submit"]');
            const btnText = btn ? btn.textContent : '';
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Отправка…';
            }

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            })
                .then(function(res) {
                    return res.json().then(function(data) {
                        return { res: res, data: data };
                    }).catch(function() {
                        return { res: res, data: {} };
                    });
                })
                .then(function(result) {
                    if (result.res.ok && result.data.ok) {
                        showToast(result.data.message || 'Сообщение отправлено', 'success');
                        form.reset();
                    } else {
                        showToast(result.data.message || 'Не удалось отправить сообщение', 'error');
                        refreshCaptcha(form, result.data.captchaUrl);
                    }
                })
                .catch(function() {
                    showToast('Не удалось отправить сообщение', 'error');
                })
                .finally(function() {
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = btnText;
                    }
                });
        });
    });
}

function toggleA11y() {
    const panel = document.getElementById('a11yPanel');
    if (panel) {
        const visible = panel.style.display !== 'none';
        panel.style.display = visible ? 'none' : 'block';
        if (!visible) document.body.classList.add('a11y-active');
    }
}

function setFontSize(size) {
    document.body.classList.remove('a11y-large', 'a11y-xlarge');
    if (size === 'large') document.body.classList.add('a11y-large');
    if (size === 'xlarge') document.body.classList.add('a11y-xlarge');
    localStorage.setItem('a11y_font', size);
}

function setColorScheme(scheme) {
    document.body.classList.remove('a11y-bw', 'a11y-dark');
    if (scheme === 'bw') document.body.classList.add('a11y-bw');
    if (scheme === 'dark') document.body.classList.add('a11y-dark');
    localStorage.setItem('a11y_color', scheme);
}

function acceptCookies() {
    localStorage.setItem('cookies_accepted', '1');
    const banner = document.getElementById('cookieBanner');
    if (banner) banner.style.display = 'none';
}

function declineCookies() {
    localStorage.setItem('cookies_accepted', '0');
    const banner = document.getElementById('cookieBanner');
    if (banner) banner.style.display = 'none';
}

function initCaptcha() {
    document.querySelectorAll('[data-captcha-refresh]').forEach(function(button) {
        button.addEventListener('click', function() {
            var group = button.closest('[data-captcha-group]') || document;
            refreshCaptcha(group);
        });
    });
}

function refreshCaptcha(scope, imageUrl) {
    var root = scope && scope.querySelector ? scope : document;
    var image = root.querySelector('[data-captcha-image]');
    var answer = root.querySelector('input[name="captcha_answer"]');
  if (answer) {
        answer.value = '';
    }

    if (!image) {
        return;
    }

    if (imageUrl) {
        image.src = imageUrl;
        return;
    }

    fetch(routePath('captcha/refresh'), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.ok && data.imageUrl) {
                image.src = data.imageUrl;
            }
        })
        .catch(function() {
            image.src = image.src.split('?')[0] + '?t=' + Date.now();
        });
}

function routePath(path) {
    var base = document.body.getAttribute('data-base-path') || '';
    if (base && base.slice(-1) === '/') {
        base = base.slice(0, -1);
    }
    return (base ? base + '/' : '/') + path.replace(/^\//, '');
}

function showToast(message, type) {
    type = type || 'success';
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        container.setAttribute('aria-live', 'polite');
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.setAttribute('role', 'status');
    toast.textContent = message;
    container.appendChild(toast);

    requestAnimationFrame(function() {
        toast.classList.add('show');
    });

    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() { toast.remove(); }, 400);
    }, 4000);
}

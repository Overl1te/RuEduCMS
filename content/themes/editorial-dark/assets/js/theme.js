// RuEduCMS Editorial Dark Theme JS
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initA11y();
    initCookieBanner();
    initAjaxForms();
    initCaptcha();
    initMapFrames();
});

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

function initMapFrames() {
    document.querySelectorAll('.ed-map__embed iframe').forEach(function(frame) {
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

function showToast(message, type) {
    type = type || 'success';
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'ed-toast-container';
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

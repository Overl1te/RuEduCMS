// RuEduCMS Theme JS
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu
    const toggle = document.getElementById('menuToggle');
    const nav = document.getElementById('mainNav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const open = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    // A11y toggle
    const a11yToggle = document.getElementById('a11yToggle');
    if (a11yToggle) {
        a11yToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleA11y();
        });
    }

    // Cookie banner
    if (!localStorage.getItem('cookies_accepted')) {
        const banner = document.getElementById('cookieBanner');
        if (banner) banner.style.display = 'block';
    }

    // Restore a11y settings
    const fontSize = localStorage.getItem('a11y_font');
    const colorScheme = localStorage.getItem('a11y_color');
    if (fontSize) setFontSize(fontSize);
    if (colorScheme) setColorScheme(colorScheme);

    document.querySelectorAll('.map-container__embed iframe').forEach(function(frame) {
        frame.style.removeProperty('display');
        frame.style.removeProperty('visibility');
        if (window.getComputedStyle(frame).display === 'none') {
            frame.style.setProperty('display', 'block', 'important');
        }
    });

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
});

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
    document.getElementById('cookieBanner').style.display = 'none';
}

function declineCookies() {
    localStorage.setItem('cookies_accepted', '0');
    document.getElementById('cookieBanner').style.display = 'none';
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
        setTimeout(function() { toast.remove(); }, 300);
    }, 4000);
}

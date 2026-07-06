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

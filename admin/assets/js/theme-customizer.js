(function () {
    'use strict';

    var iframe = document.getElementById('customizerPreview');
    if (!iframe) {
        return;
    }

    var config = window.themeCustomizerConfig || { fontStacks: {}, fontFields: {} };

    function getRoot() {
        try {
            return iframe.contentDocument && iframe.contentDocument.documentElement;
        } catch (e) {
            return null;
        }
    }

    function fontCssValue(key, name) {
        var stack = config.fontFields[key] === 'serif' ? config.fontStacks.serif : config.fontStacks.sans;
        return "'" + name + "', " + stack;
    }

    function applyCssVar(key, value, type) {
        var root = getRoot();
        if (!root) {
            return;
        }
        if (type === 'font') {
            root.style.setProperty(key, fontCssValue(key, value));
            return;
        }
        root.style.setProperty(key, value);
    }

    function syncColorPicker(picker, textInput) {
        var hex = textInput.value.trim();
        if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
            picker.value = hex;
        }
    }

    document.querySelectorAll('.customizer-color').forEach(function (picker) {
        var key = picker.getAttribute('data-css-key');
        var textInput = picker.closest('.customizer-field').querySelector('.customizer-input');

        picker.addEventListener('input', function () {
            textInput.value = picker.value;
            applyCssVar(key, picker.value, 'color');
        });

        textInput.addEventListener('input', function () {
            syncColorPicker(picker, textInput);
            applyCssVar(key, textInput.value, 'color');
        });
    });

    document.querySelectorAll('.customizer-range').forEach(function (range) {
        var key = range.getAttribute('data-css-key');
        var unit = range.getAttribute('data-unit') || 'px';
        var hidden = range.closest('.customizer-field').querySelector('.customizer-input');
        var label = range.closest('.customizer-field').querySelector('.customizer-range-value');

        range.addEventListener('input', function () {
            var val = range.value + unit;
            hidden.value = val;
            if (label) {
                label.textContent = val;
            }
            applyCssVar(key, val, 'range');
        });
    });

    document.querySelectorAll('select.customizer-input').forEach(function (select) {
        var key = select.getAttribute('data-css-key');
        select.addEventListener('change', function () {
            applyCssVar(key, select.value, 'font');
        });
    });

    iframe.addEventListener('load', function () {
        document.querySelectorAll('.customizer-input, .customizer-color').forEach(function (el) {
            var key = el.getAttribute('data-css-key');
            if (!key) {
                return;
            }
            var field = el.closest('.customizer-field');
            var type = field ? field.getAttribute('data-type') : 'text';
            var value = el.type === 'color' ? field.querySelector('.customizer-input').value : el.value;
            if (type === 'color' && el.type === 'color') {
                return;
            }
            applyCssVar(key, value, type);
        });
    });
})();

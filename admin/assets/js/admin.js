// RuEduCMS Admin JS

(function () {
    function normalize(value) {
        return value.toLowerCase().trim();
    }

    function initAutocomplete(input) {
        var raw = input.getAttribute('data-autocomplete');
        if (!raw) {
            return;
        }

        var options;
        try {
            options = JSON.parse(raw);
        } catch (e) {
            return;
        }

        if (!Array.isArray(options) || !options.length) {
            return;
        }

        var wrap = document.createElement('div');
        wrap.className = 'autocomplete-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        var list = document.createElement('div');
        list.className = 'autocomplete-list';
        list.hidden = true;
        wrap.appendChild(list);

        var activeIndex = -1;

        function setActive(items) {
            items.forEach(function (item, index) {
                item.classList.toggle('active', index === activeIndex);
            });
            if (activeIndex >= 0 && items[activeIndex]) {
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            }
        }

        function selectValue(value) {
            input.value = value;
            list.hidden = true;
            activeIndex = -1;
        }

        function render() {
            var query = normalize(input.value);
            var filtered = query
                ? options.filter(function (option) {
                    return normalize(option).indexOf(query) !== -1;
                })
                : options.slice();

            list.innerHTML = '';
            activeIndex = -1;

            if (!filtered.length) {
                list.hidden = true;
                return;
            }

            filtered.slice(0, 20).forEach(function (option) {
                var item = document.createElement('button');
                item.type = 'button';
                item.className = 'autocomplete-item';
                item.textContent = option;
                item.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                    selectValue(option);
                });
                list.appendChild(item);
            });

            list.hidden = false;
        }

        input.addEventListener('input', render);
        input.addEventListener('focus', render);
        input.addEventListener('blur', function () {
            window.setTimeout(function () {
                list.hidden = true;
                activeIndex = -1;
            }, 150);
        });
        input.addEventListener('keydown', function (event) {
            var items = list.querySelectorAll('.autocomplete-item');
            if (list.hidden || !items.length) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                setActive(items);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                setActive(items);
            } else if (event.key === 'Enter' && activeIndex >= 0) {
                event.preventDefault();
                selectValue(items[activeIndex].textContent);
            } else if (event.key === 'Escape') {
                list.hidden = true;
                activeIndex = -1;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-autocomplete]').forEach(initAutocomplete);
    });
})();

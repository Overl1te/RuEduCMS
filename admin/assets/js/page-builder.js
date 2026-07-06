(function () {
    'use strict';

    var config = window.pageBuilderConfig || { blocks: [], blockTypes: {} };
    var blocks = JSON.parse(JSON.stringify(config.blocks || []));
    var blockTypes = config.blockTypes || {};
    var selectedIndex = -1;
    var sortableInstance = null;

    var canvas = document.getElementById('blockCanvas');
    var propsPanel = document.getElementById('blockPropsPanel');
    var blocksInput = document.getElementById('blocksInput');
    var blockCount = document.getElementById('blockCount');

    if (!canvas || !propsPanel || !blocksInput) {
        return;
    }

    function syncInput() {
        blocksInput.value = JSON.stringify(blocks.map(function (b) {
            return { type: b.type, props: b.props || {} };
        }));
        if (blockCount) {
            blockCount.textContent = blocks.length + ' блок(ов)';
        }
    }

    function blockLabel(type) {
        return (blockTypes[type] && blockTypes[type].label) || type;
    }

    function blockIcon(type) {
        return (blockTypes[type] && blockTypes[type].icon) || 'bi-square';
    }

    function blockPreview(props) {
        var keys = ['title', 'subtitle', 'badge', 'text', 'content'];
        for (var i = 0; i < keys.length; i++) {
            var val = props[keys[i]];
            if (val && typeof val === 'string') {
                return val.replace(/<[^>]+>/g, '').slice(0, 80);
            }
        }
        return '';
    }

    function defaultProps(type) {
        var meta = blockTypes[type];
        return meta && meta.defaults ? JSON.parse(JSON.stringify(meta.defaults)) : {};
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function initSortable() {
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
        if (typeof Sortable === 'undefined' || blocks.length === 0) {
            return;
        }
        sortableInstance = new Sortable(canvas, {
            animation: 150,
            handle: '.block-card__handle',
            draggable: '.block-card',
            onEnd: function (evt) {
                if (evt.oldIndex === evt.newIndex) {
                    return;
                }
                var moved = blocks.splice(evt.oldIndex, 1)[0];
                blocks.splice(evt.newIndex, 0, moved);
                selectedIndex = evt.newIndex;
                syncInput();
                updateSelection();
            }
        });
    }

    function updateSelection() {
        canvas.querySelectorAll('.block-card').forEach(function (card) {
            var idx = parseInt(card.dataset.index, 10);
            card.classList.toggle('is-selected', idx === selectedIndex);
        });
    }

    function renderCanvas() {
        canvas.innerHTML = '';

        if (blocks.length === 0) {
            var empty = document.createElement('div');
            empty.className = 'block-canvas__empty text-muted text-center py-5';
            empty.textContent = 'Добавьте блок из палитры слева';
            canvas.appendChild(empty);
            propsPanel.innerHTML = '<p class="text-muted small mb-0">Выберите блок на холсте</p>';
            selectedIndex = -1;
            syncInput();
            initSortable();
            return;
        }

        blocks.forEach(function (block, index) {
            var card = document.createElement('div');
            card.className = 'block-card' + (index === selectedIndex ? ' is-selected' : '');
            card.dataset.index = String(index);
            card.innerHTML =
                '<div class="block-card__header">' +
                    '<span class="block-card__handle" title="Перетащить"><i class="bi bi-grip-vertical"></i></span>' +
                    '<p class="block-card__title"><i class="bi ' + escapeHtml(blockIcon(block.type)) + '"></i> ' + escapeHtml(blockLabel(block.type)) + '</p>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger block-delete" title="Удалить"><i class="bi bi-trash"></i></button>' +
                '</div>' +
                '<div class="block-card__preview">' + escapeHtml(blockPreview(block.props || {})) + '</div>';

            card.addEventListener('click', function (e) {
                if (e.target.closest('.block-delete')) {
                    return;
                }
                selectBlock(index);
            });

            card.querySelector('.block-delete').addEventListener('click', function (e) {
                e.stopPropagation();
                blocks.splice(index, 1);
                if (selectedIndex >= blocks.length) {
                    selectedIndex = blocks.length - 1;
                }
                renderCanvas();
                if (selectedIndex >= 0) {
                    renderProps(selectedIndex);
                }
            });

            canvas.appendChild(card);
        });

        syncInput();
        initSortable();
    }

    function selectBlock(index) {
        selectedIndex = index;
        updateSelection();
        renderProps(index);
    }

    function renderProps(index) {
        var block = blocks[index];
        if (!block) {
            propsPanel.innerHTML = '<p class="text-muted small mb-0">Выберите блок на холсте</p>';
            return;
        }

        var meta = blockTypes[block.type] || {};
        var fields = meta.fields || [];
        var html = '<p class="small fw-semibold mb-3">' + escapeHtml(blockLabel(block.type)) + '</p>';

        fields.forEach(function (field) {
            var key = field.key;
            var val = block.props[key];
            var label = field.label || key;
            var type = field.type || 'text';

            html += '<div class="block-props-field">';
            html += '<label class="form-label">' + escapeHtml(label) + '</label>';

            if (type === 'textarea' || type === 'json') {
                var textVal = type === 'json' ? JSON.stringify(val, null, 2) : (val || '');
                html += '<textarea class="form-control form-control-sm prop-input" data-key="' + escapeHtml(key) + '" data-type="' + type + '" rows="' + (type === 'json' ? 6 : 3) + '">' + escapeHtml(String(textVal)) + '</textarea>';
            } else if (type === 'select') {
                html += '<select class="form-select form-select-sm prop-input" data-key="' + escapeHtml(key) + '" data-type="select">';
                (field.options || []).forEach(function (opt) {
                    html += '<option value="' + escapeHtml(opt) + '"' + (val === opt ? ' selected' : '') + '>' + escapeHtml(opt) + '</option>';
                });
                html += '</select>';
            } else if (type === 'checkbox') {
                html += '<div class="form-check"><input type="checkbox" class="form-check-input prop-input" data-key="' + escapeHtml(key) + '" data-type="checkbox"' + (val ? ' checked' : '') + '></div>';
            } else if (type === 'number') {
                html += '<input type="number" class="form-control form-control-sm prop-input" data-key="' + escapeHtml(key) + '" data-type="number" value="' + escapeHtml(String(val != null ? val : '')) + '">';
            } else {
                html += '<input type="text" class="form-control form-control-sm prop-input" data-key="' + escapeHtml(key) + '" data-type="text" value="' + escapeHtml(String(val != null ? val : '')) + '">';
            }

            html += '</div>';
        });

        propsPanel.innerHTML = html;

        propsPanel.querySelectorAll('.prop-input').forEach(function (input) {
            var eventName = input.type === 'checkbox' || input.tagName === 'SELECT' ? 'change' : 'input';
            input.addEventListener(eventName, function () {
                updateProp(index, input);
            });
        });
    }

    function updateProp(index, input) {
        var key = input.getAttribute('data-key');
        var type = input.getAttribute('data-type');
        var value;

        if (type === 'checkbox') {
            value = input.checked;
        } else if (type === 'number') {
            value = parseInt(input.value, 10) || 0;
        } else if (type === 'json') {
            try {
                value = JSON.parse(input.value || '[]');
                input.classList.remove('is-invalid');
            } catch (e) {
                input.classList.add('is-invalid');
                return;
            }
        } else {
            value = input.value;
        }

        if (!blocks[index].props) {
            blocks[index].props = {};
        }
        blocks[index].props[key] = value;
        syncInput();

        var preview = canvas.querySelector('[data-index="' + index + '"] .block-card__preview');
        if (preview) {
            preview.textContent = blockPreview(blocks[index].props);
        }
    }

    document.querySelectorAll('.palette-block').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var type = btn.getAttribute('data-type');
            blocks.push({
                type: type,
                props: defaultProps(type)
            });
            selectedIndex = blocks.length - 1;
            renderCanvas();
            renderProps(selectedIndex);
        });
    });

    renderCanvas();
    if (blocks.length > 0) {
        selectBlock(0);
    }
})();

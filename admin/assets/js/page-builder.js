(function () {
    'use strict';

    var cfg = window.pageBuilderConfig || {};
    var rows = JSON.parse(JSON.stringify(cfg.rows || []));
    var layouts = cfg.layouts || {};
    var selected = -1;
    var sortable = null;

    var canvas = document.getElementById('rowCanvas');
    var fieldPanel = document.getElementById('fieldEditorPanel');
    var blockStylePanel = document.getElementById('blockStylePanel');
    var elementStylePanel = document.getElementById('elementStylePanel');
    var fieldDataInput = document.getElementById('fieldDataInput');
    var rowCount = document.getElementById('rowCount');
    var previewFrame = document.getElementById('builderPreview');

    if (!canvas || !fieldDataInput) return;

    function sync() {
        fieldDataInput.value = JSON.stringify(rows);
        if (rowCount) rowCount.textContent = rows.length + ' блок(ов)';
        debouncedPreview();
    }

    var previewTimer;
    function debouncedPreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(refreshPreview, 600);
    }

    function refreshPreview() {
        if (!cfg.apiPreview) return;
        fetch(cfg.apiPreview, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ entity: cfg.entity, field_data: rows })
        }).then(function (r) { return r.json(); }).then(function (res) {
            if (!previewFrame) return;
            try {
                var doc = previewFrame.contentDocument;
                var mount = doc.getElementById('fg-preview-mount');
                if (!mount) {
                    mount = doc.createElement('div');
                    mount.id = 'fg-preview-mount';
                    var main = doc.querySelector('.main-content') || doc.body;
                    if (main) main.prepend(mount);
                }
                if (mount) mount.innerHTML = res.html || '';
            } catch (e) { /* cross-origin fallback */ }
        }).catch(function () {});
    }

    document.getElementById('refreshPreview').addEventListener('click', function () {
        if (previewFrame) previewFrame.src = previewFrame.src;
        refreshPreview();
    });

    function newRow(layout) {
        var meta = layouts[layout];
        return {
            id: 'blk_' + Date.now().toString(36),
            layout: layout,
            data: meta ? window.RuEduFieldEditor.layoutDefaults(meta) : {},
            style: {},
            elementStyles: {}
        };
    }

    function renderCanvas() {
        canvas.innerHTML = '';
        if (rows.length === 0) {
            canvas.innerHTML = '<div class="text-muted text-center py-3">Добавьте layout</div>';
        }
        rows.forEach(function (row, idx) {
            var meta = layouts[row.layout] || {};
            var card = document.createElement('div');
            card.className = 'block-card' + (idx === selected ? ' is-selected' : '');
            card.innerHTML =
                '<div class="block-card__header">' +
                '<span class="block-card__handle"><i class="bi bi-grip-vertical"></i></span>' +
                '<p class="block-card__title"><i class="bi ' + (meta.icon || 'bi-square') + '"></i> ' + esc(meta.label || row.layout) + '</p>' +
                '<button type="button" class="btn btn-sm btn-outline-danger row-del"><i class="bi bi-trash"></i></button>' +
                '</div>';
            card.addEventListener('click', function (e) {
                if (e.target.closest('.row-del')) return;
                selectRow(idx);
            });
            card.querySelector('.row-del').addEventListener('click', function (e) {
                e.stopPropagation();
                rows.splice(idx, 1);
                if (selected >= rows.length) selected = rows.length - 1;
                renderCanvas();
                renderPanels();
                sync();
            });
            canvas.appendChild(card);
        });
        initSortable();
        sync();
    }

    function initSortable() {
        if (sortable) sortable.destroy();
        if (typeof Sortable === 'undefined' || rows.length === 0) return;
        sortable = new Sortable(canvas, {
            animation: 150,
            handle: '.block-card__handle',
            draggable: '.block-card',
            onEnd: function (evt) {
                var m = rows.splice(evt.oldIndex, 1)[0];
                rows.splice(evt.newIndex, 0, m);
                selected = evt.newIndex;
                renderCanvas();
                renderPanels();
            }
        });
    }

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function selectRow(idx) {
        selected = idx;
        renderCanvas();
        renderPanels();
    }

    function renderPanels() {
        var row = rows[selected];
        if (!row) {
            fieldPanel.innerHTML = blockStylePanel.innerHTML = elementStylePanel.innerHTML = '<p class="text-muted small">Выберите блок</p>';
            return;
        }
        var meta = layouts[row.layout] || { subfields: [], elements: [] };

        fieldPanel.innerHTML = '';
        fieldPanel.appendChild(window.RuEduFieldEditor.renderLayoutEditor(row.layout, meta, row.data, function () {
            sync();
        }));

        blockStylePanel.innerHTML = '';
        (cfg.styleKeys || []).forEach(function (key) {
            var fg = document.createElement('div');
            fg.className = 'mb-2';
            fg.innerHTML = '<label class="form-label small">' + key + '</label><input type="text" class="form-control form-control-sm" data-style-key="' + key + '" value="' + esc(row.style[key] || '') + '">';
            fg.querySelector('input').addEventListener('input', function (e) {
                row.style[key] = e.target.value;
                sync();
            });
            blockStylePanel.appendChild(fg);
        });

        elementStylePanel.innerHTML = '';
        var elements = meta.elements && meta.elements.length ? meta.elements : ['title', 'subtitle', 'button'];
        var elSel = document.createElement('select');
        elSel.className = 'form-select form-select-sm mb-3';
        elements.forEach(function (el) {
            var o = document.createElement('option');
            o.value = el;
            o.textContent = el;
            elSel.appendChild(o);
        });
        elementStylePanel.appendChild(elSel);

        var elFields = document.createElement('div');
        elementStylePanel.appendChild(elFields);

        function renderElStyles() {
            var el = elSel.value;
            if (!row.elementStyles[el]) row.elementStyles[el] = {};
            elFields.innerHTML = '';
            (cfg.styleKeys || []).slice(0, 8).forEach(function (key) {
                var fg = document.createElement('div');
                fg.className = 'mb-2';
                fg.innerHTML = '<label class="form-label small">' + key + '</label><input type="text" class="form-control form-control-sm" value="' + esc(row.elementStyles[el][key] || '') + '">';
                fg.querySelector('input').addEventListener('input', function (e) {
                    row.elementStyles[el][key] = e.target.value;
                    sync();
                });
                elFields.appendChild(fg);
            });
        }
        elSel.addEventListener('change', renderElStyles);
        renderElStyles();
    }

    document.querySelectorAll('.palette-layout').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var layout = btn.getAttribute('data-layout');
            rows.push(newRow(layout));
            selected = rows.length - 1;
            renderCanvas();
            renderPanels();
        });
    });

    renderCanvas();
    if (rows.length) selectRow(0);
    previewFrame && previewFrame.addEventListener('load', refreshPreview);
})();

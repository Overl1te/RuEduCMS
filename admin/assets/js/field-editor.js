/**
 * Dynamic field editor for flexible content layouts.
 */
window.RuEduFieldEditor = (function () {
    'use strict';

    var mediaCallback = null;

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function defaultsFromFields(fields) {
        var data = {};
        (fields || []).forEach(function (f) {
            var cfg = f.config || {};
            if (f.type === 'repeater') {
                data[f.name] = Array.isArray(cfg.default) ? JSON.parse(JSON.stringify(cfg.default)) : [];
            } else if (f.type === 'group') {
                data[f.name] = defaultsFromFields(f.subfields || []);
            } else {
                data[f.name] = cfg.default != null ? cfg.default : '';
            }
        });
        return data;
    }

    function renderFields(fields, data, path, onChange) {
        var wrap = document.createElement('div');
        (fields || []).forEach(function (field) {
            wrap.appendChild(renderField(field, data, path, onChange));
        });
        return wrap;
    }

    function renderField(field, data, path, onChange) {
        var name = field.name;
        var type = field.type;
        var val = data[name];
        var box = document.createElement('div');
        box.className = 'block-props-field mb-3';

        var label = document.createElement('label');
        label.className = 'form-label small';
        label.textContent = field.label || name;
        box.appendChild(label);

        function set(v) {
            data[name] = v;
            onChange();
        }

        if (type === 'textarea' || type === 'wysiwyg') {
            var ta = document.createElement('textarea');
            ta.className = 'form-control form-control-sm' + (type === 'wysiwyg' ? ' fe-wysiwyg' : '');
            ta.rows = type === 'wysiwyg' ? 6 : 3;
            ta.value = val || '';
            ta.dataset.path = path + '.' + name;
            ta.addEventListener('input', function () { set(ta.value); });
            box.appendChild(ta);
            if (type === 'wysiwyg') {
                setTimeout(function () { initWysiwyg(ta); }, 0);
            }
        } else if (type === 'select') {
            var sel = document.createElement('select');
            sel.className = 'form-select form-select-sm';
            (field.config && field.config.choices ? field.config.choices : []).forEach(function (opt) {
                var o = document.createElement('option');
                o.value = opt;
                o.textContent = opt;
                if (val === opt) o.selected = true;
                sel.appendChild(o);
            });
            sel.addEventListener('change', function () { set(sel.value); });
            box.appendChild(sel);
        } else if (type === 'checkbox') {
            var chk = document.createElement('input');
            chk.type = 'checkbox';
            chk.className = 'form-check-input';
            chk.checked = !!val;
            chk.addEventListener('change', function () { set(chk.checked); });
            box.appendChild(chk);
        } else if (type === 'number') {
            var num = document.createElement('input');
            num.type = 'number';
            num.className = 'form-control form-control-sm';
            num.value = val != null ? val : '';
            num.addEventListener('input', function () { set(parseInt(num.value, 10) || 0); });
            box.appendChild(num);
        } else if (type === 'color') {
            var col = document.createElement('input');
            col.type = 'color';
            col.className = 'form-control form-control-color';
            col.value = /^#/.test(val) ? val : '#000000';
            col.addEventListener('input', function () { set(col.value); });
            box.appendChild(col);
        } else if (type === 'image') {
            box.appendChild(renderImageField(val, function (v) { set(v); }));
        } else if (type === 'link') {
            box.appendChild(renderLinkField(val, function (v) { set(v); }));
        } else if (type === 'repeater') {
            box.appendChild(renderRepeater(field, val || [], path + '.' + name, function (rows) { set(rows); onChange(); }));
        } else if (type === 'group') {
            if (!data[name] || typeof data[name] !== 'object') data[name] = defaultsFromFields(field.subfields || []);
            box.appendChild(renderFields(field.subfields || [], data[name], path + '.' + name, onChange));
        } else {
            var inp = document.createElement('input');
            inp.type = 'text';
            inp.className = 'form-control form-control-sm';
            inp.value = val != null ? val : '';
            inp.addEventListener('input', function () { set(inp.value); });
            box.appendChild(inp);
        }

        return box;
    }

    function renderImageField(val, onSet) {
        var wrap = document.createElement('div');
        var url = typeof val === 'object' && val ? (val.url || '') : (val || '');
        var preview = document.createElement('div');
        preview.className = 'small text-muted mb-1';
        preview.textContent = url || 'Не выбрано';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-secondary';
        btn.textContent = 'Выбрать изображение';
        btn.addEventListener('click', function () {
            openMediaPicker(function (item) {
                var v = { id: item.id, url: item.url, path: item.path };
                preview.textContent = item.url;
                onSet(v);
            });
        });
        wrap.appendChild(preview);
        wrap.appendChild(btn);
        return wrap;
    }

    function renderLinkField(val, onSet) {
        var wrap = document.createElement('div');
        var v = typeof val === 'object' && val ? val : { url: val || '', label: '' };
        var inp = document.createElement('input');
        inp.type = 'text';
        inp.className = 'form-control form-control-sm mb-1';
        inp.placeholder = 'URL';
        inp.value = v.url || '';
        inp.addEventListener('input', function () { v.url = inp.value; onSet(v); });
        var sel = document.createElement('select');
        sel.className = 'form-select form-select-sm';
        sel.innerHTML = '<option value="">— страница сайта —</option>';
        (window.pageBuilderConfig && window.pageBuilderConfig.pages || []).forEach(function (p) {
            var o = document.createElement('option');
            o.value = '/page/' + p.slug;
            o.textContent = p.title;
            sel.appendChild(o);
        });
        sel.addEventListener('change', function () {
            if (sel.value) { inp.value = sel.value; v.url = sel.value; onSet(v); }
        });
        wrap.appendChild(inp);
        wrap.appendChild(sel);
        return wrap;
    }

    function renderRepeater(field, rows, path, onSet) {
        var wrap = document.createElement('div');
        rows = Array.isArray(rows) ? rows : [];

        function render() {
            wrap.innerHTML = '';
            rows.forEach(function (row, idx) {
                var card = document.createElement('div');
                card.className = 'border rounded p-2 mb-2';
                var head = document.createElement('div');
                head.className = 'd-flex justify-content-between mb-2';
                head.innerHTML = '<span class="small text-muted">#' + (idx + 1) + '</span>';
                var del = document.createElement('button');
                del.type = 'button';
                del.className = 'btn btn-sm btn-outline-danger';
                del.innerHTML = '<i class="bi bi-trash"></i>';
                del.addEventListener('click', function () {
                    rows.splice(idx, 1);
                    onSet(rows);
                    render();
                });
                head.appendChild(del);
                card.appendChild(head);
                card.appendChild(renderFields(field.subfields || [], row, path + '[' + idx + ']', function () {
                    onSet(rows);
                }));
                wrap.appendChild(card);
            });
            var add = document.createElement('button');
            add.type = 'button';
            add.className = 'btn btn-sm btn-outline-primary';
            add.textContent = '+ Добавить';
            add.addEventListener('click', function () {
                rows.push(defaultsFromFields(field.subfields || []));
                onSet(rows);
                render();
            });
            wrap.appendChild(add);
        }
        render();
        return wrap;
    }

    function initWysiwyg(ta) {
        if (!window.tinymce || ta.dataset.mceInit) return;
        ta.dataset.mceInit = '1';
        tinymce.init({
            target: ta,
            height: 200,
            menubar: false,
            plugins: 'link lists',
            toolbar: 'bold italic | bullist numlist | link',
            setup: function (ed) {
                ed.on('change keyup', function () { ta.value = ed.getContent(); ta.dispatchEvent(new Event('input')); });
            }
        });
    }

    function openMediaPicker(cb) {
        mediaCallback = cb;
        var grid = document.getElementById('mediaPickerGrid');
        if (!grid) return;
        grid.innerHTML = '<p class="text-muted">Загрузка...</p>';
        var modal = document.getElementById('mediaPickerModal');
        if (modal && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
        fetch(window.pageBuilderConfig.apiMedia).then(function (r) { return r.json(); }).then(function (items) {
            grid.innerHTML = '';
            items.forEach(function (item) {
                var url = item.url || ('/content/uploads/' + item.path);
                var col = document.createElement('div');
                col.className = 'col-4';
                col.innerHTML = '<button type="button" class="btn btn-light w-100 p-1 border"><img src="' + esc(url) + '" alt="" style="max-width:100%;height:80px;object-fit:cover"></button>';
                col.querySelector('button').addEventListener('click', function () {
                    item.url = url;
                    if (mediaCallback) mediaCallback(item);
                    if (modal && window.bootstrap) bootstrap.Modal.getInstance(modal).hide();
                });
                grid.appendChild(col);
            });
        });
    }

    function renderLayoutEditor(layoutName, layoutMeta, data, onChange) {
        var fields = layoutMeta.subfields || [];
        return renderFields(fields, data, layoutName, onChange);
    }

    function layoutDefaults(layoutMeta) {
        return defaultsFromFields(layoutMeta.subfields || []);
    }

    return {
        renderLayoutEditor: renderLayoutEditor,
        layoutDefaults: layoutDefaults,
        defaultsFromFields: defaultsFromFields
    };
})();

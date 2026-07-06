(function () {
    'use strict';

    var cfg = window.fgBuilderConfig || { fields: [], locations: [], fieldTypes: {}, locationParams: [] };
    var fields = JSON.parse(JSON.stringify(cfg.fields || []));
    var locations = JSON.parse(JSON.stringify(cfg.locations || []));

    var fieldsTree = document.getElementById('fieldsTree');
    var fieldsJson = document.getElementById('fieldsJson');
    var locationRules = document.getElementById('locationRules');
    var fgForm = document.getElementById('fgForm');

    if (!fieldsTree || !fieldsJson) return;

    function syncFields() {
        fieldsJson.value = JSON.stringify(fields);
    }

    function syncLocationsInput() {
        var input = document.getElementById('locationsJson');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'locations_json';
            input.id = 'locationsJson';
            fgForm.appendChild(input);
        }
        input.value = JSON.stringify(locations);
    }

    function renderLocations() {
        locationRules.innerHTML = '';
        locations.forEach(function (rule, idx) {
            var row = document.createElement('div');
            row.className = 'location-rule';
            row.innerHTML =
                '<select class="form-select form-select-sm loc-param" data-idx="' + idx + '">' +
                    cfg.locationParams.map(function (p) {
                        return '<option value="' + p + '"' + (rule.param === p ? ' selected' : '') + '>' + p + '</option>';
                    }).join('') +
                '</select>' +
                '<select class="form-select form-select-sm loc-op" data-idx="' + idx + '">' +
                    '<option value="=="' + (rule.operator === '==' ? ' selected' : '') + '>==</option>' +
                    '<option value="!="' + (rule.operator === '!=' ? ' selected' : '') + '>!=</option>' +
                '</select>' +
                '<input type="text" class="form-control form-control-sm loc-val" data-idx="' + idx + '" value="' + escapeAttr(rule.value || '') + '">' +
                '<button type="button" class="btn btn-sm btn-outline-danger loc-del" data-idx="' + idx + '"><i class="bi bi-trash"></i></button>';

            row.querySelector('.loc-param').addEventListener('change', function (e) {
                locations[idx].param = e.target.value;
                syncLocationsInput();
            });
            row.querySelector('.loc-op').addEventListener('change', function (e) {
                locations[idx].operator = e.target.value;
                syncLocationsInput();
            });
            row.querySelector('.loc-val').addEventListener('input', function (e) {
                locations[idx].value = e.target.value;
                syncLocationsInput();
            });
            row.querySelector('.loc-del').addEventListener('click', function () {
                locations.splice(idx, 1);
                renderLocations();
                syncLocationsInput();
            });
            locationRules.appendChild(row);
        });
        syncLocationsInput();
    }

    function escapeAttr(s) {
        return String(s).replace(/"/g, '&quot;');
    }

    function newField(type) {
        return {
            name: 'field_' + Date.now().toString(36),
            label: 'Поле',
            type: type || 'text',
            config: {},
            subfields: []
        };
    }

    function renderFieldNode(field, container, path) {
        var node = document.createElement('div');
        node.className = 'fg-field-node';
        node.dataset.path = path.join('.');

        var types = Object.keys(cfg.fieldTypes);
        var head = document.createElement('div');
        head.className = 'fg-field-node__head';
        head.innerHTML =
            '<span class="fg-field-handle"><i class="bi bi-grip-vertical"></i></span>' +
            '<input type="text" class="form-control form-control-sm fg-name" placeholder="name" value="' + escapeAttr(field.name || '') + '">' +
            '<input type="text" class="form-control form-control-sm fg-label" placeholder="label" value="' + escapeAttr(field.label || '') + '">' +
            '<select class="form-select form-select-sm fg-type">' +
                types.map(function (t) {
                    return '<option value="' + t + '"' + (field.type === t ? ' selected' : '') + '>' + (cfg.fieldTypes[t].label || t) + '</option>';
                }).join('') +
            '</select>' +
            '<button type="button" class="btn btn-sm btn-outline-primary fg-add-child" title="Подполе"><i class="bi bi-plus"></i></button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger fg-del"><i class="bi bi-trash"></i></button>';

        head.querySelector('.fg-name').addEventListener('input', function (e) { field.name = e.target.value; syncFields(); });
        head.querySelector('.fg-label').addEventListener('input', function (e) { field.label = e.target.value; syncFields(); });
        head.querySelector('.fg-type').addEventListener('change', function (e) {
            field.type = e.target.value;
            if (!field.subfields) field.subfields = [];
            syncFields();
            renderTree();
        });
        head.querySelector('.fg-del').addEventListener('click', function () {
            removeAtPath(path);
            renderTree();
        });
        head.querySelector('.fg-add-child').addEventListener('click', function () {
            if (!field.subfields) field.subfields = [];
            field.subfields.push(newField('text'));
            syncFields();
            renderTree();
        });

        node.appendChild(head);

        if (field.subfields && field.subfields.length) {
            var children = document.createElement('div');
            children.className = 'fg-field-node__children';
            field.subfields.forEach(function (child, i) {
                renderFieldNode(child, children, path.concat([i]));
            });
            node.appendChild(children);
        }

        container.appendChild(node);
    }

    function getAtPath(path) {
        var cur = fields;
        for (var i = 0; i < path.length; i++) {
            cur = cur[path[i]].subfields;
        }
        return cur;
    }

    function removeAtPath(path) {
        if (path.length === 1) {
            fields.splice(path[0], 1);
        } else {
            var parent = fields;
            for (var i = 0; i < path.length - 1; i++) {
                parent = parent[path[i]].subfields;
            }
            parent.splice(path[path.length - 1], 1);
        }
        syncFields();
    }

    function renderTree() {
        fieldsTree.innerHTML = '';
        fields.forEach(function (field, i) {
            renderFieldNode(field, fieldsTree, [i]);
        });
        syncFields();
    }

    document.getElementById('addLocationRule').addEventListener('click', function () {
        locations.push({ param: cfg.locationParams[0] || 'page_type', operator: '==', value: '' });
        renderLocations();
    });

    document.getElementById('addFlexibleField').addEventListener('click', function () {
        fields.push({ name: 'content', label: 'Содержимое', type: 'flexible', config: {}, subfields: [] });
        renderTree();
    });

    document.getElementById('addFieldBtn').addEventListener('click', function () {
        fields.push(newField('text'));
        renderTree();
    });

    renderLocations();
    renderTree();
})();

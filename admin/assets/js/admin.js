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

    function phoneDigits(value) {
        var digits = String(value || '').replace(/\D/g, '');
        if (!digits) {
            return '';
        }
        if (digits.charAt(0) === '8') {
            digits = '7' + digits.slice(1);
        } else if (digits.charAt(0) !== '7') {
            digits = '7' + digits;
        }
        return digits.slice(0, 11);
    }

    function formatPhone(value) {
        var digits = phoneDigits(value);
        if (!digits) {
            return '';
        }
        if (digits.length === 1) {
            return '+7 (';
        }

        var local = digits.slice(1);
        var formatted = '+7 (' + local.slice(0, 3);
        if (local.length < 3) {
            return formatted;
        }

        formatted += ')';
        if (local.length > 3) {
            formatted += ' ' + local.slice(3, 6);
        }
        if (local.length > 6) {
            formatted += '-' + local.slice(6, 8);
        }
        if (local.length > 8) {
            formatted += '-' + local.slice(8, 10);
        }
        return formatted;
    }

    function isPhonePrefix(value) {
        return value === '+7' || value === '+7 (' || value === '+7 (';
    }

    function initPhoneMask(input) {
        if (input.dataset.phoneMaskInit) {
            return;
        }
        input.dataset.phoneMaskInit = '1';
        input.setAttribute('inputmode', 'tel');
        input.setAttribute('autocomplete', 'tel');
        if (!input.placeholder) {
            input.placeholder = '+7 (___) ___-__-__';
        }

        function applyValue(value) {
            input.value = formatPhone(value);
            var end = input.value.length;
            input.setSelectionRange(end, end);
        }

        input.addEventListener('input', function () {
            applyValue(input.value);
        });
        input.addEventListener('paste', function (event) {
            event.preventDefault();
            var text = (event.clipboardData || window.clipboardData).getData('text');
            applyValue(text);
        });
        input.addEventListener('focus', function () {
            if (!input.value) {
                input.value = '+7 (';
                input.setSelectionRange(input.value.length, input.value.length);
            }
        });
        input.addEventListener('blur', function () {
            if (isPhonePrefix(input.value)) {
                input.value = '';
            }
        });
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Backspace' && isPhonePrefix(input.value)) {
                event.preventDefault();
                input.value = '';
            }
        });

        if (input.value) {
            input.value = formatPhone(input.value);
        }
    }

    function initTimeRangeMask(input) {
        if (input.dataset.timeMaskInit) {
            return;
        }
        input.dataset.timeMaskInit = '1';
        input.setAttribute('inputmode', 'numeric');
        if (!input.placeholder) {
            input.placeholder = '08:30–09:15';
        }

        function digits(value) {
            return String(value || '').replace(/\D/g, '').slice(0, 8);
        }

        function format(value) {
            var d = digits(value);
            if (!d) {
                return '';
            }

            var result = d.slice(0, 2);
            if (d.length >= 2) {
                result += ':';
            }
            if (d.length > 2) {
                result += d.slice(2, 4);
            }
            if (d.length >= 4) {
                result += '–';
            }
            if (d.length > 4) {
                result += d.slice(4, 6);
            }
            if (d.length >= 6) {
                result += ':';
            }
            if (d.length > 6) {
                result += d.slice(6, 8);
            }
            return result;
        }

        function applyValue(value) {
            input.value = format(value);
            var end = input.value.length;
            input.setSelectionRange(end, end);
        }

        input.addEventListener('input', function () {
            applyValue(input.value);
        });
        input.addEventListener('paste', function (event) {
            event.preventDefault();
            var text = (event.clipboardData || window.clipboardData).getData('text');
            applyValue(text);
        });

        if (input.value) {
            input.value = format(input.value);
        }
    }

    function initScheduleLessonFields() {
        document.querySelectorAll('.schedule-lesson-field').forEach(function (input) {
            if (input.dataset.scheduleFieldInit) {
                return;
            }
            input.dataset.scheduleFieldInit = '1';
            input.addEventListener('change', function () {
                var formId = input.getAttribute('form');
                if (!formId) {
                    return;
                }
                var form = document.getElementById(formId);
                if (form) {
                    form.requestSubmit();
                }
            });
        });
    }

    function renumberScheduleDay(tbody) {
        var num = 1;
        tbody.querySelectorAll('.schedule-lesson-row').forEach(function (row) {
            var numCell = row.querySelector('.schedule-lesson-num');
            if (numCell) {
                numCell.textContent = String(num);
            }
            var lessonId = row.getAttribute('data-lesson-id');
            var form = lessonId ? document.getElementById('lesson-form-' + lessonId) : null;
            if (form) {
                var input = form.querySelector('input[name="lesson_number"]');
                if (input) {
                    input.value = String(num);
                }
            }
            num += 1;
        });

        var addBtn = tbody.querySelector('.schedule-add-lesson');
        if (addBtn) {
            addBtn.setAttribute('data-lesson', String(num));
        }
    }

    function saveScheduleOrder(tbody) {
        var grid = tbody.closest('.schedule-days-grid');
        if (!grid) {
            return;
        }

        var reorderUrl = grid.getAttribute('data-schedule-reorder-url');
        var className = grid.getAttribute('data-schedule-class');
        var csrf = grid.getAttribute('data-schedule-csrf');
        var day = tbody.getAttribute('data-schedule-day');
        if (!reorderUrl || !className || !csrf || !day) {
            return;
        }

        var order = [];
        tbody.querySelectorAll('.schedule-lesson-row').forEach(function (row) {
            var id = parseInt(row.getAttribute('data-lesson-id') || '', 10);
            if (id > 0) {
                order.push(id);
            }
        });
        if (!order.length) {
            return;
        }

        fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                _csrf: csrf,
                class_name: className,
                day_of_week: day,
                order: JSON.stringify(order)
            })
        }).catch(function () {
            // ignore network errors; order is already updated in the UI
        });
    }

    function initScheduleLessonDrag() {
        document.querySelectorAll('.schedule-day-table tbody[data-schedule-day]').forEach(function (tbody) {
            if (tbody.dataset.scheduleDragInit) {
                return;
            }
            tbody.dataset.scheduleDragInit = '1';

            var draggedRow = null;

            tbody.querySelectorAll('.schedule-drag-handle').forEach(function (handle) {
                handle.addEventListener('dragstart', function (event) {
                    draggedRow = handle.closest('.schedule-lesson-row');
                    if (!draggedRow) {
                        return;
                    }
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', draggedRow.getAttribute('data-lesson-id') || '');
                    draggedRow.classList.add('schedule-lesson-row--dragging');
                });
            });

            tbody.addEventListener('dragend', function () {
                tbody.querySelectorAll('.schedule-lesson-row--dragging, .schedule-lesson-row--over').forEach(function (row) {
                    row.classList.remove('schedule-lesson-row--dragging', 'schedule-lesson-row--over');
                });
                if (draggedRow) {
                    renumberScheduleDay(tbody);
                    saveScheduleOrder(tbody);
                    draggedRow = null;
                }
            });

            tbody.addEventListener('dragover', function (event) {
                if (!draggedRow) {
                    return;
                }
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';

                var row = event.target.closest('.schedule-lesson-row');
                tbody.querySelectorAll('.schedule-lesson-row--over').forEach(function (item) {
                    if (item !== row) {
                        item.classList.remove('schedule-lesson-row--over');
                    }
                });
                if (!row || row === draggedRow) {
                    return;
                }

                row.classList.add('schedule-lesson-row--over');
                var rect = row.getBoundingClientRect();
                var after = event.clientY > rect.top + rect.height / 2;
                if (after) {
                    row.parentNode.insertBefore(draggedRow, row.nextSibling);
                } else {
                    row.parentNode.insertBefore(draggedRow, row);
                }
            });

            tbody.addEventListener('drop', function (event) {
                event.preventDefault();
            });
        });
    }

    function initAdminEnhancements(root) {
        var scope = root || document;
        scope.querySelectorAll('[data-autocomplete]').forEach(initAutocomplete);
        scope.querySelectorAll('[data-phone-mask], input[name="phone"], input[name="contact_phone"]').forEach(initPhoneMask);
        scope.querySelectorAll('[data-time-mask]').forEach(initTimeRangeMask);
        initScheduleLessonFields();
        initScheduleLessonDrag();
    }

    document.addEventListener('DOMContentLoaded', function () {
        initAdminEnhancements();
    });
})();

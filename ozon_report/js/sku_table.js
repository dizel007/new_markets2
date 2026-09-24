/**
 * Логика таблицы юнит-экономики:
 * 1) Клиентская сортировка таблицы по столбцам с классом .sortable.
 * 2) Поиск по столбцу «Наименование» (название + SKU + артикул).
 * 3) Пересчёт строки ИТОГО (включая количества) после фильтрации.
 * 4) Экспорт всей таблицы в Excel (xlsx) на клиенте — БЕЗ запросов к серверу.
 *
 * Требует: таблица с id="ancor_table", tbody id="filterable-table-body",
 *           input id="nameSearch", кнопка id="exportExcelBtn", span id="exportHint".
 *           Библиотека SheetJS (XLSX) должна быть подключена до этого скрипта.
 */
(function () {
    'use strict';

    var table = document.getElementById('ancor_table');
    if (!table) return;

    var tbody = document.getElementById('filterable-table-body');
    if (!tbody) return;

    var headers = table.querySelectorAll('th.sortable');
    var searchInput = document.getElementById('nameSearch');

    /* ---------- Форматирование денег (совпадает с PHP moneyNum) ---------- */
    function formatMoney(num) {
        var rounded = Math.round(num * 100) / 100;
        var isInt = Math.abs(rounded - Math.round(rounded)) < 0.001;
        var s = isInt ? String(Math.round(rounded)) : rounded.toFixed(2);

        var parts = s.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        return parts.join('.');
    }

    function moneySpan(num) {
        var formatted = formatMoney(num);
        var cls = num > 0 ? 'positive' : (num < 0 ? 'negative' : 'zero');
        return '<span class="' + cls + '">' + formatted + '</span>';
    }

    function countSpan(num) {
        var rounded = Math.round(num);
        var s = String(rounded).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        var cls = rounded > 0 ? 'positive' : (rounded < 0 ? 'negative' : 'zero');
        return '<span class="' + cls + '">' + s + '</span>';
    }

    /* ---------- Пересчёт ИТОГО по видимым строкам ---------- */
    function recalcTotals() {
        var totalCells = tbody.querySelectorAll('.total-row [data-total-key]');
        if (!totalCells.length) return;

        var totals = {};
        totalCells.forEach(function (cell) {
            totals[cell.dataset.totalKey] = 0;
        });

        var rows = tbody.querySelectorAll('tr:not(.total-row)');
        rows.forEach(function (row) {
            if (row.style.display === 'none') return;
            totalCells.forEach(function (cell) {
                var key = cell.dataset.totalKey;
                var v = parseFloat(row.getAttribute('data-' + key));
                if (!isNaN(v)) totals[key] += v;
            });
        });

        totalCells.forEach(function (cell) {
            var key  = cell.dataset.totalKey;
            var type = cell.dataset.totalType || 'money';

            var anyFound = false;
            tbody.querySelectorAll('tr:not(.total-row)').forEach(function (r) {
                if (r.style.display === 'none') return;
                if (r.hasAttribute('data-' + key)) anyFound = true;
            });
            if (!anyFound) return;

            var html = (type === 'count') ? countSpan(totals[key]) : moneySpan(totals[key]);
            cell.innerHTML = '<b>' + html + '</b>';
        });
    }

    /* ---------- Сортировка ---------- */
    headers.forEach(function (th) {
        th.addEventListener('click', function () {
            var key      = th.dataset.sort;
            var attrName = 'data-' + key;
            var current  = th.dataset.order || 'none';
            var next     = (current === 'desc') ? 'asc' : 'desc';

            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'))
                            .filter(function (r) { return !r.classList.contains('total-row'); });

            rows.sort(function (a, b) {
                var av = parseFloat(a.getAttribute(attrName));
                var bv = parseFloat(b.getAttribute(attrName));
                if (isNaN(av)) av = 0;
                if (isNaN(bv)) bv = 0;
                return (next === 'asc') ? (av - bv) : (bv - av);
            });

            var totalRow = tbody.querySelector('tr.total-row');
            var frag = document.createDocumentFragment();
            rows.forEach(function (r) { frag.appendChild(r); });
            if (totalRow) frag.appendChild(totalRow);
            tbody.appendChild(frag);

            headers.forEach(function (other) {
                var icon = other.querySelector('.sort-icon');
                if (other === th) {
                    other.dataset.order = next;
                    if (icon) icon.textContent = (next === 'asc') ? '▲' : '▼';
                } else {
                    other.dataset.order = 'none';
                    if (icon) icon.textContent = '↕';
                }
            });

            recalcTotals();
        });
    });

    /* ---------- Поиск по названию / SKU / артикулу ---------- */
    if (searchInput) {
        searchInput.addEventListener('click', function (e) { e.stopPropagation(); });

        searchInput.addEventListener('input', function () {
            var q = this.value.toLowerCase().trim();
            var rows = tbody.querySelectorAll('tr');

            rows.forEach(function (r) {
                if (r.classList.contains('total-row')) return;

                if (q === '') {
                    r.style.display = '';
                } else {
                    var hay = r.getAttribute('data-search') || '';
                    r.style.display = (hay.indexOf(q) !== -1) ? '' : 'none';
                }
            });

            recalcTotals();
        });
    }

    /* =========================================================================
     * ЭКСПОРТ В EXCEL (клиентский, SheetJS)
     * Данные берём прямо из таблицы. К серверу НЕ обращаемся.
     * ========================================================================= */
    var exportBtn  = document.getElementById('exportExcelBtn');
    var exportHint = document.getElementById('exportHint');

    // Заголовки листа (жёстко, чтобы не тащить иконки сортировки)
    var XLS_HEADERS = [
        'Наименование',
        'Артикул',
        'SKU',
        'К-во Заказ (шт)',
        'К-во Возвр (шт)',
        'К-во проданных (шт)',
        'Стоимость товара в ЛК (руб)',
        'Комиссия озон (руб)',
        'Стоимость логистики (руб)',
        'Стоимость сервисов (руб)',
        'Эквайринг (руб)',
        'Сумма продаж без комис и логис (руб)',
        'доп.услуги (руб)',
        'Выплаты (руб)',
        'Ино_продажи (руб)',
        'Себестоимость (руб)',
        'Прибыль (руб)'
    ];

    // Соответствие: data-атрибут строки  →  столбец в Excel
    var XLS_KEYS = [
        'count-direct', 'count-return', 'count-buy',
        'seller-price', 'commission', 'logistika', 'service', 'equairing',
        'price-without', 'no-sku', 'viplata', 'ino_prodazhi',
        'sebestoimost', 'profit'
    ];

    function buildExcelRows() {
        var rows = [XLS_HEADERS.slice()];

        // Экспортируем ВСЕ строки таблицы (не только видимые) — «полная таблица»
        tbody.querySelectorAll('tr:not(.total-row)').forEach(function (tr) {
            var nameEl = tr.querySelector('.tovar_name');
            var name   = nameEl ? (nameEl.getAttribute('title') || nameEl.textContent).trim() : '';

            var artTd  = tr.children[1];
            var links  = artTd ? artTd.querySelectorAll('a') : [];
            var article = links[0] ? links[0].textContent.trim() : '';
            var sku     = links[1] ? links[1].textContent.trim() : '';

            var row = [name, article, sku];

            XLS_KEYS.forEach(function (k) {
                var v = parseFloat(tr.getAttribute('data-' + k));
                row.push(isNaN(v) ? 0 : v);
            });

            rows.push(row);
        });

        // Итоговая строка
        var totals = XLS_KEYS.map(function (k) {
            var sum = 0;
            tbody.querySelectorAll('tr:not(.total-row)').forEach(function (tr) {
                var v = parseFloat(tr.getAttribute('data-' + k));
                if (!isNaN(v)) sum += v;
            });
            return Math.round(sum * 100) / 100;
        });
        rows.push(['ИТОГО', '', ''].concat(totals));

        return rows;
    }

    function applySheetStyles(ws, rowsCount, colsCount) {
        // Ширины колонок
        ws['!cols'] = [
            { wch: 50 }, // Наименование
            { wch: 16 }, // Артикул
            { wch: 16 }, // SKU
            { wch: 12 }, { wch: 12 }, { wch: 12 }, // штуки
            { wch: 16 }, { wch: 16 }, { wch: 16 }, { wch: 16 }, { wch: 14 }, // деньги
            { wch: 20 }, { wch: 14 }, { wch: 16 }, { wch: 16 }, // доп.услуги, выплаты, ино
            { wch: 16 }, { wch: 16 } // себест, прибыль
        ];

        // Форматы чисел: столбцы D..F — целые, G..Q — деньги
        var moneyCols = ['G','H','I','J','K','L','M','N','O','P','Q'];
        var countCols = ['D','E','F'];

        for (var R = 2; R <= rowsCount; R++) {
            countCols.forEach(function (c) {
                var addr = c + R;
                if (ws[addr]) ws[addr].z = '#,##0';
            });
            moneyCols.forEach(function (c) {
                var addr = c + R;
                if (ws[addr]) ws[addr].z = '#,##0.00';
            });
        }

        // Жирная шапка (визуальный стиль ограничен — SheetJS CE не хранит стили,
        // но начальные строки всё равно будут читаемыми)
        ws['!freeze'] = { xSplit: 0, ySplit: 1 };
        ws['!autofilter'] = { ref: 'A1:Q' + (rowsCount - 1) };
    }

    function safeFileName() {
        // Ищем shop_name в URL
        var params = new URLSearchParams(window.location.search);
        var shop = params.get('ozon_shop') || 'export';
        var d1 = params.get('dateFrom') || '';
        var d2 = params.get('dateTo') || '';
        var stamp = new Date().toISOString().slice(0, 10);
        return 'unit_economika_' + shop + '_' + d1 + '_' + d2 + '_' + stamp + '.xlsx';
    }

    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            if (typeof XLSX === 'undefined') {
                if (exportHint) exportHint.textContent = 'Не удалось загрузить библиотеку XLSX. Проверьте интернет.';
                return;
            }

            if (exportHint) exportHint.textContent = 'Готовим файл...';
            exportBtn.disabled = true;

            // Небольшая задержка, чтобы UI успел обновиться
            setTimeout(function () {
                try {
                    var rows = buildExcelRows();

                    if (rows.length <= 1) {
                        if (exportHint) exportHint.textContent = 'Нет данных для экспорта.';
                        exportBtn.disabled = false;
                        return;
                    }

                    var ws = XLSX.utils.aoa_to_sheet(rows);
                    applySheetStyles(ws, rows.length, XLS_HEADERS.length);

                    var wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Юнит-экономика');

                    XLSX.writeFile(wb, safeFileName());

                    if (exportHint) exportHint.textContent = 'Файл сохранён ✓';
                } catch (e) {
                    console.error(e);
                    if (exportHint) exportHint.textContent = 'Ошибка экспорта: ' + e.message;
                } finally {
                    exportBtn.disabled = false;
                    setTimeout(function () {
                        if (exportHint) exportHint.textContent = '';
                    }, 4000);
                }
            }, 20);
        });
    }
})();
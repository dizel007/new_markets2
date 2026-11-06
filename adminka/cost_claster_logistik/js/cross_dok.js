/**********************************************************************************************************************
 * Файл js/cross_dok.js
 *
 * Логика страницы расчёта логистики и кросс-докинга.
 *********************************************************************************************************************/

(function () {
    'use strict';

    const products = window.__CROSS_DOK_PRODUCTS__ || {};
    const MIN_PALLET_VOLUME = 1900;

    if (!document.getElementById('calcTable')) return;

    const tbody = document.querySelector('#calcTable tbody');
    const crossTbody = document.querySelector('#crossTable tbody');
    const localLogTbody = document.querySelector('#localLogTable tbody');

    const addBtn = document.getElementById('addRowBtn');

    const grandTotalEl = document.getElementById('grandTotal');
    const totalVolumeEl = document.getElementById('totalVolume');

    const crossPalletTotalEl = document.getElementById('crossPalletTotal');
    const crossBoxTotalEl = document.getElementById('crossBoxTotal');

    const localLogTotalEl = document.getElementById('localLogTotal');
    const localLogVolumeEl = document.getElementById('localLogVolume');

    // Итоговая сводка — 1-я строка
    const summaryDiffEl = document.getElementById('summaryDiff');
    const summaryPalletSumEl = document.getElementById('summaryPalletSum');
    const summaryBoxSumEl = document.getElementById('summaryBoxSum');

    // Итоговая сводка — 2-я строка (разницы)
    const summaryPalletDeltaEl = document.getElementById('summaryPalletDelta');
    const summaryBoxDeltaEl = document.getElementById('summaryBoxDelta');

    const clusterFromSelect = document.getElementById('ClusterFrom');
    const clusterToSelect = document.getElementById('ClusterTo');

    const productArray = Object.keys(products).map(function (id) {
        return { id: id, vol: products[id].value, weight: products[id].weight };
    });

    // ---------- Утилиты ----------
    function formatMoney(num) {
        if (isNaN(num) || num === null) return '0,00';
        return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
    }

    function formatVolume(num) {
        if (isNaN(num) || num === null) return '0,00';
        return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
    }

    function parseMoney(str) {
        if (!str) return 0;
        const clean = String(str).replace(/\s/g, '').replace('₽', '').replace(',', '.');
        const val = parseFloat(clean);
        return isNaN(val) ? 0 : val;
    }

    function renderPalletCell(cell, realSum, actualSum, isBelowMin) {
        if (isBelowMin) {
            cell.innerHTML =
                '<span class="cross-real-note">Реальный: ' + formatMoney(realSum) + ' ₽</span>' +
                '<span class="cross-actual">' + formatMoney(actualSum) + ' ₽</span>';
        } else {
            cell.innerHTML = '<span class="cross-actual">' + formatMoney(actualSum) + ' ₽</span>';
        }
        cell.dataset.actual = actualSum;
    }

    // ---------- Добавление строки ----------
    function addRow() {
        const tr = document.createElement('tr');
        tr.innerHTML =
            '<td>' +
                '<div class="autocomplete-wrapper">' +
                    '<input type="text" class="autocomplete-input" placeholder="Артикул..." autocomplete="off">' +
                    '<ul class="autocomplete-list"></ul>' +
                '</div>' +
            '</td>' +
            '<td class="vol-display">—</td>' +
            '<td class="weight-display">—</td>' +
            '<td><input type="number" class="qty-input" value="1" min="1"></td>' +
            '<td class="total-vol-display">—</td>' +
            '<td class="unit-price">0,00</td>' +
            '<td class="total-price">0,00</td>' +
            '<td><button class="remove-btn" type="button">✕</button></td>';
        tbody.appendChild(tr);

        const crossTr = document.createElement('tr');
        crossTr.innerHTML =
            '<td class="cross-offer">—</td>' +
            '<td class="cross-volume">—</td>' +
            '<td class="cross-pallet-price" data-actual="0">0,00</td>' +
            '<td class="cross-box-price">0,00</td>';
        crossTbody.appendChild(crossTr);

        const localTr = document.createElement('tr');
        localTr.innerHTML =
            '<td class="local-offer">—</td>' +
            '<td class="local-vol">—</td>' +
            '<td class="local-weight">—</td>' +
            '<td class="local-qty">—</td>' +
            '<td class="local-total-vol">—</td>' +
            '<td class="local-unit-price">0,00</td>' +
            '<td class="local-total-price">0,00</td>';
        localLogTbody.appendChild(localTr);

        attachEvents(tr, crossTr, localTr);
    }

    // ---------- События строки ----------
    function attachEvents(tr, crossTr, localTr) {
        const inputWrapper = tr.querySelector('.autocomplete-wrapper');
        const input = tr.querySelector('.autocomplete-input');
        const list = tr.querySelector('.autocomplete-list');
        const qtyInput = tr.querySelector('.qty-input');
        const removeBtn = tr.querySelector('.remove-btn');

        let currentFocus = -1;

        function resetCrossRow(crossRow) {
            crossRow.querySelector('.cross-offer').textContent = '—';
            crossRow.querySelector('.cross-volume').textContent = '—';
            const pc = crossRow.querySelector('.cross-pallet-price');
            pc.innerHTML = '0,00';
            pc.dataset.actual = '0';
            crossRow.querySelector('.cross-box-price').textContent = '0,00';
        }

        function resetLocalRow(localRow) {
            localRow.querySelector('.local-offer').textContent = '—';
            localRow.querySelector('.local-vol').textContent = '—';
            localRow.querySelector('.local-weight').textContent = '—';
            localRow.querySelector('.local-qty').textContent = '—';
            localRow.querySelector('.local-total-vol').textContent = '—';
            localRow.querySelector('.local-unit-price').textContent = '0,00';
            localRow.querySelector('.local-total-price').textContent = '0,00';
        }

        function renderList(searchTerm) {
            list.innerHTML = '';
            currentFocus = -1;

            const term = searchTerm.toLowerCase().trim();
            if (!term) { list.classList.remove('show'); return; }

            const filtered = productArray.filter(function (p) {
                return p.id.toLowerCase().indexOf(term) !== -1;
            }).slice(0, 50);

            if (filtered.length === 0) { list.classList.remove('show'); return; }

            filtered.forEach(function (p) {
                const li = document.createElement('li');
                li.className = 'autocomplete-item';
                li.innerHTML =
                    '<span class="offer-name">' + p.id + '</span>' +
                    '<span class="offer-vol">' + p.vol + ' л</span>';
                li.addEventListener('click', function () {
                    input.value = p.id;
                    input.dataset.selectedId = p.id;
                    tr.querySelector('.vol-display').textContent = p.vol;
                    tr.querySelector('.weight-display').textContent = p.weight;
                    crossTr.querySelector('.cross-offer').textContent = p.id;
                    list.classList.remove('show');
                    calculateRow(tr, crossTr, localTr);
                });
                list.appendChild(li);
            });

            list.classList.add('show');
        }

        input.addEventListener('input', function (e) {
            input.dataset.selectedId = '';
            tr.querySelector('.vol-display').textContent = '—';
            tr.querySelector('.weight-display').textContent = '—';
            tr.querySelector('.total-vol-display').textContent = '—';
            tr.querySelector('.unit-price').textContent = '0,00';
            tr.querySelector('.total-price').textContent = '0,00';
            resetCrossRow(crossTr);
            resetLocalRow(localTr);
            calculateGrandTotal();
            renderList(e.target.value);
        });

        input.addEventListener('keydown', function (e) {
            const items = list.querySelectorAll('.autocomplete-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault(); currentFocus++; addActive(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault(); currentFocus--; addActive(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (currentFocus > -1 && items[currentFocus]) items[currentFocus].click();
            } else if (e.key === 'Escape') {
                list.classList.remove('show');
            }
        });

        function addActive(items) {
            if (!items) return false;
            removeActive(items);
            if (currentFocus >= items.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (items.length - 1);
            items[currentFocus].classList.add('active');
            items[currentFocus].scrollIntoView({ block: 'nearest' });
        }
        function removeActive(items) {
            for (let i = 0; i < items.length; i++) items[i].classList.remove('active');
        }

        document.addEventListener('click', function (e) {
            if (!inputWrapper.contains(e.target)) list.classList.remove('show');
        });

        input.addEventListener('focus', function () {
            if (input.value.trim() !== '') renderList(input.value);
        });

        qtyInput.addEventListener('input', function () {
            calculateRow(tr, crossTr, localTr);
        });

        removeBtn.addEventListener('click', function () {
            tr.remove();
            crossTr.remove();
            localTr.remove();
            calculateGrandTotal();
        });
    }

    // ---------- Расчёт одной строки ----------
    function calculateRow(tr, crossTr, localTr) {
        const input = tr.querySelector('.autocomplete-input');
        const offerId = input.dataset.selectedId;
        const qty = parseInt(tr.querySelector('.qty-input').value) || 0;
        const clusterFrom = clusterFromSelect.value;
        const clusterTo = clusterToSelect.value;

        if (offerId && products[offerId]) {
            const p = products[offerId];
            localTr.querySelector('.local-offer').textContent = offerId;
            localTr.querySelector('.local-vol').textContent = p.value;
            localTr.querySelector('.local-weight').textContent = p.weight;
            localTr.querySelector('.local-qty').textContent = qty;
        } else {
            localTr.querySelector('.local-offer').textContent = '—';
            localTr.querySelector('.local-vol').textContent = '—';
            localTr.querySelector('.local-weight').textContent = '—';
            localTr.querySelector('.local-qty').textContent = '—';
        }

        let totalVol = 0;
        if (offerId && products[offerId]) {
            const volPerItem = parseFloat(products[offerId].value) || 0;
            totalVol = volPerItem * qty;
            tr.querySelector('.total-vol-display').textContent = formatVolume(totalVol);
            crossTr.querySelector('.cross-volume').textContent = formatVolume(totalVol);
            localTr.querySelector('.local-total-vol').textContent = formatVolume(totalVol);
        } else {
            tr.querySelector('.total-vol-display').textContent = '—';
            crossTr.querySelector('.cross-volume').textContent = '—';
            localTr.querySelector('.local-total-vol').textContent = '—';
        }

        if (!offerId || !products[offerId] || !clusterFrom || !clusterTo) {
            tr.querySelector('.unit-price').textContent = '0,00';
            tr.querySelector('.total-price').textContent = '0,00';

            const p1 = crossTr.querySelector('.cross-pallet-price');
            p1.innerHTML = '0,00'; p1.dataset.actual = '0';
            crossTr.querySelector('.cross-box-price').textContent = '0,00';

            localTr.querySelector('.local-unit-price').textContent = '0,00';
            localTr.querySelector('.local-total-price').textContent = '0,00';

            calculateGrandTotal();
            return;
        }

        const volume = parseFloat(products[offerId].value) || 0;

        // 1. Логистика (from → to)
        fetch('get-tariff.php?from=' + encodeURIComponent(clusterFrom) + '&to=' + encodeURIComponent(clusterTo) + '&volume=' + volume)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok && data.price !== null) {
                    const unitPrice = parseFloat(data.price);
                    tr.querySelector('.unit-price').textContent = formatMoney(unitPrice);
                    tr.querySelector('.total-price').textContent = formatMoney(unitPrice * qty);
                } else {
                    tr.querySelector('.unit-price').textContent = 'Нет тарифа';
                    tr.querySelector('.total-price').textContent = '0,00';
                }
                calculateGrandTotal();
            })
            .catch(function () {
                tr.querySelector('.unit-price').textContent = 'Ошибка';
                tr.querySelector('.total-price').textContent = '0,00';
                calculateGrandTotal();
            });

        // 2. Кросс-докинг по маршруту (from → to)
        fetch('get-cross-dok.php?from=' + encodeURIComponent(clusterFrom) + '&to=' + encodeURIComponent(clusterTo))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                const palletCell = crossTr.querySelector('.cross-pallet-price');
                const boxCell = crossTr.querySelector('.cross-box-price');

                if (data.ok && data.pallet !== null) {
                    const palletRate = parseFloat(data.pallet);
                    const boxRate = parseFloat(data.box);

                    const realSum = totalVol * palletRate;
                    const effectiveVol = Math.max(totalVol, MIN_PALLET_VOLUME);
                    const actualSum = effectiveVol * palletRate;
                    const boxSum = totalVol * boxRate;

                    renderPalletCell(palletCell, realSum, actualSum, totalVol < MIN_PALLET_VOLUME);
                    boxCell.textContent = formatMoney(boxSum);
                } else {
                    palletCell.textContent = 'Нет тарифа';
                    palletCell.dataset.actual = '0';
                    boxCell.textContent = 'Нет тарифа';
                }
                calculateGrandTotal();
            })
            .catch(function () {
                const pc = crossTr.querySelector('.cross-pallet-price');
                pc.textContent = 'Ошибка'; pc.dataset.actual = '0';
                crossTr.querySelector('.cross-box-price').textContent = 'Ошибка';
                calculateGrandTotal();
            });

        // 3. Логистика внутри кластера доставки (to → to)
        fetch('get-tariff.php?from=' + encodeURIComponent(clusterTo) + '&to=' + encodeURIComponent(clusterTo) + '&volume=' + volume)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.ok && data.price !== null) {
                    const unitPrice = parseFloat(data.price);
                    localTr.querySelector('.local-unit-price').textContent = formatMoney(unitPrice);
                    localTr.querySelector('.local-total-price').textContent = formatMoney(unitPrice * qty);
                } else {
                    localTr.querySelector('.local-unit-price').textContent = 'Нет тарифа';
                    localTr.querySelector('.local-total-price').textContent = '0,00';
                }
                calculateGrandTotal();
            })
            .catch(function () {
                localTr.querySelector('.local-unit-price').textContent = 'Ошибка';
                localTr.querySelector('.local-total-price').textContent = '0,00';
                calculateGrandTotal();
            });
    }

    // ---------- Итоги ----------
    function calculateGrandTotal() {
        let sumCost = 0, sumVol = 0;
        let sumCPallet = 0, sumCBox = 0;
        let sumLocalCost = 0, sumLocalVol = 0;

        // Собираем данные из таблиц
        document.querySelectorAll('#calcTable tbody tr').forEach(function (tr) {
            sumCost += parseMoney(tr.querySelector('.total-price').textContent);
            const v = tr.querySelector('.total-vol-display').textContent;
            if (v !== '—') sumVol += parseMoney(v);
        });

        document.querySelectorAll('#crossTable tbody tr').forEach(function (tr) {
            const pc = tr.querySelector('.cross-pallet-price');
            const a = parseFloat(pc.dataset.actual || '0');
            if (!isNaN(a)) sumCPallet += a;

            const bt = tr.querySelector('.cross-box-price').textContent;
            if (bt !== 'Нет тарифа' && bt !== 'Ошибка') sumCBox += parseMoney(bt);
        });

        document.querySelectorAll('#localLogTable tbody tr').forEach(function (tr) {
            sumLocalCost += parseMoney(tr.querySelector('.local-total-price').textContent);
            const v = tr.querySelector('.local-total-vol').textContent;
            if (v !== '—') sumLocalVol += parseMoney(v);
        });

        // Выводим итоги под таблицами
        grandTotalEl.textContent = formatMoney(sumCost);
        totalVolumeEl.textContent = formatVolume(sumVol);
        crossPalletTotalEl.textContent = formatMoney(sumCPallet);
        crossBoxTotalEl.textContent = formatMoney(sumCBox);
        localLogTotalEl.textContent = formatMoney(sumLocalCost);
        localLogVolumeEl.textContent = formatVolume(sumLocalVol);

        // ---------- Сводка ----------
        
        // 1. Считаем значения для ПЕРВОЙ строки сводки
        const diff = sumCost - sumLocalCost; 
        const palletPlusLocal = sumCPallet + sumLocalCost; 
        const boxPlusLocal = sumCBox + sumLocalCost;       

        // Записываем их в HTML
        summaryDiffEl.textContent = formatMoney(diff) + ' ₽';
        summaryPalletSumEl.textContent = formatMoney(palletPlusLocal) + ' ₽';
        summaryBoxSumEl.textContent = formatMoney(boxPlusLocal) + ' ₽';

        // 2. Считаем значения для ВТОРОЙ строки сводки
        // Берем "Общую стоимость логистики" (sumCost) и вычитаем из нее значения из первой строки
        
        // Для 2-го столбца: sumCost - (Кросс-докинг + Локальная)
        const finalPalletCheck = sumCost - palletPlusLocal;
        summaryPalletDeltaEl.textContent = formatMoney(finalPalletCheck) + ' ₽';

        // Для 3-го столбца: sumCost - (Кросс-докинг + Локальная)
        const finalBoxCheck = sumCost - boxPlusLocal;
        summaryBoxDeltaEl.textContent = formatMoney(finalBoxCheck) + ' ₽';

        // 3. Красим значения
        summaryPalletDeltaEl.classList.remove('diff-positive', 'diff-negative');
        summaryBoxDeltaEl.classList.remove('diff-positive', 'diff-negative');

        const epsilon = 0.005;

        // Если положительное (> 0) - зеленый, если отрицательное (< 0) - красный
        if (finalPalletCheck > epsilon) {
            summaryPalletDeltaEl.classList.add('diff-negative');
        } else if (finalPalletCheck < -epsilon) {
            summaryPalletDeltaEl.classList.add('diff-positive');
        }

        if (finalBoxCheck > epsilon) {
            summaryBoxDeltaEl.classList.add('diff-negative');
        } else if (finalBoxCheck < -epsilon) {
            summaryBoxDeltaEl.classList.add('diff-positive');
        }
    }
    // ---------- Смена кластеров ----------
    function recalcAll() {
        const rows = document.querySelectorAll('#calcTable tbody tr');
        const crossRows = document.querySelectorAll('#crossTable tbody tr');
        const localRows = document.querySelectorAll('#localLogTable tbody tr');
        rows.forEach(function (tr, i) {
            if (crossRows[i] && localRows[i]) {
                calculateRow(tr, crossRows[i], localRows[i]);
            }
        });
    }

    clusterFromSelect.addEventListener('change', recalcAll);
    clusterToSelect.addEventListener('change', recalcAll);

    addBtn.addEventListener('click', addRow);
    addRow();

})();
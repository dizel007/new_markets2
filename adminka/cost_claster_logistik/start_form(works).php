<?php
/**********************************************************************************************************************
 * Файл start_form.php
 *
 * Отвечает за отрисовку интерфейса:
 *  - форма выбора магазина и кластеров;
 *  - основная таблица логистики (from → to);
 *  - таблица кросс-докинга по маршруту (from → to);
 *  - таблица логистики внутри кластера доставки (to → to);
 *  - итоговая сводка (4-я таблица) с двумя строками.
 *
 * Для кросс-докинга по паллете действует правило минимального объёма — 1900 л.
 *********************************************************************************************************************/

function render_form($products = [], $selectedFrom = '', $selectedTo = '')
{
    // ---------------------------------------------------------
    // 1. ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ
    // ---------------------------------------------------------
    $offset = "../../";
    require_once $offset . "connect_db.php";
    global $pdo;

    $optionsFrom = '';
    $optionsTo   = '';

    // ---------------------------------------------------------
    // 2. ЗАГРУЗКА СПИСКА КЛАСТЕРОВ ИЗ БАЗЫ
    // ---------------------------------------------------------
    if (isset($pdo)) {
        $fromClusters = $pdo->query("SELECT DISTINCT claster_send FROM ozon_logistika_price ORDER BY claster_send ASC")->fetchAll(PDO::FETCH_COLUMN);
        $toClusters   = $pdo->query("SELECT DISTINCT claster_get FROM ozon_logistika_price ORDER BY claster_get ASC")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($fromClusters as $c) {
            $sel = ($c === $selectedFrom) ? 'selected' : '';
            $optionsFrom .= "<option value=\"" . htmlspecialchars($c) . "\" $sel>" . htmlspecialchars($c) . "</option>";
        }
        foreach ($toClusters as $c) {
            $sel = ($c === $selectedTo) ? 'selected' : '';
            $optionsTo .= "<option value=\"" . htmlspecialchars($c) . "\" $sel>" . htmlspecialchars($c) . "</option>";
        }
    }

    // ---------------------------------------------------------
    // 3. ПОДГОТОВКА ДАННЫХ О ТОВАРАХ ДЛЯ JAVASCRIPT
    // ---------------------------------------------------------
    $productsJson = json_encode($products, JSON_UNESCAPED_UNICODE);
    if (!$productsJson) {
        $productsJson = '{}';
    }

    // ---------------------------------------------------------
    // 4. ПРОВЕРКА: НАЖАТА ЛИ КНОПКА START
    // ---------------------------------------------------------
    $shopSelected    = !empty($_GET['ozon_shop']);
    $isFormSubmitted = $shopSelected && !empty($selectedFrom) && !empty($selectedTo);
    ?>
    <head>
        <meta charset="utf-8">
        <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="css/cost_logistik.css">
        <style>
            .cross-real-note {
                display: block;
                color: #d9534f;
                font-size: 12px;
                line-height: 1.2;
                margin-bottom: 2px;
            }
            .cross-actual {
                font-weight: 600;
                color: #1f2a3a;
            }
            /* Стили для итоговой сводки */
            .summary-table th {
                background: #f5f7fa;
                font-weight: 600;
                text-align: center;
                vertical-align: top;
            }
            .summary-table td {
                text-align: center;
                font-size: 16px;
                padding: 15px;
            }
            .summary-table td.summary-value {
                font-weight: 700;
                color: #1f2a3a;
                font-size: 18px;
            }
            .summary-formula {
                display: block;
                font-size: 12px;
                font-weight: 400;
                color: #6b7280;
                margin-top: 4px;
                line-height: 1.3;
            }
            .summary-title {
                display: block;
                font-size: 13px;
                font-weight: 700;
                color: #1f2a3a;
                margin-bottom: 4px;
            }
            /* Подсветка разниц во второй строке */
            .diff-positive {
                color: #c0392b; /* красный */
                font-weight: 700;
            }
            .diff-negative {
                color: #27ae60; /* зелёный */
                font-weight: 700;
            }
        </style>
    </head>
    <body>

    <h1 class="page-title">Расчёт стоимости логистики и кросс-докинга</h1>
    <p class="page-subtitle">Укажите магазин и кластеры движения, чтобы рассчитать тариф по артикулам</p>

    <form action="#" method="get" class="main-form">
        <label>Магазин</label>
        <select required name="ozon_shop">
            <option value="ozon_anmaks" <?= ($_GET['ozon_shop'] ?? '') === 'ozon_anmaks' ? 'selected' : '' ?>>OZON ООО</option>
            <option value="ozon_ip_zel" <?= ($_GET['ozon_shop'] ?? '') === 'ozon_ip_zel' ? 'selected' : '' ?>>OZON ИП ЗЕЛ</option>
        </select>

        <label>Кластер отгрузки</label>
        <select name="ClusterFrom" id="ClusterFrom" required>
            <option value="">Выберите...</option>
            <?= $optionsFrom ?>
        </select>

        <label>Кластер доставки</label>
        <select name="ClusterTo" id="ClusterTo" required>
            <option value="">Выберите...</option>
            <?= $optionsTo ?>
        </select>

        <input type="submit" value="START">
    </form>

    <?php if ($isFormSubmitted): ?>
    <div id="calc-section" style="max-width: 1200px; margin: 0 auto;">

        <!-- =========================================================
             ТАБЛИЦА ЛОГИСТИКИ (from → to)
             ========================================================= -->
        <h2 style="margin: 30px 0 15px;">Логистика</h2>
        <button class="add-btn" id="addRowBtn" type="button">+ Добавить товар</button>

        <table class="calc-table" id="calcTable">
            <thead>
                <tr>
                    <th>Артикул (начните вводить)</th>
                    <th>Объем, л</th>
                    <th>Вес, г</th>
                    <th style="width: 120px;">Кол-во</th>
                    <th>Объем всего, л</th>
                    <th>Стоимость за 1 шт (₽)</th>
                    <th>Итого (₽)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="grand-total">
            <div>Общая стоимость логистики: <span id="grandTotal">0,00</span> ₽</div>
            <div style="font-size: 18px; color: #1f2a3a; margin-top: 10px;">Общий объем товаров: <span id="totalVolume">0,00</span> л</div>
        </div>

        <!-- =========================================================
             ТАБЛИЦА КРОСС-ДОКИНГА ПО МАРШРУТУ (from → to)
             ========================================================= -->
        <h2 style="margin: 40px 0 15px;">Кросс-докинг (по маршруту)</h2>

        <table class="calc-table" id="crossTable">
            <thead>
                <tr>
                    <th>Артикул</th>
                    <th>Объем всего, л</th>
                    <th>Кросс-докинг (паллета), ₽</th>
                    <th>Кросс-докинг (коробка), ₽</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="grand-total">
            <div>Итого кросс-докинг (паллета): <span id="crossPalletTotal">0,00</span> ₽</div>
            <div style="font-size: 18px; color: #1f2a3a; margin-top: 10px;">Итого кросс-докинг (коробка): <span id="crossBoxTotal">0,00</span> ₽</div>
        </div>

        <!-- =========================================================
             ТАБЛИЦА ЛОГИСТИКИ ВНУТРИ КЛАСТЕРА ДОСТАВКИ (to → to)
             ========================================================= -->
        <h2 style="margin: 40px 0 15px;">Логистика внутри кластера доставки</h2>

        <table class="calc-table" id="localLogTable">
            <thead>
                <tr>
                    <th>Артикул</th>
                    <th>Объем, л</th>
                    <th>Вес, г</th>
                    <th style="width: 120px;">Кол-во</th>
                    <th>Объем всего, л</th>
                    <th>Стоимость за 1 шт (₽)</th>
                    <th>Итого (₽)</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="grand-total">
            <div>Итого логистика внутри кластера: <span id="localLogTotal">0,00</span> ₽</div>
            <div style="font-size: 18px; color: #1f2a3a; margin-top: 10px;">Общий объем (внутри кластера): <span id="localLogVolume">0,00</span> л</div>
        </div>

        <!-- =========================================================
             ИТОГОВАЯ СВОДКА (4-я таблица)
             ========================================================= -->
        <h2 style="margin: 50px 0 15px;">Итоговая сводка</h2>

        <table class="calc-table summary-table" id="summaryTable">
            <thead>
                <tr>
                    <th>
                        <span class="summary-title">Разница</span>
                        <span class="summary-formula">
                            Логистика − Логистика внутри кластера
                        </span>
                    </th>
                    <th>
                        <span class="summary-title">Кросс-докинг (паллета)</span>
                        <span class="summary-formula">
                            Кросс-докинг (паллета) + Логистика внутри кластера
                        </span>
                    </th>
                    <th>
                        <span class="summary-title">Кросс-докинг (коробка)</span>
                        <span class="summary-formula">
                            Кросс-докинг (коробка) + Логистика внутри кластера
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <!-- 1-я строка: абсолютные значения -->
                <tr>
                    <td class="summary-value" id="summaryDiff">0,00 ₽</td>
                    <td class="summary-value" id="summaryPalletSum">0,00 ₽</td>
                    <td class="summary-value" id="summaryBoxSum">0,00 ₽</td>
                </tr>
                <!-- 2-я строка: разницы (2-й и 3-й столбец), 1-й — пустой -->
                <tr>
                    <td></td>
                    <td class="summary-value" id="summaryPalletDelta">0,00 ₽</td>
                    <td class="summary-value" id="summaryBoxDelta">0,00 ₽</td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- =========================================================
         JAVASCRIPT ЛОГИКА
         ========================================================= -->
    <script>
        const products = <?= $productsJson ?>;

        const MIN_PALLET_VOLUME = 1900;

        if (document.getElementById('calcTable')) {

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

            // Элементы итоговой сводки (1-я строка)
            const summaryDiffEl = document.getElementById('summaryDiff');
            const summaryPalletSumEl = document.getElementById('summaryPalletSum');
            const summaryBoxSumEl = document.getElementById('summaryBoxSum');

            // Элементы итоговой сводки (2-я строка — разницы)
            const summaryPalletDeltaEl = document.getElementById('summaryPalletDelta');
            const summaryBoxDeltaEl = document.getElementById('summaryBoxDelta');

            const clusterFromSelect = document.getElementById('ClusterFrom');
            const clusterToSelect = document.getElementById('ClusterTo');

            const productArray = Object.keys(products).map(id => ({
                id: id,
                vol: products[id].value,
                weight: products[id].weight
            }));

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
                    cell.innerHTML = `
                        <span class="cross-real-note">Реальный: ${formatMoney(realSum)} ₽</span>
                        <span class="cross-actual">${formatMoney(actualSum)} ₽</span>
                    `;
                } else {
                    cell.innerHTML = `<span class="cross-actual">${formatMoney(actualSum)} ₽</span>`;
                }
                cell.dataset.actual = actualSum;
            }

            // ---------- Добавление строки ----------
            function addRow() {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <div class="autocomplete-wrapper">
                            <input type="text" class="autocomplete-input" placeholder="Начните вводить артикул..." autocomplete="off">
                            <ul class="autocomplete-list"></ul>
                        </div>
                    </td>
                    <td class="vol-display">—</td>
                    <td class="weight-display">—</td>
                    <td style="width: 120px;"><input type="number" class="qty-input" value="1" min="1" style="width: 100%; padding: 10px; font-size: 15px; text-align: center;"></td>
                    <td class="total-vol-display">—</td>
                    <td class="unit-price">0,00</td>
                    <td class="total-price">0,00</td>
                    <td><button class="remove-btn" type="button">✕</button></td>
                `;
                tbody.appendChild(tr);

                const crossTr = document.createElement('tr');
                crossTr.innerHTML = `
                    <td class="cross-offer">—</td>
                    <td class="cross-volume">—</td>
                    <td class="cross-pallet-price" data-actual="0">0,00</td>
                    <td class="cross-box-price">0,00</td>
                `;
                crossTbody.appendChild(crossTr);

                const localTr = document.createElement('tr');
                localTr.innerHTML = `
                    <td class="local-offer">—</td>
                    <td class="local-vol">—</td>
                    <td class="local-weight">—</td>
                    <td class="local-qty">—</td>
                    <td class="local-total-vol">—</td>
                    <td class="local-unit-price">0,00</td>
                    <td class="local-total-price">0,00</td>
                `;
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

                    const filtered = productArray.filter(p => p.id.toLowerCase().includes(term)).slice(0, 50);
                    if (filtered.length === 0) { list.classList.remove('show'); return; }

                    filtered.forEach(p => {
                        const li = document.createElement('li');
                        li.className = 'autocomplete-item';
                        li.innerHTML = `
                            <span class="offer-name">${p.id}</span>
                            <span class="offer-vol">${p.vol} л</span>
                        `;
                        li.addEventListener('click', () => {
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

                input.addEventListener('input', (e) => {
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

                input.addEventListener('keydown', (e) => {
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

                document.addEventListener('click', (e) => {
                    if (!inputWrapper.contains(e.target)) list.classList.remove('show');
                });

                input.addEventListener('focus', () => {
                    if (input.value.trim() !== '') renderList(input.value);
                });

                qtyInput.addEventListener('input', () => calculateRow(tr, crossTr, localTr));

                removeBtn.addEventListener('click', () => {
                    tr.remove();
                    crossTr.remove();
                    localTr.remove();
                    calculateGrandTotal();
                });
            }

            // ---------- Расчёт одной строки ----------
            async function calculateRow(tr, crossTr, localTr) {
                const input = tr.querySelector('.autocomplete-input');
                const offerId = input.dataset.selectedId;
                const qty = parseInt(tr.querySelector('.qty-input').value) || 0;
                const clusterFrom = clusterFromSelect.value;
                const clusterTo = clusterToSelect.value;

                // Заполняем локальную строку данными товара
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
                try {
                    const response = await fetch(`get-tariff.php?from=${encodeURIComponent(clusterFrom)}&to=${encodeURIComponent(clusterTo)}&volume=${volume}`);
                    const data = await response.json();
                    if (data.ok && data.price !== null) {
                        const unitPrice = parseFloat(data.price);
                        tr.querySelector('.unit-price').textContent = formatMoney(unitPrice);
                        tr.querySelector('.total-price').textContent = formatMoney(unitPrice * qty);
                    } else {
                        tr.querySelector('.unit-price').textContent = 'Нет тарифа';
                        tr.querySelector('.total-price').textContent = '0,00';
                    }
                } catch (err) {
                    tr.querySelector('.unit-price').textContent = 'Ошибка';
                    tr.querySelector('.total-price').textContent = '0,00';
                }

                // 2. Кросс-докинг по маршруту (from → to)
                try {
                    const response = await fetch(`get-cross-dok.php?from=${encodeURIComponent(clusterFrom)}&to=${encodeURIComponent(clusterTo)}`);
                    const data = await response.json();
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
                } catch (err) {
                    const pc = crossTr.querySelector('.cross-pallet-price');
                    pc.textContent = 'Ошибка'; pc.dataset.actual = '0';
                    crossTr.querySelector('.cross-box-price').textContent = 'Ошибка';
                }

                // 3. Логистика внутри кластера доставки (to → to)
                try {
                    const response = await fetch(`get-tariff.php?from=${encodeURIComponent(clusterTo)}&to=${encodeURIComponent(clusterTo)}&volume=${volume}`);
                    const data = await response.json();
                    if (data.ok && data.price !== null) {
                        const unitPrice = parseFloat(data.price);
                        localTr.querySelector('.local-unit-price').textContent = formatMoney(unitPrice);
                        localTr.querySelector('.local-total-price').textContent = formatMoney(unitPrice * qty);
                    } else {
                        localTr.querySelector('.local-unit-price').textContent = 'Нет тарифа';
                        localTr.querySelector('.local-total-price').textContent = '0,00';
                    }
                } catch (err) {
                    localTr.querySelector('.local-unit-price').textContent = 'Ошибка';
                    localTr.querySelector('.local-total-price').textContent = '0,00';
                }

                calculateGrandTotal();
            }

            // ---------- Итоги ----------
            function calculateGrandTotal() {
                let sumCost = 0, sumVol = 0;
                let sumCPallet = 0, sumCBox = 0;
                let sumLocalCost = 0, sumLocalVol = 0;

                document.querySelectorAll('#calcTable tbody tr').forEach(tr => {
                    sumCost += parseMoney(tr.querySelector('.total-price').textContent);
                    const v = tr.querySelector('.total-vol-display').textContent;
                    if (v !== '—') sumVol += parseMoney(v);
                });

                document.querySelectorAll('#crossTable tbody tr').forEach(tr => {
                    const pc = tr.querySelector('.cross-pallet-price');
                    const a = parseFloat(pc.dataset.actual || '0');
                    if (!isNaN(a)) sumCPallet += a;

                    const bt = tr.querySelector('.cross-box-price').textContent;
                    if (bt !== 'Нет тарифа' && bt !== 'Ошибка') sumCBox += parseMoney(bt);
                });

                document.querySelectorAll('#localLogTable tbody tr').forEach(tr => {
                    sumLocalCost += parseMoney(tr.querySelector('.local-total-price').textContent);
                    const v = tr.querySelector('.local-total-vol').textContent;
                    if (v !== '—') sumLocalVol += parseMoney(v);
                });

                grandTotalEl.textContent = formatMoney(sumCost);
                totalVolumeEl.textContent = formatVolume(sumVol);
                crossPalletTotalEl.textContent = formatMoney(sumCPallet);
                crossBoxTotalEl.textContent = formatMoney(sumCBox);
                localLogTotalEl.textContent = formatMoney(sumLocalCost);
                localLogVolumeEl.textContent = formatVolume(sumLocalVol);

                // ---------- Итоговая сводка (4-я таблица, 1-я строка) ----------
                const diff = sumCost - sumLocalCost;
                const palletPlusLocal = sumCPallet + sumLocalCost;
                const boxPlusLocal = sumCBox + sumLocalCost;

                summaryDiffEl.textContent = formatMoney(diff) + ' ₽';
                summaryPalletSumEl.textContent = formatMoney(palletPlusLocal) + ' ₽';
                summaryBoxSumEl.textContent = formatMoney(boxPlusLocal) + ' ₽';

                // ---------- Итоговая сводка (4-я таблица, 2-я строка — разницы) ----------
                // Разница 2-го столбца: (Кросс-докинг паллета + лок. логистика) − Разница
                const deltaPallet = palletPlusLocal - diff;
                // Разница 3-го столбца: (Кросс-докинг коробка + лок. логистика) − Разница
                const deltaBox = boxPlusLocal - diff;

                summaryPalletDeltaEl.textContent = formatMoney(deltaPallet) + ' ₽';
                summaryBoxDeltaEl.textContent = formatMoney(deltaBox) + ' ₽';

                // Подсветка: если deltaPallet > deltaBox → паллета красная, коробка зелёная
                //            если deltaPallet < deltaBox → паллета зелёная, коробка красная
                //            если равны → без подсветки
                summaryPalletDeltaEl.classList.remove('diff-positive', 'diff-negative');
                summaryBoxDeltaEl.classList.remove('diff-positive', 'diff-negative');

                const epsilon = 0.005;
                if (Math.abs(deltaPallet - deltaBox) > epsilon) {
                    if (deltaPallet > deltaBox) {
                        summaryPalletDeltaEl.classList.add('diff-positive'); // красный
                        summaryBoxDeltaEl.classList.add('diff-negative');   // зелёный
                    } else {
                        summaryPalletDeltaEl.classList.add('diff-negative'); // зелёный
                        summaryBoxDeltaEl.classList.add('diff-positive');   // красный
                    }
                }
            }

            // ---------- Смена кластеров ----------
            function recalcAll() {
                const rows = document.querySelectorAll('#calcTable tbody tr');
                const crossRows = document.querySelectorAll('#crossTable tbody tr');
                const localRows = document.querySelectorAll('#localLogTable tbody tr');
                rows.forEach((tr, i) => {
                    if (crossRows[i] && localRows[i]) {
                        calculateRow(tr, crossRows[i], localRows[i]);
                    }
                });
            }

            clusterFromSelect.addEventListener('change', recalcAll);
            clusterToSelect.addEventListener('change', recalcAll);

            addBtn.addEventListener('click', addRow);
            addRow();
        }
    </script>
    </body>
    <?php
}
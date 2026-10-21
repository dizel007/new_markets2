<?php
function render_form($products = [], $selectedFrom = '', $selectedTo = '')
{
    $offset = "../../";
    require_once $offset . "connect_db.php";
    global $pdo;

    $optionsFrom = '';
    $optionsTo   = '';

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

    $productsJson = json_encode($products, JSON_UNESCAPED_UNICODE);
    if (!$productsJson) {
        $productsJson = '{}';
    }

    $shopSelected    = !empty($_GET['ozon_shop']);
    $isFormSubmitted = $shopSelected && !empty($selectedFrom) && !empty($selectedTo);
    ?>
    <head>
        <meta charset="utf-8">
        <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
        <!-- Все стили теперь здесь -->
        <link rel="stylesheet" href="css/cost_logistik.css">
    </head>
    <body>

    <h1 class="page-title">Расчёт стоимости логистики</h1>
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
        <button class="add-btn" id="addRowBtn" type="button">+ Добавить товар</button>
        <table class="calc-table" id="calcTable">
            <thead>
                <tr>
                    <th>Артикул (начните вводить)</th>
                    <th>Объем, л</th>
                    <th>Вес, г</th>
                    <th style="width: 120px;">Кол-во</th>
                    <th>Стоимость за 1 шт (₽)</th>
                    <th>Итого (₽)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="grand-total">
            Общая стоимость логистики: <span id="grandTotal">0,00</span> ₽
        </div>
    </div>
    <?php endif; ?>

    <script>
        const products = <?= $productsJson ?>;
        
        if (document.getElementById('calcTable')) {
            const tbody = document.querySelector('#calcTable tbody');
            const addBtn = document.getElementById('addRowBtn');
            const grandTotalEl = document.getElementById('grandTotal');
            const clusterFromSelect = document.getElementById('ClusterFrom');
            const clusterToSelect = document.getElementById('ClusterTo');

            const productArray = Object.keys(products).map(id => ({
                id: id,
                vol: products[id].value,
                weight: products[id].weight
            }));

            function formatMoney(num) {
                if (isNaN(num) || num === null) return '0,00';
                return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
            }

            function parseMoney(str) {
                if (!str) return 0;
                const clean = String(str).replace(/\s/g, '').replace(',', '.');
                const val = parseFloat(clean);
                return isNaN(val) ? 0 : val;
            }

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
                    <td class="unit-price">0,00</td>
                    <td class="total-price">0,00</td>
                    <td><button class="remove-btn" type="button">✕</button></td>
                `;

                tbody.appendChild(tr);
                attachEvents(tr);
            }

            function attachEvents(tr) {
                const inputWrapper = tr.querySelector('.autocomplete-wrapper');
                const input = tr.querySelector('.autocomplete-input');
                const list = tr.querySelector('.autocomplete-list');
                const qtyInput = tr.querySelector('.qty-input');
                const removeBtn = tr.querySelector('.remove-btn');

                let currentFocus = -1;

                function renderList(searchTerm) {
                    list.innerHTML = '';
                    currentFocus = -1;
                    
                    const term = searchTerm.toLowerCase().trim();
                    if (!term) {
                        list.classList.remove('show');
                        return;
                    }

                    const filtered = productArray.filter(p => p.id.toLowerCase().includes(term)).slice(0, 50);

                    if (filtered.length === 0) {
                        list.classList.remove('show');
                        return;
                    }

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
                            list.classList.remove('show');
                            calculateRow(tr);
                        });
                        list.appendChild(li);
                    });

                    list.classList.add('show');
                }

                input.addEventListener('input', (e) => {
                    input.dataset.selectedId = '';
                    tr.querySelector('.vol-display').textContent = '—';
                    tr.querySelector('.weight-display').textContent = '—';
                    tr.querySelector('.unit-price').textContent = '0,00';
                    tr.querySelector('.total-price').textContent = '0,00';
                    calculateGrandTotal();
                    
                    renderList(e.target.value);
                });

                input.addEventListener('keydown', (e) => {
                    const items = list.querySelectorAll('.autocomplete-item');
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        currentFocus++;
                        addActive(items);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        currentFocus--;
                        addActive(items);
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (currentFocus > -1 && items[currentFocus]) {
                            items[currentFocus].click();
                        }
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
                    for (let i = 0; i < items.length; i++) {
                        items[i].classList.remove('active');
                    }
                }

                document.addEventListener('click', (e) => {
                    if (!inputWrapper.contains(e.target)) {
                        list.classList.remove('show');
                    }
                });

                input.addEventListener('focus', () => {
                    if (input.value.trim() !== '') {
                        renderList(input.value);
                    }
                });

                qtyInput.addEventListener('input', () => calculateRow(tr));

                removeBtn.addEventListener('click', () => {
                    tr.remove();
                    calculateGrandTotal();
                });
            }

            async function calculateRow(tr) {
                const input = tr.querySelector('.autocomplete-input');
                const offerId = input.dataset.selectedId;
                const qty = parseInt(tr.querySelector('.qty-input').value) || 0;
                const clusterFrom = clusterFromSelect.value;
                const clusterTo = clusterToSelect.value;

                if (!offerId || !products[offerId] || !clusterFrom || !clusterTo) {
                    tr.querySelector('.unit-price').textContent = '0,00';
                    tr.querySelector('.total-price').textContent = '0,00';
                    calculateGrandTotal();
                    return;
                }

                const volume = products[offerId].value;

                try {
                    const response = await fetch(`get-tariff.php?from=${encodeURIComponent(clusterFrom)}&to=${encodeURIComponent(clusterTo)}&volume=${volume}`);
                    const data = await response.json();

                    if (data.ok && data.price !== null) {
                        const unitPrice = parseFloat(data.price);
                        const total = unitPrice * qty;
                        tr.querySelector('.unit-price').textContent = formatMoney(unitPrice);
                        tr.querySelector('.total-price').textContent = formatMoney(total);
                    } else {
                        tr.querySelector('.unit-price').textContent = 'Нет тарифа';
                        tr.querySelector('.total-price').textContent = '0,00';
                    }
                } catch (err) {
                    tr.querySelector('.unit-price').textContent = 'Ошибка';
                }
                calculateGrandTotal();
            }

            function calculateGrandTotal() {
                let sum = 0;
                document.querySelectorAll('.total-price').forEach(td => {
                    sum += parseMoney(td.textContent);
                });
                grandTotalEl.textContent = formatMoney(sum);
            }

            clusterFromSelect.addEventListener('change', () => {
                document.querySelectorAll('#calcTable tbody tr').forEach(tr => calculateRow(tr));
            });
            clusterToSelect.addEventListener('change', () => {
                document.querySelectorAll('#calcTable tbody tr').forEach(tr => calculateRow(tr));
            });

            addBtn.addEventListener('click', addRow);
            addRow();
        }
    </script>
    </body>
    <?php
}
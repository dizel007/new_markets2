<?php
/**********************************************************************************************************************
 * Файл start_form.php
 * 
 * Отвечает за отрисовку интерфейса: шапки, формы выбора магазина и кластеров,
 * а также таблицы для расчёта стоимости логистики.
 * 
 * Все стили вынесены в css/cost_logistik.css.
 *********************************************************************************************************************/

/**
 * Основная функция отрисовки страницы.
 * 
 * @param array  $products     Массив товаров из Ozon (артикул => ['value'=>объем, 'weight'=>вес, 'sku'=>sku])
 * @param string $selectedFrom Выбранный кластер отгрузки (из GET-запроса)
 * @param string $selectedTo   Выбранный кластер доставки (из GET-запроса)
 */
function render_form($products = [], $selectedFrom = '', $selectedTo = '')
{
    // ---------------------------------------------------------
    // 1. ПОДКЛЮЧЕНИЕ К БАЗЕ ДАННЫХ
    // ---------------------------------------------------------
    $offset = "../../"; // Относительный путь к корню проекта (на 2 уровня выше)
    require_once $offset . "connect_db.php"; // Подключаем файл с настройками БД
    global $pdo; // $pdo создается внутри connect_db.php, поэтому делаем её глобальной

    $optionsFrom = '';
    $optionsTo   = '';

    // ---------------------------------------------------------
    // 2. ЗАГРУЗКА СПИСКА КЛАСТЕРОВ ИЗ БАЗЫ
    // ---------------------------------------------------------
    // Если подключение к БД прошло успешно ($pdo существует)
    if (isset($pdo)) {
        // Запрашиваем уникальные кластеры отгрузки, сортируем по алфавиту
        $fromClusters = $pdo->query("SELECT DISTINCT claster_send FROM ozon_logistika_price ORDER BY claster_send ASC")->fetchAll(PDO::FETCH_COLUMN);
        
        // Запрашиваем уникальные кластеры доставки
        $toClusters   = $pdo->query("SELECT DISTINCT claster_get FROM ozon_logistika_price ORDER BY claster_get ASC")->fetchAll(PDO::FETCH_COLUMN);

        // Формируем HTML-опции для выпадающих списков
        // Если текущий кластер совпадает с выбранным ранее, ставим атрибут "selected"
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
    // Превращаем PHP-массив в JSON, чтобы потом передать его в JS
    $productsJson = json_encode($products, JSON_UNESCAPED_UNICODE);
    if (!$productsJson) {
        $productsJson = '{}'; // Если массив пустой, передаем пустой объект
    }

    // ---------------------------------------------------------
    // 4. ПРОВЕРКА: НАЖАТА ЛИ КНОПКА START
    // ---------------------------------------------------------
    // Магазин выбран, если в URL есть параметр ozon_shop
    $shopSelected    = !empty($_GET['ozon_shop']);
    
    // Форма считается отправленной, если есть магазин, кластер отгрузки и кластер доставки
    $isFormSubmitted = $shopSelected && !empty($selectedFrom) && !empty($selectedTo);
    ?>
    <!-- =========================================================
         ВЕРХНЯЯ ЧАСТЬ (HEAD И ПОДКЛЮЧЕНИЕ СТИЛЕЙ)
         ========================================================= -->
    <head>
        <meta charset="utf-8">
        <!-- Подключаем шрифт Inter для современного вида -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
        <!-- Подключаем общие стили (все стили вынесены сюда) -->
        <link rel="stylesheet" href="css/cost_logistik.css">
    </head>
    <body>

    <!-- =========================================================
         ЗАГОЛОВКИ СТРАНИЦЫ
         ========================================================= -->
    <h1 class="page-title">Расчёт стоимости логистики</h1>
    <p class="page-subtitle">Укажите магазин и кластеры движения, чтобы рассчитать тариф по артикулам</p>

    <!-- =========================================================
         ФОРМА ВЫБОРА МАГАЗИНА И КЛАСТЕРОВ
         ========================================================= -->
    <form action="#" method="get" class="main-form">
        <label>Магазин</label>
        <select required name="ozon_shop">
            <!-- Если в GET был выбран "ozon_anmaks", оставляем его выбранным -->
            <option value="ozon_anmaks" <?= ($_GET['ozon_shop'] ?? '') === 'ozon_anmaks' ? 'selected' : '' ?>>OZON ООО</option>
            <!-- Если выбран "ozon_ip_zel", оставляем его -->
            <option value="ozon_ip_zel" <?= ($_GET['ozon_shop'] ?? '') === 'ozon_ip_zel' ? 'selected' : '' ?>>OZON ИП ЗЕЛ</option>
        </select>

        <label>Кластер отгрузки</label>
        <select name="ClusterFrom" id="ClusterFrom" required>
            <option value="">Выберите...</option>
            <!-- Выводим сформированные ранее опции -->
            <?= $optionsFrom ?>
        </select>

        <label>Кластер доставки</label>
        <select name="ClusterTo" id="ClusterTo" required>
            <option value="">Выберите...</option>
            <!-- Выводим сформированные ранее опции -->
            <?= $optionsTo ?>
        </select>

        <input type="submit" value="START">
    </form>

    <?php if ($isFormSubmitted): ?>
    <!-- =========================================================
         БЛОК РАСЧЁТА (ПОЯВЛЯЕТСЯ ТОЛЬКО ПОСЛЕ НАЖАТИЯ START)
         ========================================================= -->
    <div id="calc-section" style="max-width: 1200px; margin: 0 auto;">
        
        <!-- Кнопка добавления новой строки -->
        <button class="add-btn" id="addRowBtn" type="button">+ Добавить товар</button>
        
        <!-- Таблица для товаров -->
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
            <tbody>
                <!-- Строки будут добавляться через JavaScript -->
            </tbody>
        </table>

        <!-- Итоговые суммы -->
        <div class="grand-total">
            <div>Общая стоимость логистики: <span id="grandTotal">0,00</span> ₽</div>
            <div style="font-size: 18px; color: #1f2a3a; margin-top: 10px;">Общий объем товаров: <span id="totalVolume">0,00</span> л</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- =========================================================
         JAVASCRIPT ЛОГИКА
         ========================================================= -->
    <script>
        // Получаем данные о товарах из PHP (массив с ключами-артикулами)
        const products = <?= $productsJson ?>;
        
        // Проверяем, существует ли таблица на странице. 
        // Если форма не была отправлена (нет #calcTable), весь JS ниже просто не выполнится.
        if (document.getElementById('calcTable')) {
            
            // Ссылки на основные DOM-элементы
            const tbody = document.querySelector('#calcTable tbody');
            const addBtn = document.getElementById('addRowBtn');
            const grandTotalEl = document.getElementById('grandTotal'); // Для вывода общей стоимости
            const totalVolumeEl = document.getElementById('totalVolume'); // Для вывода общего объема
            const clusterFromSelect = document.getElementById('ClusterFrom');
            const clusterToSelect = document.getElementById('ClusterTo');

            // Преобразуем объект products в массив для удобного поиска через .filter()
            const productArray = Object.keys(products).map(id => ({
                id: id,
                vol: products[id].value,
                weight: products[id].weight
            }));

            // ---------------------------------------------------------
            // УТИЛИТЫ ФОРМАТИРОВАНИЯ
            // ---------------------------------------------------------
            // Форматируем число как деньги: 1250.5 -> "1 250,50"
            function formatMoney(num) {
                if (isNaN(num) || num === null) return '0,00';
                return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
            }

            // Форматируем число как объём: 1.5 -> "1,50"
            function formatVolume(num) {
                if (isNaN(num) || num === null) return '0,00';
                return new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
            }

            // Парсим отформатированную строку обратно в число: "1 250,50" -> 1250.5
            function parseMoney(str) {
                if (!str) return 0;
                const clean = String(str).replace(/\s/g, '').replace(',', '.');
                const val = parseFloat(clean);
                return isNaN(val) ? 0 : val;
            }

            // ---------------------------------------------------------
            // ДОБАВЛЕНИЕ НОВОЙ СТРОКИ В ТАБЛИЦУ
            // ---------------------------------------------------------
            function addRow() {
                const tr = document.createElement('tr');
                
                // Шаблон строки. Обратите внимание на классы, они нужны для JS.
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
                attachEvents(tr); // Привязываем события к только что созданной строке
            }

            // ---------------------------------------------------------
            // ПРИВЯЗКА СОБЫТИЙ К СТРОКЕ
            // ---------------------------------------------------------
            function attachEvents(tr) {
                const inputWrapper = tr.querySelector('.autocomplete-wrapper');
                const input = tr.querySelector('.autocomplete-input');
                const list = tr.querySelector('.autocomplete-list');
                const qtyInput = tr.querySelector('.qty-input');
                const removeBtn = tr.querySelector('.remove-btn');

                let currentFocus = -1; // Индекс для навигации по списку стрелками

                // Функция отрисовки выпадающего списка
                function renderList(searchTerm) {
                    list.innerHTML = '';
                    currentFocus = -1;
                    
                    const term = searchTerm.toLowerCase().trim();
                    if (!term) {
                        list.classList.remove('show');
                        return;
                    }

                    // Фильтруем массив товаров по вхождению подстроки. Ограничиваем 50 результатами.
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
                        // Клик по элементу списка
                        li.addEventListener('click', () => {
                            input.value = p.id; // Устанавливаем значение в инпут
                            input.dataset.selectedId = p.id; // Сохраняем ID в data-атрибут
                            tr.querySelector('.vol-display').textContent = p.vol; // Показываем объем
                            tr.querySelector('.weight-display').textContent = p.weight; // Показываем вес
                            list.classList.remove('show'); // Скрываем список
                            calculateRow(tr); // Пересчитываем строку
                        });
                        list.appendChild(li);
                    });

                    list.classList.add('show');
                }

                // Ввод текста в поле поиска
                input.addEventListener('input', (e) => {
                    // Сбрасываем выбранный ID и старые значения
                    input.dataset.selectedId = '';
                    tr.querySelector('.vol-display').textContent = '—';
                    tr.querySelector('.weight-display').textContent = '—';
                    tr.querySelector('.total-vol-display').textContent = '—';
                    tr.querySelector('.unit-price').textContent = '0,00';
                    tr.querySelector('.total-price').textContent = '0,00';
                    calculateGrandTotal(); // Пересчитываем итоговую сумму
                    
                    renderList(e.target.value); // Показываем новый список
                });

                // Навигация по списку с клавиатуры
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
                            items[currentFocus].click(); // Эмулируем клик по выбранному элементу
                        }
                    } else if (e.key === 'Escape') {
                        list.classList.remove('show');
                    }
                });

                // Функции для подсветки активного элемента
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

                // Закрываем список при клике вне его
                document.addEventListener('click', (e) => {
                    if (!inputWrapper.contains(e.target)) {
                        list.classList.remove('show');
                    }
                });

                // Открываем список при фокусе (если в поле есть текст)
                input.addEventListener('focus', () => {
                    if (input.value.trim() !== '') {
                        renderList(input.value);
                    }
                });

                // Изменение количества
                qtyInput.addEventListener('input', () => calculateRow(tr));

                // Удаление строки
                removeBtn.addEventListener('click', () => {
                    tr.remove();
                    calculateGrandTotal();
                });
            }

            // ---------------------------------------------------------
            // РАСЧЁТ ОДНОЙ СТРОКИ
            // ---------------------------------------------------------
            async function calculateRow(tr) {
                const input = tr.querySelector('.autocomplete-input');
                const offerId = input.dataset.selectedId; // Выбранный артикул
                const qty = parseInt(tr.querySelector('.qty-input').value) || 0; // Количество
                const clusterFrom = clusterFromSelect.value;
                const clusterTo = clusterToSelect.value;

                // Считаем объем для этой строки (объем товара * количество)
                if (offerId && products[offerId]) {
                    const volPerItem = parseFloat(products[offerId].value) || 0;
                    const totalVol = volPerItem * qty;
                    tr.querySelector('.total-vol-display').textContent = formatVolume(totalVol);
                } else {
                    tr.querySelector('.total-vol-display').textContent = '—';
                }

                // Если не выбран товар или кластеры, сбрасываем цену
                if (!offerId || !products[offerId] || !clusterFrom || !clusterTo) {
                    tr.querySelector('.unit-price').textContent = '0,00';
                    tr.querySelector('.total-price').textContent = '0,00';
                    calculateGrandTotal();
                    return;
                }

                const volume = products[offerId].value; // Объем одной единицы

                // Делаем запрос к серверу, чтобы узнать тариф
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
                calculateGrandTotal(); // Обновляем итоговые значения
            }

            // ---------------------------------------------------------
            // ПОДСЧЁТ ОБЩИХ ЗНАЧЕНИЙ (стоимость и объем)
            // ---------------------------------------------------------
            function calculateGrandTotal() {
                let sumCost = 0;
                let sumVol = 0;

                // Проходим по всем строкам таблицы
                document.querySelectorAll('#calcTable tbody tr').forEach(tr => {
                    // Суммируем стоимость
                    const priceText = tr.querySelector('.total-price').textContent;
                    sumCost += parseMoney(priceText);

                    // Суммируем объем
                    const volText = tr.querySelector('.total-vol-display').textContent;
                    if (volText !== '—') {
                        sumVol += parseMoney(volText);
                    }
                });

                // Выводим отформатированные значения
                grandTotalEl.textContent = formatMoney(sumCost);
                totalVolumeEl.textContent = formatVolume(sumVol);
            }

            // ---------------------------------------------------------
            // ОБРАБОТЧИКИ СМЕНЫ КЛАСТЕРОВ (пересчитываем все строки)
            // ---------------------------------------------------------
            clusterFromSelect.addEventListener('change', () => {
                document.querySelectorAll('#calcTable tbody tr').forEach(tr => calculateRow(tr));
            });
            clusterToSelect.addEventListener('change', () => {
                document.querySelectorAll('#calcTable tbody tr').forEach(tr => calculateRow(tr));
            });

            // Добавляем обработчик на кнопку "Добавить товар"
            addBtn.addEventListener('click', addRow);
            
            // Добавляем первую строку при загрузке страницы
            addRow();
        }
    </script>
    </body>
    <?php
}
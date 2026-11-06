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
 * JS-логика вынесена в js/cross_dok.js, стили — в css/cost_logistik.css.
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
    <!doctype html>
    <html lang="ru">
    <head>
        <meta charset="utf-8">
        <title>Расчёт стоимости логистики и кросс-докинга</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="css/cost_logistik.css">
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
        <h2 style="margin: 20px 0 15px;">Логистика</h2>
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
            <div style="font-size: 14px; color: #1f2a3a; margin-top: 10px;">Общий объем товаров: <span id="totalVolume">0,00</span> л</div>
        </div>

        <!-- =========================================================
             ТАБЛИЦА КРОСС-ДОКИНГА ПО МАРШРУТУ (from → to)
             ========================================================= -->
        <h2 style="margin: 20px 0 15px;">Кросс-докинг (по маршруту)</h2>

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
            <div style="font-size: 14px; color: #1f2a3a; margin-top: 10px;">Итого кросс-докинг (коробка): <span id="crossBoxTotal">0,00</span> ₽</div>
        </div>

        <!-- =========================================================
             ТАБЛИЦА ЛОГИСТИКИ ВНУТРИ КЛАСТЕРА ДОСТАВКИ (to → to)
             ========================================================= -->
        <h2 style="margin: 20px 0 15px;">Логистика внутри кластера доставки</h2>

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
            <div style="font-size: 14px; color: #1f2a3a; margin-top: 10px;">Общий объем (внутри кластера): <span id="localLogVolume">0,00</span> л</div>
        </div>

        <!-- =========================================================
             ИТОГОВАЯ СВОДКА (4-я таблица)
             ========================================================= -->
        <h2 style="margin: 20px 0 15px;">Итоговая сводка</h2>

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

    <!-- Данные о товарах для JS -->
    <script>
        window.__CROSS_DOK_PRODUCTS__ = <?= $productsJson ?>;
    </script>
    <!-- Логика страницы -->
    <script src="js/cross_dok.js"></script>
    </body>
    </html>
    <?php
}
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Оформление заказа — стиль Ozon</title>
<link rel="stylesheet" href="css/css_add_new_tovar.css" />
<script src="js/js_get_nomenclature.js"></script>
</head>
<body>
<div class="ozon-card">
    <div class="form-header">
        <h2>Добавление нового товара в БД</h2>
        <p>Заполните данные товара</p>
    </div>
    <div class="form-body">
        <form id="orderForm" action="add_new_tovar_in_db.php" method="post">
            <!-- ПЕРВЫЙ ЭЛЕМЕНТ: ВЫБОР МАГАЗИНА (как и требовалось) -->
            <div class="form-group">
                <label for="shopSelect">
                    <span class="section-icon">🏬</span> Магазин
                    <span class="required-star">*</span>
                </label>
                <select id="shopSelect" name="shop" required>
                    <option value="" disabled selected>Выберите магазин</option>
                    <option value="ozon_anmaks">OZON Anmaks</option>
                    <option value="ozon_ip_zel">OZON IPZel</option>
                    <option value="wb_anmaks">WB Anmaks</option>
                    <option value="wb_ip_zel">WB IPZel</option>
                    <!-- <option value="ya_anmaks_fbs">YM Anmaks</option> -->
                    
            </select>
            </div>

            <!-- НЕСКОЛЬКО ОКОН ДЛЯ ВВОДА ИНФОРМАЦИИ ВРУЧНУЮ -->
             <!-- Артикулы -->
            <div class="form-row">
                <div class="form-group">
                    <label>
                         Артикул на данном Маркете
                        <span class="required-star">*</span>
                    </label>
                    <input type="text" id="mp_article" name="mp_article" placeholder="6211" required autocomplete="off">
                </div>
            </div>
             <!-- СКУ Баркоды -->
            <div class="form-row">
                <div class="form-group">
                    <label">
                         SKU
                        <span class="required-star">*</span>
                    </label>
                    <input type="numbers" id="sku" name="sku" placeholder="6215465211" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>
                         Barcode
                        <span class="required-star">*</span>
                    </label>
                    <input type="numbers" id="barcode" name="barcode" placeholder="6215465211" required autocomplete="off">
                </div>
                
            </div>


            <div class="form-group">
                <label for="address">
                    <span class="section-icon">🏠</span> Название товара
                    <span class="required-star">*</span>
                </label>
                <input type="text" id="mp_name" name="mp_name" placeholder="Бордюр садовый ..." required autocomplete="off">
            </div>

            <button type="submit" class="submit-btn">Продолжить оформление</button>
        </form>
    </div>
</div>

</body>
</html>
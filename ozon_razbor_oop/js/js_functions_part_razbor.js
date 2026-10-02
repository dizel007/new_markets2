function alerting() {
    // 1. Проверка номера заказа
    var numberOrderInput = document.querySelector('input[name="number_order"]');
    if (!numberOrderInput || numberOrderInput.value.trim() === '') {
        alert('Пожалуйста, введите номер заказа');
        return false;
    }

    // 2. Проверка выбранных товаров и количества > 0
    var checkboxes = document.querySelectorAll('.row-checkbox');
    var anyChecked = false;
    var hasInvalidQty = false;

    checkboxes.forEach(function(cb) {
        if (cb.checked) {
            anyChecked = true;
            var row = cb.closest('tr');
            if (row) {
                var qtyInput = row.querySelector('.new-qty-input');
                if (qtyInput) {
                    var qtyVal = parseInt(qtyInput.value, 10);
                    if (isNaN(qtyVal) || qtyVal <= 0) {
                        hasInvalidQty = true;
                        qtyInput.classList.add('invalid');
                    } else {
                        qtyInput.classList.remove('invalid');
                    }
                } else {
                    hasInvalidQty = true;
                }
            }
        }
    });

    if (!anyChecked) {
        alert('Пожалуйста, выберите хотя бы один товар для сборки.');
        return false;
    }

    if (hasInvalidQty) {
        alert('Для выбранных товаров необходимо указать новое количество больше 0.');
        return false;
    }

    // 3. Всё ок – скрываем блоки и показываем ожидание
    var lock_1 = document.getElementById('up_input');
    if (lock_1) lock_1.className = 'LockOn';
    var lock_2 = document.getElementById('down_input');
    if (lock_2) lock_2.className = 'LockOn';
    var see_text = document.getElementById('OnLock_textLockPane');
    if (see_text) see_text.className = 'LockOff';

    return true; // форма отправится
}

    (function() {
        // Получаем элементы
        const form = document.getElementById('productsForm');
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        const rows = document.querySelectorAll('tbody tr');
        
        // Функция обновления состояния "Выбрать все"
        function updateSelectAll() {
            const allCheckboxes = Array.from(document.querySelectorAll('.row-checkbox'));
            const allChecked = allCheckboxes.every(cb => cb.checked);
            const someChecked = allCheckboxes.some(cb => cb.checked);
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && someChecked;
            }
        }

        // Установить состояние для всех чекбоксов строк
        function setAllCheckboxes(checked) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = checked);
            updateSelectAll();
        }
        
        // Клиентская валидация поля нового количества: не больше фактического
        function validateNewQtyInput(input, maxQty, rowElement) {
            let value = parseInt(input.value, 10);
            const isValid = (!isNaN(value) && value >= 0 && value <= maxQty) || (input.value === '');
            if (!isValid && input.value !== '') {
                input.classList.add('invalid');
                if (rowElement) {
                    let errSpan = rowElement.querySelector('.inline-error');
                    if (!errSpan) {
                        errSpan = document.createElement('span');
                        errSpan.className = 'inline-error';
                        errSpan.style.fontSize = '0.7rem';
                        errSpan.style.color = '#dc3545';
                        errSpan.style.marginLeft = '8px';
                        errSpan.style.display = 'inline-block';
                        input.parentNode.appendChild(errSpan);
                    }
                    errSpan.textContent = ` ≤ ${maxQty}`;
                }
                return false;
            } else {
                input.classList.remove('invalid');
                if (rowElement) {
                    const errSpan = rowElement.querySelector('.inline-error');
                    if (errSpan) errSpan.remove();
                }
                return true;
            }
        }
        
        // Привязываем валидацию ко всем полям ввода
        function bindValidation() {
            rows.forEach(row => {
                const qtyInput = row.querySelector('.new-qty-input');
                const maxQty = parseInt(row.getAttribute('data-max-qty'), 10);
                if (qtyInput) {
                    // События ввода и изменения
                    qtyInput.addEventListener('input', function() {
                        validateNewQtyInput(this, maxQty, row);
                    });
                    qtyInput.addEventListener('blur', function() {
                        validateNewQtyInput(this, maxQty, row);
                        // Доп. авто-коррекция, если больше максимума
                        let val = parseInt(this.value, 10);
                        if (!isNaN(val) && val > maxQty) {
                            this.value = maxQty;
                            validateNewQtyInput(this, maxQty, row);
                        } else if (!isNaN(val) && val < 0) {
                            this.value = 0;
                            validateNewQtyInput(this, maxQty, row);
                        }
                    });
                }
            });
        }
        
        
        // Перед отправкой формы - серверная валидация дублируется, но клиентская поможет избежать ошибок отправки
        form.addEventListener('submit', function(e) {
            let hasInvalid = false;
            rows.forEach(row => {
                const cb = row.querySelector('.row-checkbox');
                if (cb && cb.checked) {
                    const qtyInput = row.querySelector('.new-qty-input');
                    const maxQty = parseInt(row.getAttribute('data-max-qty'), 10);
                    if (qtyInput) {
                        let val = qtyInput.value.trim();
                        if (val !== '') {
                            let num = parseInt(val, 10);
                            if (isNaN(num) || num < 0 || num > maxQty) {
                                hasInvalid = true;
                                validateNewQtyInput(qtyInput, maxQty, row);
                            }
                        }
                    }
                }
            });
            if (hasInvalid) {
                e.preventDefault();
                alert('Пожалуйста, исправьте некорректные значения в полях "Новое количество" (должны быть числа от 0 до фактического количества).');
            }
        });
        
        // Обработчик "Выбрать все"
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                setAllCheckboxes(this.checked);
            });
        }
        
            
            
        // Обновление состояния selectAll при изменении любого чекбокса
        const allRowCheckboxes = document.querySelectorAll('.row-checkbox');
        allRowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectAll);
        });
        
        // Инициализация валидации и синхронизация
        bindValidation();
        updateSelectAll();

    })();

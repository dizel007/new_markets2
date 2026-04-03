
    document.addEventListener('DOMContentLoaded', function() {
        const shopSelect = document.getElementById('shopSelect');
        const formBody = document.querySelector('.form-body');
        // shopSelect = 'ozon_anmaks';
        // Создадим контейнер для динамического списка (если его ещё нет)
        let selectContainer = null;
          
        shopSelect.addEventListener('change', function() {
            const selectedShop = this.value;
            console.log(selectedShop);
            if (!selectedShop) return;
            
            // Удаляем предыдущий динамический список, если был
            if (selectContainer) selectContainer.remove();
            
            // Создаём новый блок для выбора существующего товара
            selectContainer = document.createElement('div');
            selectContainer.className = 'form-group';
            selectContainer.innerHTML = `
                <label for="existingArticleSelect">
                    <span class="section-icon">📦</span> Выберите существующий товар
                </label>
                <select id="existingArticleSelect" name="existing_article">
                    <option value="">-- Загрузка артикулов... --</option>
                </select>
            `;
            // Вставляем перед первой группой полей (или куда удобнее)
            const firstGroup = document.querySelector('.form-group');
            firstGroup.parentNode.insertBefore(selectContainer, firstGroup.nextSibling);
            
            const dynamicSelect = document.getElementById('existingArticleSelect');
         
            // Отправляем запрос к PHP
            fetch(`get_articles.php?`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        dynamicSelect.innerHTML = `<option value="">Ошибка: ${data.error}</option>`;
                        return;
                    }
                    if (data.length === 0) {
                        dynamicSelect.innerHTML = `<option value="">Нет товаров для этого магазина</option>`;
                        return;
                    }
                    // Формируем опции: показываем mp_name + артикул, сохраняем все данные в data-атрибутах
                    let options = '<option value="">-- Выберите товар --</option>';
                    data.forEach(product => {
                        options += `<option value="${product.main_article_1c}">                            
                        ${product.main_article_1c}</option>`;
                    });
                    dynamicSelect.innerHTML = options;
                })
                .catch(error => {
                    console.error('Ошибка AJAX:', error);
                    dynamicSelect.innerHTML = `<option value="">Ошибка загрузки данных</option>`;
                });
        });
        
        // Делегируем событие изменения для динамически созданного select
        formBody.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'existingArticleSelect') {
                const selectedOption = e.target.options[e.target.selectedIndex];
                if (selectedOption.value === '') {
                    // Если выбрана пустая опция, ничего не делаем
                    return;
                }
                // Заполняем поля формы данными из data-атрибутов
                document.getElementById('main_article').value = selectedOption.dataset.main_article || '';
                document.getElementById('mp_article').value = selectedOption.dataset.mp_article || '';
                document.getElementById('sku').value = selectedOption.dataset.sku || '';
                document.getElementById('barcode').value = selectedOption.dataset.barcode || '';
                document.getElementById('mp_name').value = selectedOption.dataset.mp_name || '';
            }
        });
    });

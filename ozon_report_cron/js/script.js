document.getElementById('dateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const startDate = new Date(document.getElementById('startDate').value);
    const endDate = new Date(document.getElementById('endDate').value);
    const ozon_shop = document.getElementById('ozon_shop').value;

    if (startDate && endDate) {
        // Проверка на неправильный порядок дат
        if (startDate > endDate) {
            alert('Конечная дата не может быть раньше начальной!');
            return;
        }
        
        // Вычисляем разницу в днях (без учета времени)
        const diffMs = endDate - startDate;
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        const diffDaysInclusive = diffDays + 1; // Количество дней включительно

        // Ozon API строго ограничивает период: максимум 1 месяц.
        // Безопасный лимит: разница не более 30 дней (31 день включительно).
        const maxDaysDiff = 30; 

        if (diffDays > maxDaysDiff) {
            alert(`Ошибка: Максимальный период запроса в Ozon — 1 месяц (не более 31 дня включительно).\nВыбранный период: ${diffDaysInclusive} дней.`);
            return;
        }

        // Форматируем даты в YYYY-MM-DD
        const start_date = startDate.toISOString().split('T')[0]; 
        const end_date   = endDate.toISOString().split('T')[0]; 

        const params = new URLSearchParams({
            dateFrom: start_date,
            dateTo: end_date,
            ozon_shop: ozon_shop
        });

        window.location.href = `?${params.toString()}`;
    }
});
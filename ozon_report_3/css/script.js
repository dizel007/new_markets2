
document.getElementById('dateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const startDate = new Date(document.getElementById('startDate').value);
    const endDate = new Date(document.getElementById('endDate').value);
    const ozon_shop = document.getElementById('ozon_shop').value;

    if (startDate && endDate) {
        if (startDate > endDate) {
            alert('Конечная дата не может быть раньше начальной!');
            return;
        }
        
        // Вычисляем разницу
        const diffMs = endDate - startDate;
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        
        let message = `Период может быть не более 31 дня. Выбранный период: ${diffDays} дней`;
    
        if (diffDays > 31) {
            alert(message);
            return;
         }
       
         start_date = startDate.toISOString().split('T')[0]; 
         end_date = endDate.toISOString().split('T')[0]; 

        const params = new URLSearchParams({
            dateFrom: start_date,
            dateTo: end_date,
            ozon_shop: ozon_shop
        });

       window.location.href = `?${params.toString()}`;

    }
});
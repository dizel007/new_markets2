function alerting() {

    // 3. Всё ок – скрываем блоки и показываем ожидание
    var lock_1 = document.getElementById('up_input');
    if (lock_1) lock_1.className = 'LockOn';
    var lock_2 = document.getElementById('down_input');
    if (lock_2) lock_2.className = 'LockOn';
    var see_text = document.getElementById('OnLock_textLockPane');
    if (see_text) see_text.className = 'LockOff';

    return true; // форма отправится
}
<?php
require_once "../../vendor/autoload.php";

session_start();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];
?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<title>Поиск тарифа по Excel</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<h1>Поиск тарифа по Excel-файлу</h1>
<p class="muted">Загрузите xlsx с тарифами Ozon, затем укажите параметры для расчёта.</p>

<div id="drop">
  Перетащите xlsx-файл сюда или нажмите для выбора
  <input type="file" id="input" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" hidden>
</div>


<div id="status"></div>
<div id="result"></div>

<script>const CSRF = <?= json_encode($csrf) ?>;</script>
<script>
const drop = document.getElementById('drop');
const input = document.getElementById('input');
const statusEl = document.getElementById('status');

drop.addEventListener('click', () => input.click());
drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('hover'); });
drop.addEventListener('dragleave', () => drop.classList.remove('hover'));
drop.addEventListener('drop', e => {
  e.preventDefault();
  drop.classList.remove('hover');
  handleFile(e.dataTransfer.files[0]);
});
input.addEventListener('change', () => { handleFile(input.files[0]); input.value = ''; });

async function handleFile(file) {
  if (!file) return;
  const fd = new FormData();
  fd.append('file', file);

  statusEl.textContent = 'Загрузка…';
  try {
    const resp = await fetch('parse-xlsx.php', { method: 'POST', body: fd });
    const data = await resp.json();
    if (!data.ok) throw new Error(data.error || 'Ошибка разбора');
    statusEl.innerHTML = `<span class="ok">Файл разобран, строк: ${data.rows_count}</span>`;
  } catch (err) {
    statusEl.innerHTML = `<span class="err">${err.message}</span>`;
  }
}
</script>
</body>
</html>
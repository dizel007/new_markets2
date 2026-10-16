<?php
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
<title>Обрезка последней страницы PDF</title>
<style>
  * { box-sizing: border-box; }
  body {
    font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
    max-width: 820px;
    margin: 40px auto;
    padding: 0 16px 80px;
    color: #222;
  }
  h1 { font-size: 22px; margin: 0 0 8px; }
  .muted { color: #666; font-size: 14px; margin: 0 0 20px; }

  #drop {
    border: 2px dashed #9aa;
    border-radius: 12px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: .15s;
    color: #556;
    background: #fafbfc;
    user-select: none;
  }
  #drop.hover { border-color: #2a7; background: #f0fff7; color: #185; }

  #list { margin: 16px 0; max-height: 380px; overflow-y: auto; }
  .file {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 8px 10px;
    border-bottom: 1px solid #eef0f2;
    font-size: 14px;
  }
  .file:last-child { border-bottom: none; }
  .file .name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .file .right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
  .file .st { font-weight: 600; font-size: 13px; }
  .ok  { color: #1a8; }
  .err { color: #c33; }

  .rm {
    background: transparent;
    border: none;
    color: #c33;
    font-size: 18px;
    cursor: pointer;
    line-height: 1;
    padding: 0 4px;
  }
  .rm:hover { color: #900; }

  .actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 16px;
  }
  button.main {
    padding: 10px 22px;
    font-size: 16px;
    cursor: pointer;
    border: 1px solid #2a7;
    background: #2a7;
    color: #fff;
    border-radius: 8px;
  }
  button.main:disabled { opacity: .5; cursor: not-allowed; }
  button.danger {
    padding: 10px 16px;
    font-size: 15px;
    cursor: pointer;
    border: 1px solid #c33;
    background: #fff;
    color: #c33;
    border-radius: 8px;
  }
  button.danger:disabled { opacity: .5; cursor: not-allowed; }
  button.danger:hover:not(:disabled) { background: #fdf1f1; }

  #status { font-size: 14px; color: #333; }
  #status a { color: #2a7; font-weight: 600; }
  progress {
    display: block;
    width: 100%;
    height: 16px;
    margin-top: 14px;
  }
</style>
</head>
<body>

<h1>Обрезка последней страницы PDF</h1>
<p class="muted">До 100 файлов за раз. Результат — один ZIP-архив с обрезанными PDF.</p>

<div id="drop">
  Перетащите сюда PDF-файлы или нажмите для выбора
  <input type="file" id="input" accept="application/pdf,.pdf" multiple hidden>
</div>

<div id="list"></div>

<div class="actions">
  <button id="send" class="main" disabled>Обработать</button>
  <button id="clearTmp" class="danger" type="button" title="Удалить все файлы из папки tmp/">🗑 Очистить tmp</button>
  <span id="status"></span>
</div>

<progress id="prog" value="0" max="100" hidden></progress>

<script>const CSRF = <?= json_encode($csrf) ?>;</script>
<script>
const MAX_FILES = 100;

const drop     = document.getElementById('drop');
const input    = document.getElementById('input');
const list     = document.getElementById('list');
const send     = document.getElementById('send');
const clearBtn = document.getElementById('clearTmp');
const statusEl = document.getElementById('status');
const prog     = document.getElementById('prog');

let files = [];

/* ---------- выбор файлов ---------- */

drop.addEventListener('click', () => input.click());
drop.addEventListener('dragover', e => {
  e.preventDefault();
  drop.classList.add('hover');
});
drop.addEventListener('dragleave', () => drop.classList.remove('hover'));
drop.addEventListener('drop', e => {
  e.preventDefault();
  drop.classList.remove('hover');
  addFiles([...e.dataTransfer.files]);
});
input.addEventListener('change', () => {
  addFiles([...input.files]);
  input.value = '';
});

function addFiles(newFiles) {
  const pdfs = newFiles.filter(f =>
    f.type === 'application/pdf' || /\.pdf$/i.test(f.name)
  );

  files = files.concat(pdfs);

  if (files.length > MAX_FILES) {
    files = files.slice(0, MAX_FILES);
    statusEl.innerHTML = `<span class="err">Достигнут лимит ${MAX_FILES} файлов</span>`;
  } else {
    statusEl.textContent = '';
  }

  render();
}

/* ---------- отрисовка списка ---------- */

function render() {
  list.innerHTML = '';

  files.forEach((f, i) => {
    const row = document.createElement('div');
    row.className = 'file';
    row.innerHTML =
      `<span class="name">${i + 1}. ${escapeHtml(f.name)}</span>` +
      `<span class="right">` +
        `<span class="muted">${(f.size / 1024 / 1024).toFixed(2)} МБ</span>` +
        `<button class="rm" data-i="${i}" title="Убрать">×</button>` +
      `</span>`;
    row.querySelector('.rm').addEventListener('click', () => {
      files.splice(i, 1);
      render();
    });
    list.appendChild(row);
  });

  send.disabled = files.length === 0;

  if (files.length) {
    statusEl.textContent = `Выбрано: ${files.length}`;
  } else if (!statusEl.querySelector('.err')) {
    statusEl.textContent = '';
  }
}

function escapeHtml(s) {
  return s.replace(/[&<>"']/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[c]));
}

/* ---------- обработка ---------- */

send.addEventListener('click', async () => {
  if (!files.length) return;

  send.disabled = true;
  clearBtn.disabled = true;
  prog.hidden = false;
  prog.value = 0;
  statusEl.textContent = 'Загрузка и обработка…';

  const fd = new FormData();
  files.forEach(f => fd.append('files[]', f, f.name));

  try {
    const xhr = await new Promise((resolve, reject) => {
      const x = new XMLHttpRequest();
      x.open('POST', 'upload.php');
      x.upload.onprogress = e => {
        if (e.lengthComputable) prog.value = (e.loaded / e.total) * 100;
      };
      x.onload  = () => resolve(x);
      x.onerror = () => reject(new Error('Ошибка сети'));
      x.send(fd);
    });

    let data;
    try {
      data = JSON.parse(xhr.responseText);
    } catch (e) {
      throw new Error('Некорректный ответ сервера');
    }

    if (!data.ok) throw new Error(data.error || 'Ошибка сервера');

    statusEl.innerHTML =
      `Готово: <b>${data.success}</b> из <b>${data.total}</b>. ` +
      `<a href="${data.zipUrl}">Скачать ZIP</a>`;

    list.innerHTML = '';
    data.results.forEach(r => {
      const row = document.createElement('div');
      row.className = 'file';
      row.innerHTML =
        `<span class="name">${escapeHtml(r.name)}</span>` +
        `<span class="st ${r.ok ? 'ok' : 'err'}">${r.ok ? 'OK' : escapeHtml(r.error || 'ошибка')}</span>`;
      list.appendChild(row);
    });

    // Автоскачивание
    window.location = data.zipUrl;

  } catch (err) {
    statusEl.innerHTML = `<span class="err">${escapeHtml(err.message)}</span>`;
  } finally {
    send.disabled = false;
    clearBtn.disabled = false;
    prog.hidden = true;
  }
});

/* ---------- очистка tmp ---------- */

clearBtn.addEventListener('click', async () => {
  if (!confirm('Удалить ВСЕ файлы из папки tmp/ без возможности восстановления?')) return;

  clearBtn.disabled = true;
  const oldText = clearBtn.textContent;
  clearBtn.textContent = 'Очистка…';
  statusEl.textContent = '';

  try {
    const fd = new FormData();
    fd.append('csrf', CSRF);

    const resp = await fetch('clear-tmp.php', { method: 'POST', body: fd });
    const data = await resp.json();

    if (!data.ok) throw new Error(data.error || 'Ошибка сервера');

    statusEl.innerHTML =
      `<span class="ok">Очищено: ${data.deleted}` +
      (data.failed ? `, не удалось: ${data.failed}` : '') + `</span>`;

    // Сбрасываем выбранные файлы и прогресс
    files = [];
    render();
    prog.hidden = true;

  } catch (err) {
    statusEl.innerHTML = `<span class="err">${escapeHtml(err.message)}</span>`;
  } finally {
    clearBtn.disabled = false;
    clearBtn.textContent = oldText;
  }
});
</script>
</body>
</html>
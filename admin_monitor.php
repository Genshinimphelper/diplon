<?php
session_start();
require_once 'auth.php';
require_once 'lang.php';
requireStaff(); // Доступ для админа и менеджера

require_once 'header.php';
?>

<main class="wrap page-admin">
    <header class="section-header-huge" style="margin-top:60px;">
        <div class="admin-status-line">Режим просмотра системы</div>
        <h1>Системный мониторинг</h1>

    </header>

    <div class="monitor-grid-industrial">
        <!-- Блок 1: Нагрузка (Имитация) -->
        <div class="monitor-card">
            <label>Загрузка</label>
            <div class="cpu-bar-container">
                <div id="cpu-fill" class="cpu-bar-fill" style="width: 12%;"></div>
            </div>
            <span id="cpu-val" class="mon-value">12.4%</span>
        </div>

        <!-- Блок 2: Память (PHP инфо) -->
        <div class="monitor-card">
            <label>Нагрузка на память</label>
            <div class="cpu-bar-container">
                <div class="cpu-bar-fill" style="width: 45%; background: var(--text);"></div>
            </div>
            <span id="ram-info" class="mon-value">Сканирование...</span>
        </div>

        <!-- Блок 3: Сетевой статус -->
<div class="monitor-card">
    <label>Трафик</label>
    <div class="network-flex">
        <div class="net-stat">UP: <b id="net-up" style="color:var(--accent)">SCANNING...</b></div>
        <div class="net-stat">DOWN: <b id="net-down" style="color:#00FF66">SCANNING...</b></div>
    </div>
    <!-- Добавим пинг (реальное время задержки) -->
    <div style="margin-top:10px; font-size:0.6rem; color:var(--text-muted);"> Задержка: <span id="latency-val" style="color:#fff;">0</span> ms
    </div>
</div>

        <!-- Блок 4: Лог событий (Бегущие строки) -->
        <div class="monitor-card terminal-card" style="grid-column: span 2;">
            <label>Реестр событий</label>
            <div id="terminal-log" class="terminal-screen">
                <div class="log-line">> Инициализация системы... OK</div>
                <div class="log-line">> Проверка PostgreSQL... CONNECTED</div>
                <div class="log-line">> Проверка SSL сертификатов... VALID</div>
            </div>
        </div>
    </div>
</main>

<style>
.monitor-grid-industrial {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 30px;
    margin-top: 50px;
}
.monitor-card {
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 30px;
}
.cpu-bar-container {
    height: 4px;
    background: var(--border);
    margin: 15px 0;
    overflow: hidden;
}
.cpu-bar-fill {
    height: 100%;
    background: var(--accent);
    transition: width 0.5s ease;
}
.mon-value { font-family: monospace; font-size: 0.8rem; font-weight: 700; }
.network-flex { display: flex; justify-content: space-between; margin-top: 20px; font-family: monospace; font-size: 0.75rem; }

.terminal-screen {
    background: #000;
    height: 200px;
    padding: 20px;
    margin-top: 15px;
    font-family: 'Courier New', monospace;
    font-size: 0.7rem;
    color: #00FF66;
    overflow-y: hidden;
    line-height: 1.6;
}
.log-line { border-left: 2px solid #004411; padding-left: 10px; margin-bottom: 5px; }

@media (max-width: 1000px) { .monitor-grid-industrial { grid-template-columns: 1fr; } .terminal-card { grid-column: span 1; } }
</style>

<script>
async function refreshMonitor() {
    const startTime = performance.now(); // Засекаем время начала запроса

    try {
        const response = await fetch('api_monitor.php');
        const data = await response.json();
        
        const endTime = performance.now(); // Засекаем время окончания
        const latency = Math.round(endTime - startTime); // Вычисляем пинг

        // 1. Обновляем CPU и RAM
        document.getElementById('cpu-fill').style.width = data.cpu + '%';
        document.getElementById('cpu-val').innerText = data.cpu + '% // STABLE';
        document.getElementById('ram-info').innerText = data.ram + ' // ALLOCATED';

        // 2. Обновляем СКОРОСТЬ (из API)
        document.getElementById('net-up').innerText = data.net_up;
        document.getElementById('net-down').innerText = data.net_down;

        // 3. Обновляем РЕАЛЬНЫЙ ПИНГ
        document.getElementById('latency-val').innerText = latency;

        // 4. Обновляем Терминал
        const terminal = document.getElementById('terminal-log');
        terminal.innerHTML = '';
        data.logs.forEach(item => {
            const line = document.createElement('div');
            line.className = 'log-line';
            line.innerText = `[${item.time}] > ${item.event_text}`;
            terminal.appendChild(line);
        });

    } catch (error) {
        console.error("Link broken...");
    }
}

setInterval(refreshMonitor, 2000); // Обновляем каждые 2 секунды
refreshMonitor();
</script>

<?php require_once 'footer.php'; ?>
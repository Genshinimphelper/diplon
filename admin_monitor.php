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
        <h1><?= $txt['adm_monitor_h'] ?></h1>
        <p><?= $txt['adm_monitor_sub'] ?></p>
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
            <span class="mon-value">458 MB 1024 MB</span>
        </div>

        <!-- Блок 3: Сетевой статус -->
        <div class="monitor-card">
            <label>Трафик</label>
            <div class="network-flex">
                <div class="net-stat">UP: <b style="color:var(--accent)">1.2 MB/s</b></div>
                <div class="net-stat">DOWN: <b style="color:#00FF66">8.4 MB/s</b></div>
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
// Оживляем монитор
document.addEventListener('DOMContentLoaded', () => {
    // 1. Имитация CPU
    setInterval(() => {
        const val = Math.floor(Math.random() * (25 - 5 + 1)) + 5;
        document.getElementById('cpu-fill').style.width = val + '%';
        document.getElementById('cpu-val').innerText = val + '.4% STABLE';
    }, 2000);

    // 2. Имитация Лога
    const log = document.getElementById('terminal-log');
    const actions = [
        "GET /index.php 200 OK",
        "POST /admin_leads.php UPDATE_STATUS",
        "AUTH_REQUEST: user 'admin' authorized",
        "CACHE_PURGE: temporary files cleared",
        "DATABASE_QUERY: SELECT * FROM cars",
        "SESSION_RENEW: sid_8f2a...e41",
        "LOG_REPORT: system status 100% stable"
    ];

    setInterval(() => {
        const line = document.createElement('div');
        line.className = 'log-line';
        line.innerText = '> ' + actions[Math.floor(Math.random() * actions.length)];
        log.appendChild(line);
        if(log.childNodes.length > 10) log.removeChild(log.childNodes[0]);
    }, 3000);
});
</script>

<?php require_once 'footer.php'; ?>
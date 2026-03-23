<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';
requireStaff();
// Доступ разрешен и админу, и менеджеру
requireLogin();
if (!isAdmin() && $_SESSION['user']['role'] !== 'manager') {
    header("Location: index.php");
    exit;
}

require_once 'header.php';
?>

<main class="wrap page-admin-hub">
    <header class="section-header-huge">
        <div class="admin-status-line">
            Системная роль: <?= strtoupper($_SESSION['user']['role']) ?> запущено!
        </div>
        <h1> <span></span></h1>
        <p>Интерфейс управления базой данных, клиентскими запросами и модерацией контента.</p>
    </header>

    <div class="admin-hub-grid">
        <!-- 01. ЗВОНКИ -->
        <a href="admin_leads.php?type=callback" class="hub-card">
            <div class="hub-header">
                <span class="hub-num">01</span>
                <span class="hub-icon">📞</span>
            </div>
            <div class="hub-body">
                <h3>Звонки и заяки</h3>
                <p>Запросы на обратный звонок по объектам из каталога</p>
            </div>
            <div class="hub-footer">Открыть →</div>
        </a>

        <!-- 02. КРЕДИТЫ -->
        <a href="admin_leads.php?type=credit" class="hub-card">
            <div class="hub-header">
                <span class="hub-num">02</span>
                <span class="hub-icon">💳</span>
            </div>
            <div class="hub-body">
                <h3>Финансы</h3>
                <p>Кредитные анкеты и статусы банковских одобрений</p>
            </div>
            <div class="hub-footer">Открыть →</div>
        </a>

        <!-- 03. ТРЕЙД-ИН -->
        <a href="admin_leads.php?type=evaluate" class="hub-card">
            <div class="hub-header">
                <span class="hub-num">03</span>
                <span class="hub-icon">⚖️</span>
            </div>
            <div class="hub-body">
                <h3>Trade-In</h3>
                <p>Оценка входящего парка автомобилей от клиентов</p>
            </div>
            <div class="hub-footer">Открыть →</div>
        </a>

        <!-- 04. ТЕСТ-ДРАЙВЫ -->
        <a href="admin_leads.php?type=testdrive" class="hub-card">
            <div class="hub-header">
                <span class="hub-num">04</span>
                <span class="hub-icon">🏎️</span>
            </div>
            <div class="hub-body">
                <h3>Test Drives</h3>
                <p>Управление графиком заездов и бронированием времени</p>
            </div>
            <div class="hub-footer">Открыть →</div>
        </a>

        <!-- 05. МОДЕРАЦИЯ ОТЗЫВОВ -->
        <a href="admin_reviews.php" class="hub-card">
            <div class="hub-header">
                <span class="hub-num">05</span>
                <span class="hub-icon">💬</span>
            </div>
            <div class="hub-body">
                <h3>Отзывы</h3>
                <p>Проверка и публикация отзывов клиентов</p>
            </div>
            <div class="hub-footer">Открыть страницу модерации →</div>
        </a>
        
        <a href="admin_inventory.php" class="hub-card">
    <div class="hub-header">
        <span class="hub-num">05</span>
        <span class="hub-icon">📦</span>
    </div>
    <div class="hub-body">
        <h3>Машины</h3>
        <p>Редактирование параметров и безвозвратное удаление объектов</p>
    </div>
    <div class="hub-footer">проверить каталог →</div>
</a>
        <!-- 06. СКЛАД (Только для ADMIN) -->
        <?php if (isAdmin()): ?>
        <a href="admin_add_car.php" class="hub-card highlight">
            <div class="hub-header">
                <span class="hub-num">06</span>
                <span class="hub-icon">➕</span>
            </div>
            <div class="hub-body">
                <h3>Регистрация новых Авто</h3>
                <p>Регистрация нового автомобиля в реестре стока</p>
            </div>
            <div class="hub-footer">Открыть базу данных→</div>
        </a>

<a href="admin_users.php" class="hub-card">
    <div class="hub-header">
        <span class="hub-num">06</span>
        <span class="hub-icon">👥</span>
    </div>
    <div class="hub-body">
        <h3>Пользователи</h3>
        <p>Контроль уровней доступа, назначение ролей и блокировка аккаунтов</p>
    </div>
    <div class="hub-footer">Открыть меню ползователей</div>
</a>
        <?php else: ?>
        <div class="hub-card placeholder">
            <div class="hub-body">
                <p> RESTRICTED AREA<br>ADMIN PRIVILEGES REQUIRED</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'footer.php'; ?>
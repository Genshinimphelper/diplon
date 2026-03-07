<?php
// 1. Инициализация системных файлов
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';

// 2. СИНХРОНИЗАЦИЯ СЕССИИ С БАЗОЙ ДАННЫХ (Для мгновенной смены ролей и блокировки)
if (isset($_SESSION['user'])) {
    $uid = (int)$_SESSION['user']['id'];
    $check_user = pg_query_params($conn, "SELECT role, is_blocked, avatar FROM users WHERE id = $1", [$uid]);
    $actual_data = pg_fetch_assoc($check_user);
    
    if ($actual_data) {
        // Если администратор заблокировал пользователя "на лету"
        if ($actual_data['is_blocked'] === 't') {
            session_destroy();
            header("Location: login.php?error=blocked");
            exit;
        }
        // Обновляем роль и аватар в текущей сессии без перезахода
        $_SESSION['user']['role'] = $actual_data['role'];
        $_SESSION['user']['avatar'] = $actual_data['avatar'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $ln ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AUTOMARKET</title>
    
    <!-- Фавиконка -->
    <link rel="icon" type="image/png" href="images/logo.png">

    <!-- Шрифты Inter (Промышленный стандарт) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    
    <!-- Основной файл стилей -->
    <link rel="stylesheet" href="style.css">
    
    <!-- СКРИПТ ПРЕДЗАГРУЗКИ ТЕМЫ (Предотвращает белую вспышку) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light' || savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', savedTheme);
            }
        })();
    </script>
</head>
<body class="theme-transition">

<header class="site-header">
    <div class="wrap header-inner">
        
        <!-- 1. ГРУППА ЛОГОТИПА -->
        <a href="index.php" class="main-logo-group">
            <div class="logo-img-box">
                <img src="images/logo.png" alt="Logo" class="logo-pic">
            </div>
            <div class="logo-text-box">
                AUTO<span>MARKET</span>
            </div>
        </a>

        <!-- 2. ЦЕНТРАЛЬНАЯ НАВИГАЦИЯ -->
        <nav class="main-nav">
            <a href="catalog.php" class="nav-link"><?= $txt['nav_catalog'] ?></a>
            <a href="evaluate.php" class="nav-link"><?= $txt['nav_tradein'] ?></a>
            <a href="reviews.php" class="nav-link"><?= $txt['nav_reviews'] ?></a>
            <a href="compare.php" class="nav-link nav-compare-wrapper">
                <?= $txt['nav_compare'] ?>
                <span id="compare-count-badge" class="count-badge-mini">
                    <?= isset($_SESSION['compare']) ? count($_SESSION['compare']) : 0 ?>
                </span>
            </a>
        </nav>

        <!-- 3. ПРАВАЯ ЧАСТЬ: ИНСТРУМЕНТЫ И АККАУНТ -->
        <div class="header-utilities">
            
            <div class="controls-bundle">
                <!-- Переключатель языков -->
                <div class="lang-switch-mini">
                    <a href="?lang=ru" class="<?= $ln == 'ru' ? 'active' : '' ?>">RU</a>
                    <a href="?lang=en" class="<?= $ln == 'en' ? 'active' : '' ?>">EN</a>
                </div>
                
                <!-- Переключатель тем (3 состояния) -->
                <button id="theme-toggle" class="theme-btn-minimal" title="Switch Theme">🌓</button>
            </div>

            <div class="user-block-industrial">
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="user-logged-box">
                        <!-- Имя пользователя (ссылка в профиль) -->
                        <a href="profile.php" class="user-name-link">
                            <?php if (!empty($_SESSION['user']['avatar'])): ?>
                                <img src="avatars/<?= $_SESSION['user']['avatar'] ?>" class="nav-avatar-mini">
                            <?php endif; ?>
                            <?= htmlspecialchars($_SESSION['user']['login']) ?>
                        </a>
                        
                        <!-- Панель управления (видна Админу и Менеджеру) -->
                        <?php if (isStaff()): ?>
                            <a href="admin_panel.php" class="admin-btn-accent">DASHBOARD</a>
                        <?php endif; ?>

                        <!-- Иконка выхода -->
                        <a href="logout.php" class="logout-icon-small" title="<?= $txt['nav_exit'] ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Ссылки для гостей -->
                    <div class="guest-actions">
                        <a href="login.php" class="login-link-minimal"><?= $txt['nav_login'] ?></a>
                        <a href="register.php" class="register-btn-industrial"><?= $txt['nav_join'] ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</header>

<!-- Скрипт переключения тем -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const themeBtn = document.getElementById('theme-toggle');
    const htmlEl = document.documentElement;
    const themeCycle = [null, 'light', 'dark'];

    let currentTheme = localStorage.getItem('theme');
    let currentIndex = themeCycle.indexOf(currentTheme);
    if (currentIndex === -1) currentIndex = 0;

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % themeCycle.length;
            const nextTheme = themeCycle[currentIndex];

            if (nextTheme) {
                htmlEl.setAttribute('data-theme', nextTheme);
                localStorage.setItem('theme', nextTheme);
            } else {
                htmlEl.removeAttribute('data-theme');
                localStorage.removeItem('theme');
            }
        });
    }
});
</script>
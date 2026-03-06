<?php require_once 'lang.php'; ?>
<!DOCTYPE html>
<html lang="<?= $ln ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AUTOMARKET</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css"><!-- Иконка во вкладке браузера -->
    <link rel="icon" type="image/png" href="images/logo.png">
</head>
<script>
    // Мгновенная проверка темы до отрисовки контента
    (function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light' || savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', savedTheme);
        }
        // Если темы нет, браузер сам применит адаптивную (Auto) через CSS
    })();
</script>
<body class="theme-transition">

<header class="site-header">
    <div class="wrap header-inner">
        
        <!-- 1. ЛОГОТИП (Слева) -->
        <a href="index.php" class="main-logo-group">
            <div class="logo-img-box">
                <img src="images/logo.png" alt="Logo" class="logo-pic">
            </div>
            <div class="logo-text-box">
                AUTO<span>MARKET</span>
            </div>
        </a>

        <!-- 2. НАВИГАЦИЯ (Центр) -->
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

        <!-- 3. ИНСТРУМЕНТЫ И ПРОФИЛЬ (Справа) -->
        <div class="header-utilities">
            
            <div class="controls-bundle">
                <div class="lang-switch-mini">
                    <a href="?lang=ru" class="<?= $ln == 'ru' ? 'active' : '' ?>">RU</a>
                    <a href="?lang=en" class="<?= $ln == 'en' ? 'active' : '' ?>">EN</a>
                </div>
                
                <button id="theme-toggle" class="theme-btn-minimal">🌓</button>
            </div>

            <div class="user-block-industrial">
                <?php if (isset($_SESSION['user'])): ?>
                    <div class="user-logged-box">
                        <a href="profile.php" class="user-name-link">
                            <?= htmlspecialchars($_SESSION['user']['login']) ?>
                        </a>
                        
                        <?php if (isAdmin()): ?>
                            <a href="admin_panel.php" class="admin-btn-accent">DASHBOARD</a>
                        <?php endif; ?>

                        <a href="logout.php" class="logout-icon-small" title="Выход">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="login-link-minimal">ВХОД</a>
                    <a href="register.php" class="register-btn-industrial">JOIN</a>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</header>
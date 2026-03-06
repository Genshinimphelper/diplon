<footer class="site-footer">
    <div class="wrap">
        <div class="footer-main-grid">
            
            <!-- 1. БРЕНД И ОПИСАНИЕ -->
            <div class="footer-col-brand">
                <a href="index.php" class="logo">AUTO<span>MARKET</span></a>
                <p class="footer-description">
                    <?= $txt['hero_p'] ?>
                </p>
                <div class="footer-status-tag"></div>
            </div>

            <!-- 2. НАВИГАЦИЯ -->
            <div class="footer-col">
                <h4><?= $txt['nav_catalog'] ?></h4>
                <nav class="footer-nav-list">
                    <a href="catalog.php"><?= $txt['nav_catalog'] ?></a>
                    <a href="evaluate.php"><?= $txt['nav_tradein'] ?></a>
                    <a href="credit_apply.php"><?= $txt['calc_h_plain'] ?? 'Credit' ?></a>
                </nav>
            </div>

            <!-- 3. ГРАФИК И КОНТАКТЫ -->
            <div class="footer-col">
                <h4><?= $txt['f_schedule'] ?></h4>
                <p class="footer-info-text"><?= $txt['f_work_days'] ?></p>
                <p class="footer-info-text">Москва, Автомобильная 15</p>
                <p class="footer-info-text" style="color: var(--accent); font-weight: 800;">+7 900 123-45-67</p>
            </div>

            <!-- 4. СОЦСЕТИ И КНОПКА -->
            <div class="footer-col-actions">
                <h4><?= $txt['f_social'] ?></h4>
                <div class="footer-social-links">
                    <a href="#" class="social-box">TG</a>
                    <a href="#" class="social-box">VK</a>
                    <a href="#" class="social-box">YT</a>
                </div>
                <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="btn-to-top">
                    UP ↑
                </button>
            </div>

        </div>

        <!-- НИЖНЯЯ ПАНЕЛЬ -->
        <div class="footer-bottom-panel">
            <div class="footer-legal">
                <span>&copy; <?= date('Y') ?> AUTOMARKET</span>
                <a href="#"><?= $txt['f_privacy'] ?></a>
            </div>
            <p class="footer-disclaimer">
                <?= $txt['f_copy'] ?>
            </p>
        </div>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>
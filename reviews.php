<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';

$msg = '';

// --- 1. ЛОГИКА ОТПРАВКИ ОТЗЫВА (ПОЛЬЗОВАТЕЛЬ) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $car_info = trim($_POST['car_info'] ?? '');
    $text = trim($_POST['text'] ?? '');
    $rating = (int)($_POST['rating'] ?? 5);

    if (!empty($text) && !empty($car_info)) {
        $res = pg_query_params($conn, 
            "INSERT INTO reviews (user_id, car_info, text, rating, status) VALUES ($1, $2, $3, $4, 'pending')",
            [$user_id, $car_info, $text, $rating]
        );
        if ($res) {
            $msg = $txt['rev_success'];
        }
    }
}

// --- 2. ПОЛУЧЕНИЕ ОДОБРЕННЫХ ОТЗЫВОВ ДЛЯ ВЫВОДА ---
$rev_res = pg_query($conn, "
    SELECT r.*, u.login, u.avatar 
    FROM reviews r 
    JOIN users u ON u.id = r.user_id 
    WHERE r.status = 'approved' 
    ORDER BY r.created_at DESC
");
$reviews = pg_fetch_all($rev_res) ?: [];

require_once 'header.php';
?>

<main class="wrap page-reviews">
    <!-- ЗАГОЛОВОК СТРАНИЦЫ -->
    <header class="section-header-huge" style="margin-top: 60px;">
        <div class="admin-status-line"></div>
        <h1><?= $txt['rev_title'] ?></h1>
        <p><?= $txt['rev_subtitle'] ?></p>
    </header>

    <!-- ГРИД С ОТЗЫВАМИ -->
    <div class="reviews-grid-industrial">
        <?php if (empty($reviews)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px; border: 1px dashed var(--border); color: var(--text-muted);">
                Нет опубликованых отзывовы
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
                <div class="review-item" style="position: relative;">
                    
                    <!-- БЫСТРОЕ УДАЛЕНИЕ ДЛЯ АДМИНА -->
                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                        <a href="admin_reviews.php?action=delete&id=<?= $rev['id'] ?>&view=approved" 
                           class="admin-quick-delete"
                           onclick="return confirm('DELETE THIS REVIEW PERMANENTLY?')">
                           REMOVE
                        </a>
                    <?php endif; ?>

                    <div class="rev-header">
                        <div class="rev-user-meta">
                            <span class="rev-meta"><?= date('d.m.Y', strtotime($rev['created_at'])) ?> </span>
                            <span class="rev-author"><?= htmlspecialchars($rev['login']) ?></span>
                        </div>
                        <div class="stars-industrial"><?= str_repeat('★', $rev['rating']) ?></div>
                    </div>

                    <div class="rev-car-tag">OBJECT: <?= htmlspecialchars($rev['car_info']) ?></div>
                    
                    <p class="rev-content">
                        "<?= nl2br(htmlspecialchars($rev['text'])) ?>"
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ФОРМА НАПИСАНИЯ ОТЗЫВА -->
    <section class="write-review-section" style="margin-top: 120px; padding-bottom: 120px;">
        <?php if (isset($_SESSION['user'])): ?>
            
            <?php if ($msg): ?>
                <div class="success-banner" style="background: #00FF66; color: #000; padding: 30px; font-weight: 900; text-align: center; text-transform: uppercase;">
                    <?= $msg ?>
                </div>
            <?php else: ?>
                <div class="form-container-industrial" style="max-width: 800px; margin: 0 auto;">
                    <span class="block-tag">NEW_FEEDBACK_ENTRY</span>
                    <h2 style="margin-bottom: 10px;"><?= $txt['rev_write_h'] ?></h2>
                    <p style="color: var(--text-muted); margin-bottom: 40px; font-size: 0.9rem;"><?= $txt['rev_write_p'] ?></p>
                    
                    <form method="POST" class="industrial-form">
                        <div class="input-unit">
                            <label><?= $txt['rev_field_car'] ?></label>
                            <input type="text" name="car_info" placeholder="Ex: BMW M5 F90" required>
                        </div>
                        
                        <div class="input-unit">
                            <label><?= $txt['rev_field_rating'] ?></label>
                            <select name="rating">
                                <option value="5">5 ★★★★★</option>
                                <option value="4">4 ★★★★</option>
                                <option value="3">3 ★★★</option>
                                <option value="2">2 ★★</option>
                                <option value="1">1 ★</option>
                            </select>
                        </div>

                        <div class="input-unit">
                            <label><?= $txt['rev_field_text'] ?></label>
                            <textarea name="text" rows="6" placeholder="TYPE YOUR EXPERIENCE..." required></textarea>
                        </div>

                        <button type="submit" class="btn-search-industrial" style="width: 100%; border:none; margin-top: 20px;">
                            <?= $txt['rev_btn_send'] ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- ПРЕДЛОЖЕНИЕ ВОЙТИ -->
            <div style="text-align:center; padding: 80px; border: 1px dashed var(--border); background: rgba(255,255,255,0.02);">
                <p style="color: var(--text-muted); font-weight: 800; letter-spacing: 1px;">
                    <a href="login.php" style="color: var(--accent); text-decoration: underline;">AUTHORIZE</a> чтобы оставить отзыв
                </p>
            </div>
        <?php endif; ?>
    </section>
</main>

<style>
/* Специфические стили для кнопок удаления на публичной странице */
.admin-quick-delete {
    position: absolute;
    top: 25px;
    right: 25px;
    color: #ff3333;
    font-size: 0.65rem;
    font-weight: 900;
    border: 1px solid #ff3333;
    padding: 6px 12px;
    text-decoration: none;
    background: rgba(255, 0, 0, 0.05);
    transition: 0.2s;
    z-index: 10;
}
.admin-quick-delete:hover {
    background: #ff3333;
    color: #000;
}

/* Стили сетки и карточек */
.reviews-grid-industrial {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1px;
    background: var(--border);
    border: 1px solid var(--border);
}
.review-item {
    background: var(--bg);
    padding: 50px;
    transition: 0.3s;
}
.review-item:hover {
    background: var(--surface);
}
.rev-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}
.rev-author { font-weight: 900; color: #fff; text-transform: uppercase; }
.rev-meta { font-size: 0.65rem; color: var(--text-muted); font-family: monospace; }
.stars-industrial { color: var(--accent); letter-spacing: 3px; font-size: 0.75rem; }
.rev-car-tag {
    font-size: 0.6rem;
    font-weight: 900;
    color: var(--accent);
    margin-bottom: 15px;
    letter-spacing: 1px;
}
.rev-content {
    font-size: 1.05rem;
    line-height: 1.8;
    color: var(--text-muted);
    font-style: italic;
}

/* Форма */
.industrial-form textarea {
    background: #000 !important;
    border: 1px solid var(--border) !important;
    padding: 18px !important;
    color: #fff !important;
    font-family: monospace;
    font-size: 0.9rem;
    outline: none;
    width: 100%;
}
.industrial-form textarea:focus { border-color: var(--accent) !important; }

@media (max-width: 900px) {
    .reviews-grid-industrial { grid-template-columns: 1fr; }
}
</style>

<?php require_once 'footer.php'; ?>
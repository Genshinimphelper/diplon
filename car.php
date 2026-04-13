<?php
// 1. ИНИЦИАЛИЗАЦИЯ ЛОГИКИ
session_start();
require_once 'db.php';
require_once 'auth.php'; // Должен быть ПЕРЕД header.php
require_once 'lang.php';

$id = (int)($_GET['id'] ?? 0);

// 2. ЗАПРОС ДАННЫХ ОБЪЕКТА
$res = pg_query_params($conn, "
    SELECT c.*, b.name AS brand, t.name AS transmission_name
    FROM cars c
    LEFT JOIN brands b ON b.id = c.brand_id
    LEFT JOIN transmissions t ON t.id = c.transmission_id
    WHERE c.id = $1
", [$id]);

$car = pg_fetch_assoc($res);

if (!$car) {
    header("Location: catalog.php");
    exit;
}

// 3. ЛОГИКА "НЕДАВНО СМОТРЕЛИ"
if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}
if (!in_array($id, $_SESSION['recently_viewed'])) {
    array_unshift($_SESSION['recently_viewed'], $id);
}
$_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 4);

// 4. ДОПОЛНИТЕЛЬНЫЕ ДАННЫЕ
// Увеличение просмотров
pg_query_params($conn, "UPDATE cars SET views = views + 1 WHERE id = $1", [$id]);

// Галерея изображений
$imgs_res = pg_query_params($conn, "SELECT image FROM car_images WHERE car_id = $1 ORDER BY id", [$id]);
$images = pg_fetch_all($imgs_res) ?: [];
$main_img = $car['image_main'] ?: ($images[0]['image'] ?? 'no_photo.png');

// Расчет рейтинга (Исправлено для PHP 8.1+)
$rating_res = pg_query($conn, "SELECT AVG(rating) as avg_r, COUNT(*) as cnt FROM reviews WHERE status='approved'");
$rating_data = pg_fetch_assoc($rating_res);
$avg_rating = isset($rating_data['avg_r']) ? round((float)$rating_data['avg_r'], 1) : 5.0;

// Проверка избранного
$is_favorite = false;
if (isset($_SESSION['user'])) {
    $uid = $_SESSION['user']['id'];
    $fav_check = pg_query_params($conn, "SELECT 1 FROM favorites WHERE user_id = $1 AND car_id = $2", [$uid, $id]);
    $is_favorite = ($fav_check && pg_num_rows($fav_check) > 0);
}

require_once 'header.php';
?>

<main class="wrap car-detail-page">
    <div class="detail-grid">
        
        <!-- ЛЕВАЯ КОЛОНКА: ВИЗУАЛИЗАЦИЯ И ОПИСАНИЕ -->
        <div class="gallery-column">
            
            <!-- ГАЛЕРЕЯ -->
            <div class="main-visual-stage">
                <img id="main-gallery-img" src="images/<?= htmlspecialchars($main_img) ?>" alt="Main View">
                
                <div class="gallery-nav-arrows">
                    <button id="prev-img" class="nav-arrow">←</button>
                    <button id="next-img" class="nav-arrow">→</button>
                </div>

                <?php if ($car['status'] !== 'active'): ?>
                    <div class="badge-sold-detail"><?= $txt['status_sold'] ?></div>
                <?php endif; ?>
            </div>

            <!-- ЛЕНТА МИНИАТЮР -->
            <div class="thumbnail-strip" id="thumb-container">
                <?php if ($images): foreach ($images as $index => $img): ?>
                    <div class="thumb-node <?= ($img['image'] === $main_img) ? 'active' : '' ?>" 
                         data-src="images/<?= $img['image'] ?>" 
                         data-index="<?= $index ?>">
                        <img src="images/<?= $img['image'] ?>">
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- ТЕХНИЧЕСКОЕ ОПИСАНИЕ (DATA SHEET STYLE) -->
            <div class="description-data-sheet">
                <div class="data-sheet-bg-number"><?= str_pad($car['id'], 2, '0', STR_PAD_LEFT) ?></div>
                
                <header class="data-sheet-header">
                    <h2 class="description-title"><?= $txt['desc_title'] ?></h2>
                </header>
                
                <div class="data-sheet-body">
                    <div class="tech-bracket top-left"></div>
                    <div class="tech-bracket bottom-right"></div>
                    
                    <div class="data-text-wrapped">
                        <?= nl2br(htmlspecialchars($car['description'] ?: 'Информации не предоставлено.')) ?>
                        <br>
                    </div>
                </div>
            </div>

            <!-- VIN REPORT BOX (DECORATIVE) -->
            <div class="vin-report-box-industrial">
                <div class="vin-header">
                    <h3>VIN проверка</h3>
                    <span class="status-verified">Проверка от AUTOMARKET</span>
                </div>
                <div class="vin-grid">
                    <div class="vin-item"> ЮР. ЧИСТОТА: <b>OK</b></div>
                    <div class="vin-item"> ДТП НЕ НАЙДЕНО: <b>OK</b></div>
                    <div class="vin-item"> ЗАЛОГОВ НЕТ: <b>OK</b></div>
                    <div class="vin-item"> ВЛАДЕЛЬЦЕВ: <b>1</b></div>
                </div>
            </div>
        </div>

        <!-- ПРАВАЯ КОЛОНКА: КОММЕРЧЕСКИЕ ДАННЫЕ (STICKY) -->
        <div class="info-column">
            <div class="sticky-info-card">
                
                <div class="info-header">
                    <h1 class="model-line"><?= htmlspecialchars($car['model']) ?></h1>
                    <div class="price-line">
                        <?= number_format($car['price'], 0, '', ' ') ?> 
                        <small>РУБ</small>
                    </div>
                </div>

                <div class="car-actions-vertical">
                    <!-- Кнопка бронирования -->
                    <?php if ($car['is_booked'] === 't'): ?>
                        <button class="btn-industrial-full disabled-btn" disabled><?= $txt['msg_booked'] ?></button>
                    <?php else: ?>
                        <form action="process_booking.php" method="POST">
                            <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                            <button type="submit" class="btn-industrial-full" style="width:100%"><?= $txt['btn_book_now'] ?></button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- ТТХ ТАБЛИЦА -->
                <div class="tech-specs-box">
                    <span class="block-tag"> <?= $txt['car_tech_specs'] ?></span>
                    <table class="specs-table-refined">
                        <tr><td><?= $txt['spec_year'] ?></td><td><?= $car['year'] ?></td></tr>
                        <tr><td><?= $txt['spec_km_title'] ?></td><td><?= number_format($car['mileage'], 0, '', ' ') ?> KM</td></tr>
                        <tr><td><?= $txt['spec_power'] ?></td><td><?= (int)$car['power'] ?> HP</td></tr>
                        <tr><td><?= $txt['spec_trans'] ?></td><td><?= htmlspecialchars($car['transmission_name'] ?? 'N/A') ?></td></tr>
                        <tr><td>Рейтинг</td><td><?= $avg_rating ?> / 5.0 </td></tr>
                    </table>
                </div>

                <!-- УСЛОВИЯ ПОКУПКИ -->
                <div class="purchase-conditions-grid">
                    <div class="condition-item"><span></span> <?= $txt['ser_installments'] ?></div>
                    <div class="condition-item"><span></span> <?= $txt['ser_leasing'] ?></div>
                    <div class="condition-item"><span></span> <?= $txt['ser_delivery'] ?></div>
                </div>

                <!-- БЫСТРАЯ СВЯЗЬ -->
                <div class="callback-mini-panel">
                    <form action="send_lead.php" method="POST">
                        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                        <label><?= $txt['lead_title'] ?></label>
                        <input type="text" name="phone" placeholder="+7 ТЕЛЕФОН" required>
                        <button type="submit" class="btn-small-industrial">Отправить заявку</button>
                    </form>
                </div>

                <?php if (isAdmin()): ?>
                    <div style="margin-top: 30px; border-top: 1px solid var(--border); padding-top: 20px;">
                        <a href="admin_delete_car.php?id=<?= $car['id'] ?>" onclick="return confirm('DELETE?')" class="logout-link">Удалить из реестра </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</main>

<script>
// ЛОКАЛЬНЫЙ СКРИПТ ГАЛЕРЕИ (Для плавности)
document.addEventListener('DOMContentLoaded', () => {
    const mainImg = document.getElementById('main-gallery-img');
    const thumbs = document.querySelectorAll('.thumb-node');
    const nextBtn = document.getElementById('next-img');
    const prevBtn = document.getElementById('prev-img');

    if (mainImg && thumbs.length > 0) {
        let currentIndex = 0;
        const update = (i) => {
            currentIndex = i;
            mainImg.style.opacity = '0';
            setTimeout(() => {
                mainImg.src = thumbs[i].dataset.src;
                mainImg.style.opacity = '1';
            }, 150);
            thumbs.forEach(t => t.classList.remove('active'));
            thumbs[i].classList.add('active');
        }
        thumbs.forEach((t, i) => t.onclick = () => update(i));
        if(nextBtn) nextBtn.onclick = () => update((currentIndex + 1) % thumbs.length);
        if(prevBtn) prevBtn.onclick = () => update((currentIndex - 1 + thumbs.length) % thumbs.length);
    }
});
</script>

<?php require_once 'footer.php'; ?>
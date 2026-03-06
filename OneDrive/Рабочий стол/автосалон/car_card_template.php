<?php
/**
 * Шаблон индустриальной карточки объекта
 * Ожидает переменную $car (массив с данными из БД)
 */

// 1. Подготовка путей и данных
$img_path = !empty($car['image_main']) ? "images/" . htmlspecialchars($car['image_main']) : "images/no_photo.png";
$full_title = htmlspecialchars($car['brand'] . " " . $car['model']);
$price_display = number_format($car['price'], 0, '', ' ');

// 2. Проверка статуса "В избранном"
$is_favorite = false;
if (isset($_SESSION['user'])) {
    $uid = $_SESSION['user']['id'];
    $cid = $car['id'];
    $fav_check = pg_query_params($conn, "SELECT 1 FROM favorites WHERE user_id = $1 AND car_id = $2", [$uid, $cid]);
    $is_favorite = ($fav_check && pg_num_rows($fav_check) > 0);
}

// 3. Проверка статуса "В сравнении"
$is_in_compare = false;
if (isset($_SESSION['compare']) && in_array($car['id'], $_SESSION['compare'])) {
    $is_in_compare = true;
}
?>

<article class="industrial-card">
    <a href="car.php?id=<?= $car['id'] ?>" class="card-anchor" onclick="if(event.target.tagName === 'BUTTON') return false;">
        
        <!-- ВИЗУАЛЬНЫЙ БЛОК (ФОТО) -->
        <div class="card-visual">
            <img src="<?= $img_path ?>" alt="<?= $full_title ?>" class="card-img" loading="lazy">
            
            <!-- Бейджи (слева) -->
            <div class="card-badges-left">
                <?php if ($car['status'] !== 'active'): ?>
                    <span class="badge-tag sold">SOLD OUT</span>
                <?php endif; ?>
                
                <?php if (isset($car['is_verified']) && $car['is_verified'] === 't'): ?>
                <?php endif; ?>
            </div>

            <!-- КНОПКИ (Позиционируются по отдельности в CSS) -->
            <button class="fav-btn-ajax <?= $is_favorite ? 'active' : '' ?>" 
                    data-id="<?= $car['id'] ?>" 
                    title="<?= $txt['fav_save'] ?>"
                    onclick="event.preventDefault();">❤</button>
            
            <button class="compare-btn-ajax <?= $is_in_compare ? 'active' : '' ?>" 
                    data-id="<?= $car['id'] ?>" 
                    title="<?= $txt['btn_compare'] ?>"
                    onclick="event.preventDefault();">⇄</button>
        </div>

        <!-- ТЕХНИЧЕСКИЙ БЛОК (ДАННЫЕ) -->
        <div class="card-data">
            <!-- <span class="data-ref"><?= str_pad($car['id'], 4, '0', STR_PAD_LEFT) ?></span> -->
            <h3 class="data-title"><?= $full_title ?></h3>

            <!-- Сетка характеристик -->
            <div class="data-specs-grid">
                <div class="spec-node">
                    <span class="spec-label">YEAR</span>
                    <span class="spec-val"><?= $car['year'] ?></span>
                </div>
                <div class="spec-node">
                    <span class="spec-label">KM</span>
                    <span class="spec-val"><?= number_format($car['mileage'], 0, '', ' ') ?></span>
                </div>
                <div class="spec-node">
                    <span class="spec-label">HP</span>
                    <span class="spec-val"><?= (int)$car['power'] ?></span>
                </div>
            </div>

            <!-- Цена и индикатор -->
            <div class="card-footer-industrial">
                <div class="price-block">
                    <span class="price-currency">RUB</span>
                    <span class="price-amount"><?= $price_display ?></span>
                </div>
                <div class="data-arrow">→</div>
            </div>
        </div>
    </a>
</article>
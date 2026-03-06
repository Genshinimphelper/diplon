<?php
// 1. ИНИЦИАЛИЗАЦИЯ (ОБЯЗАТЕЛЬНО В ЭТОМ ПОРЯДКЕ)
session_start();
require_once 'db.php';
require_once 'auth.php'; // Сначала загружаем функции (isAdmin и т.д.)
require_once 'lang.php'; // Потом словарь

// 2. ЛОГИКА СРАВНЕНИЯ
$ids = $_SESSION['compare'] ?? [];
$cars = [];

if (!empty($ids)) {
    // Безопасное преобразование массива ID в строку для SQL
    $ids_str = implode(',', array_map('intval', $ids));
    
    $res = pg_query($conn, "
        SELECT c.*, b.name as brand, t.name as transmission_name 
        FROM cars c 
        JOIN brands b ON b.id = c.brand_id 
        JOIN transmissions t ON t.id = c.transmission_id 
        WHERE c.id IN ($ids_str)
    ");
    
    if ($res) {
        while($row = pg_fetch_assoc($res)) {
            $cars[] = $row;
        }
    }
}

// 3. ПОДКЛЮЧЕНИЕ ШАПКИ (Теперь функция isAdmin уже известна системе)
require_once 'header.php';
?>

<main class="wrap page-compare">
    <header class="section-header-huge" style="margin-top:60px;">
        <div class="admin-status-line">ANALYTICS COMPARISON_MODE</div>
        <h1><?= $txt['comp_h'] ?></h1>
        <p style="color: var(--text-muted); margin-top: 10px;">
            <?= count($cars) ?> / 4 ОБЪЕКТА В СРАВНЕНИИ
        </p>
    </header>

    <?php if (empty($cars)): ?>
        <div class="empty-results-industrial" style="text-align: center; padding: 100px; border: 1px dashed var(--border);">
            <h2 style="color: var(--text-muted);"><?= $txt['comp_empty'] ?> </h2>
            <a href="catalog.php" class="view-all-link" style="margin-top: 25px; display: inline-block;">
                <?= $txt['nav_catalog'] ?> →
            </a>
        </div>
    <?php else: ?>
        <div class="compare-table-container">
            <!-- Динамическая сетка: 1 колонка для меток + по 1 на каждую машину -->
            <div class="compare-table-grid" style="grid-template-columns: 200px repeat(<?= count($cars) ?>, 1fr);">
                
                <!-- PHOTO -->
                <div class="compare-label-cell">PHOTO </div>
                <?php foreach($cars as $c): ?>
                    <div class="compare-value-cell cell-photo">
                        <img src="images/<?= htmlspecialchars($c['image_main'] ?: 'no_photo.png') ?>">
                    </div>
                <?php endforeach; ?>

                <!-- MODEL -->
                <div class="compare-label-cell">MODEL </div>
                <?php foreach($cars as $c): ?>
                    <div class="compare-value-cell">
                        <b style="color: #fff;"><?= htmlspecialchars($c['brand']) ?></b>
                        <span style="font-size: 0.8rem;"><?= htmlspecialchars($c['model']) ?></span>
                    </div>
                <?php endforeach; ?>

                <!-- PRICE -->
                <div class="compare-label-cell">PRICE</div>
                <?php foreach($cars as $c): ?>
                    <div class="compare-value-cell highlight-price">
                        <?= number_format($c['price'], 0, '', ' ') ?> ₽
                    </div>
                <?php endforeach; ?>

                <!-- SPECS -->
                <div class="compare-label-cell"><?= $txt['spec_year'] ?> </div>
                <?php foreach($cars as $c): ?>
                    <div class="compare-value-cell"><?= $c['year'] ?></div>
                <?php endforeach; ?>

                <div class="compare-label-cell"><?= $txt['spec_km_title'] ?> </div>
                <?php foreach($cars as $c): ?>
                    <div class="compare-value-cell"><?= number_format($c['mileage'], 0, '', ' ') ?> KM</div>
                <?php endforeach; ?>

                <div class="compare-label-cell"><?= $txt['spec_power'] ?> </div>
                <?php foreach($cars as $c): ?>
                    <div class="compare-value-cell"><?= $c['power'] ?> HP</div>
                <?php endforeach; ?>

                <div class="compare-label-cell"><?= $txt['spec_trans'] ?> </div>
                <?php foreach($cars as $c): ?>
                    <div class="compare-value-cell"><?= htmlspecialchars($c['transmission_name']) ?></div>
                <?php endforeach; ?>

                <!-- ACTIONS -->
                <div class="compare-label-cell">MANAGEMENT</div>
                <?php foreach($cars as $c): ?>
                    <div class="compare-value-cell">
                        <div class="action-flex" style="display:flex; gap: 10px;">
                            <a href="car.php?id=<?= $c['id'] ?>" class="action-btn">VIEW</a>
                            <form method="POST" action="toggle_compare.php" style="display:inline;">
                                <input type="hidden" name="car_id" value="<?= $c['id'] ?>">
                                <button type="submit" class="action-btn del" style="background:none; border: 1px solid var(--border); padding: 5px 10px; cursor:pointer; color: #ff3333;">
                                    REMOVE
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'footer.php'; ?>
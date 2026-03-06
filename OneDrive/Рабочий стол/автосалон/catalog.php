<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

$brand_filter = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;
$price_max = isset($_GET['price_max']) ? (int)$_GET['price_max'] : 0;

// Список марок для селекта
$brands_res = pg_query($conn, "SELECT id, name FROM brands ORDER BY name");

// Формируем запрос
$sql = "SELECT c.*, b.name AS brand, t.name AS transmission_name 
        FROM cars c 
        LEFT JOIN brands b ON b.id = c.brand_id 
        LEFT JOIN transmissions t ON t.id = c.transmission_id 
        WHERE 1=1";

$params = [];
if ($brand_filter > 0) {
    $params[] = $brand_filter;
    $sql .= " AND c.brand_id = $" . count($params);
}
if ($price_max > 0) {
    $params[] = $price_max;
    $sql .= " AND c.price <= $" . count($params);
}

$sql .= " ORDER BY c.id DESC";
$res = pg_query_params($conn, $sql, $params);
$cars = pg_fetch_all($res) ?: [];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $params[] = '%' . $search . '%';
    $sql .= " AND (c.model ILIKE $" . count($params) . " OR b.name ILIKE $" . count($params) . ")";
}

require_once 'header.php';
?>

<main class="wrap page-catalog">
    <!-- Заголовок в стиле Industrial -->
    <header class="section-header-huge" style="margin-top: 60px;">
        <h1>КАТАЛОГ <span>АВТОМОБИЛЕЙ</span></h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; margin-top: 10px;">
            Актуальное наличие автомобилей в дилерском центре <?= count($cars) ?> единиц
        </p>
    </header>

    <?php if (!empty($_SESSION['recently_viewed'])): ?>
<section class="wrap section-recent">
    <div class="section-header">
        <h2>ВЫ НЕДАВНО <span>СМОТРЕЛИ</span></h2>
    </div>
    <div class="car-grid">
        <?php 
        foreach ($_SESSION['recently_viewed'] as $recent_id) {
            // Запрос данных для каждой просмотренной машины
            $res = pg_query_params($conn, "SELECT c.*, b.name as brand FROM cars c JOIN brands b ON b.id = c.brand_id WHERE c.id = $1", [$recent_id]);
            $car = pg_fetch_assoc($res);
            if ($car) include 'car_card_template.php';
        }
        ?>
    </div>
</section>
<?php endif; ?>

    <!-- ПАНЕЛЬ ФИЛЬТРОВ (ПЕРЕРАБОТАНО) -->
    <section class="filter-panel-industrial">
    <form method="GET" class="filter-flex">
        
        <!-- 1. Производитель -->
        <div class="filter-group">
            <label><?= $txt['f_brand'] ?></label>
            <div class="select-wrapper">
                <select name="brand">
                    <option value="0"><?= $txt['f_any'] ?></option>
                    <?php 
                    pg_result_seek($brands_res, 0); 
                    while ($b = pg_fetch_assoc($brands_res)): 
                    ?>
                        <option value="<?= $b['id'] ?>" <?= $brand_filter == $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <!-- 2. Поиск по модели -->
        <div class="filter-group">
            <label>ПОИСК ПО МОДЕЛИ</label>
            <input type="text" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="CAMRY / M5 / X5...">
        </div>
        
        <!-- 3. Бюджет -->
        <div class="filter-group">
            <label><?= $txt['f_price'] ?> (₽)</label>
            <input type="number" name="price_max" value="<?= $price_max > 0 ? $price_max : '' ?>" placeholder="0.00">
        </div>

        <!-- 4. КНОПКА ПОИСКА -->
        <button type="submit" class="btn-search-industrial" style="height: 52px; padding: 0 40px; border: none; align-self: flex-end;">
            <?= $txt['btn_search'] ?>
        </button>
        
        <?php if ($brand_filter > 0 || $price_max > 0 || !empty($_GET['search'])): ?>
            <a href="catalog.php" class="reset-filter-link" style="margin-bottom: 15px;">СБРОСИТЬ</a>
        <?php endif; ?>
    </form>
</section>

    <!-- СЕТКА КАТАЛОГА -->
    <section class="car-grid">
        <?php if ($cars): ?>
            <?php foreach ($cars as $car) include 'car_card_template.php'; ?>
        <?php else: ?>
            <div class="empty-results-industrial">
                <h2>NO OBJECTS FOUND</h2>
                <p>По вашему запросу ничего не найдено. Попробуйте изменить фильтры.</p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once 'footer.php'; ?>
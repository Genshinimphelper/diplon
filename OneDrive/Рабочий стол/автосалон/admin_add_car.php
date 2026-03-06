<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';
requireAdmin();

// 1. Сканируем папку images для выбора фото
$dir = 'images/';
$files = [];
if (is_dir($dir)) {
    // Получаем все файлы, кроме системных и заглушки
    $files = array_diff(scandir($dir), array('.', '..', 'no_photo.png', '.gitkeep'));
}

$msg = '';

// 2. Логика обработки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_id = (int)$_POST['brand_id'];
    $model = trim($_POST['model']);
    $year = (int)$_POST['year'];
    $price = (int)$_POST['price'];
    $mileage = (int)$_POST['mileage'];
    $power = (int)$_POST['power'];
    $transmission_id = (int)$_POST['transmission_id'];
    $body_type = $_POST['body_type'];
    $description = trim($_POST['description']);
    
    // Получаем массив выбранных картинок
    $selected_assets = $_POST['selected_assets'] ?? [];
    // Главной картинкой будет первая из выбранных (или заглушка)
    $main_image = !empty($selected_assets) ? $selected_assets[0] : 'no_photo.png';

    $is_verified = isset($_POST['is_verified']) ? 't' : 'f';
    $one_owner = isset($_POST['one_owner']) ? 't' : 'f';
    $original_pts = isset($_POST['original_pts']) ? 't' : 'f';

    // Вставляем авто
    $res = pg_query_params($conn, "
        INSERT INTO cars (
            brand_id, model, year, price, mileage, power, 
            transmission_id, body_type, description, image_main, 
            status, is_verified, one_owner, original_pts
        )
        VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, 'active', $11, $12, $13)
        RETURNING id
    ", [
        $brand_id, $model, $year, $price, $mileage, $power,
        $transmission_id, $body_type, $description, $main_image,
        $is_verified, $one_owner, $original_pts
    ]);
    
    if ($res) {
        $car_id = pg_fetch_result($res, 0, 'id');
        
        // 3. Добавляем ВСЕ выбранные картинки в таблицу car_images (для галереи в car.php)
        foreach ($selected_assets as $img) {
            pg_query_params($conn, "INSERT INTO car_images (car_id, image) VALUES ($1, $2)", [$car_id, $img]);
        }

        $msg = "DATABASE_UPDATE // SUCCESS: OBJECT AND GALLERY REGISTERED";
    } else {
        $msg = "SYSTEM_ERROR // FAILED: " . pg_last_error($conn);
    }
}

require_once 'header.php';
?>

<main class="wrap page-admin-form">
    <header class="section-header-huge" style="margin-top: 60px;">
        <div class="admin-status-line">SYSTEM // INVENTORY_CONTROL // MULTI_ASSET_MODE</div>
        <h1><?= $txt['adm_add_h'] ?></h1>
    </header>

    <?php if ($msg) echo "<div class='success-banner' style='background:".(strpos($msg, 'ERROR') ? 'var(--accent)' : '#00FF66')."; color:#000; padding:20px; font-weight:900; text-align:center; margin-bottom:40px;'>$msg</div>"; ?>
    
    <form method="POST" class="industrial-form-grid">
        <div class="form-col">
            <div class="input-unit"><label><?= $txt['adm_brand'] ?></label>
                <select name="brand_id" required><?php $br = pg_query($conn, "SELECT * FROM brands ORDER BY name"); while($b = pg_fetch_assoc($br)) echo "<option value='{$b['id']}'>{$b['name']}</option>"; ?></select>
            </div>
            <div class="input-unit"><label><?= $txt['adm_model'] ?></label><input type="text" name="model" required></div>
            <div class="input-unit"><label><?= $txt['adm_price'] ?></label><input type="number" name="price" required></div>
            <div class="input-unit"><label>BODY TYPE</label>
                <select name="body_type"><option value="sedan">SEDAN</option><option value="suv">SUV</option><option value="coupe">COUPE</option><option value="wagon">WAGON</option></select>
            </div>
            <div class="checkbox-industrial-group">
                <label class="custom-check"><input type="checkbox" name="is_verified"> <span><?= $txt['adm_verified'] ?></span></label>
                <label class="custom-check"><input type="checkbox" name="one_owner"> <span><?= $txt['adm_owner'] ?></span></label>
                <label class="custom-check"><input type="checkbox" name="original_pts"> <span>ОРИГИНАЛ ПТС</span></label>
            </div>
        </div>
        
        <div class="form-col">
            <div class="input-unit"><label><?= $txt['adm_year'] ?></label><input type="number" name="year" required></div>
            <div class="input-unit"><label><?= $txt['adm_km'] ?></label><input type="number" name="mileage" required></div>
            <div class="input-unit"><label><?= $txt['adm_hp'] ?></label><input type="number" name="power" required></div>
            <div class="input-unit"><label><?= $txt['adm_trans'] ?></label>
                <select name="transmission_id"><?php $tr = pg_query($conn, "SELECT * FROM transmissions"); while($t = pg_fetch_assoc($tr)) echo "<option value='{$t['id']}'>{$t['name']}</option>"; ?></select>
            </div>
        </div>

        <div class="form-full">
            <div class="input-unit" style="margin-top: 40px;"><label><?= $txt['adm_desc'] ?></label><textarea name="description" rows="6"></textarea></div>
            
            <!-- ГАЛЕРЕЯ ДЛЯ ВЫБОРА ФОТО -->
<!-- ГАЛЕРЕЯ ДЛЯ ВЫБОРА ФАЙЛОВ (ТОЛЬКО ТЕКСТ) -->
<!-- ГАЛЕРЕЯ ДЛЯ ВЫБОРА ФАЙЛОВ -->
<div class="asset-library-box" style="margin-top: 40px; border: 1px solid var(--border); padding: 40px;">
    <label style="margin-bottom: 20px; display: block;"><?= $txt['adm_img'] ?> // FILE_MANIFEST</label>
    <p style="color: var(--text-muted); font-size: 0.7rem; margin-bottom: 30px;">
        Выберите файлы из списка для привязки к объекту. Первый выбранный файл будет назначен основным (MAIN_ASSET).
    </p>

    <div class="asset-text-list">
        <?php foreach ($files as $file): ?>
            <label class="asset-text-item">
                <input type="checkbox" name="selected_assets[]" value="<?= $file ?>">
                <!-- Обертка для текста, чтобы разделить имя и статус -->
                <div class="asset-item-content">
                    <span class="file-name"><?= $file ?></span>
                    <span class="file-status">// READY</span>
                </div>
            </label>
        <?php endforeach; ?>
    </div>
</div>

            <button type="submit" class="btn-industrial-full" style="margin-top: 60px; border: none;">
                <?= $txt['adm_send'] ?>
            </button>
        </div>
    </form>
</main>

<?php require_once 'footer.php'; ?>
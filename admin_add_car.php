<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';

// Доступ только для администратора
requireAdmin();

// 1. Сканируем папку images для выбора фото (те, что загружены через Git)
$dir = 'images/';
$files = [];
if (is_dir($dir)) {
    $files = array_diff(scandir($dir), array('.', '..', 'no_photo.png', '.gitkeep', 'logo.png'));
}

$msg = '';

// 2. Логика обработки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- ОБРАБОТКА МАРКИ (ВВОД ВРУЧНУЮ) ---
    $brand_name_input = trim($_POST['brand_name']);
    
    // Ищем марку в базе (ILIKE - поиск без учета регистра)
    $check_brand = pg_query_params($conn, "SELECT id FROM brands WHERE name ILIKE $1", [$brand_name_input]);
    $found_brand = pg_fetch_assoc($check_brand);

    if ($found_brand) {
        $brand_id = (int)$found_brand['id'];
    } else {
        // Если такой марки нет, создаем её автоматически
        $new_brand_res = pg_query_params($conn, "INSERT INTO brands (name) VALUES ($1) RETURNING id", [$brand_name_input]);
        $brand_id = (int)pg_fetch_result($new_brand_res, 0, 'id');
    }

    // --- ОСТАЛЬНЫЕ ДАННЫЕ ---
    $model = trim($_POST['model']);
    $year = (int)$_POST['year'];
    $price = (int)$_POST['price'];
    $mileage = (int)$_POST['mileage'];
    $power = (int)$_POST['power'];
    $transmission_id = (int)$_POST['transmission_id'];
    $body_type = $_POST['body_type'];
    $description = trim($_POST['description']);
    
    // Получаем массив выбранных файлов из манифеста
    $selected_assets = $_POST['selected_assets'] ?? [];
    $main_image = !empty($selected_assets) ? $selected_assets[0] : 'no_photo.png';

    // Чекбоксы
    $is_verified = isset($_POST['is_verified']) ? 't' : 'f';
    $one_owner = isset($_POST['one_owner']) ? 't' : 'f';
    $original_pts = isset($_POST['original_pts']) ? 't' : 'f';

    // 3. ЗАПИСЬ В ТАБЛИЦУ CARS
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
        
        // 4. ЗАПИСЬ ВСЕХ ВЫБРАННЫХ ФОТО В ГАЛЕРЕЮ
        foreach ($selected_assets as $img) {
            pg_query_params($conn, "INSERT INTO car_images (car_id, image) VALUES ($1, $2)", [$car_id, $img]);
        }

        $msg = "SYSTEM // DATABASE_UPDATE // SUCCESS: OBJECT REGISTERED";
    } else {
        $msg = "SYSTEM // ERROR: " . pg_last_error($conn);
    }
}

require_once 'header.php';
?>

<main class="wrap page-admin-form">
    <header class="section-header-huge" style="margin-top: 60px;">
        <div class="admin-status-line">TERMINAL // INVENTORY_MANAGEMENT // MULTI_ASSET</div>
        <h1><?= $txt['adm_add_h'] ?></h1>
    </header>

    <?php if ($msg): ?>
        <div class="success-banner" style="background: <?= strpos($msg, 'ERROR') ? 'var(--accent)' : '#00FF66' ?>; color: #000; padding: 20px; font-weight: 900; text-align: center; margin-bottom: 40px;">
            <?= $msg ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="industrial-form-grid">
        <!-- ЛЕВАЯ КОЛОНКА -->
        <div class="form-col">
            <div class="input-unit">
                <label><?= $txt['adm_brand'] ?></label>
                <!-- ТЕКСТОВЫЙ ВВОД ВМЕСТО ВЫПАДАЮЩЕГО СПИСКА -->
                <input type="text" name="brand_name" placeholder="BMW / AUDI / MERCEDES" required>
            </div>
            
            <div class="input-unit">
                <label><?= $txt['adm_model'] ?></label>
                <input type="text" name="model" placeholder="Ex: M5 Competition" required>
            </div>
            
            <div class="input-unit">
                <label><?= $txt['adm_price'] ?></label>
                <input type="number" name="price" placeholder="0.00" required>
            </div>

            <div class="input-unit">
                <label>BODY TYPE</label>
                <select name="body_type">
                    <option value="sedan">СЕДАН // SEDAN</option>
                    <option value="suv">ВНЕДОРОЖНИК // SUV</option>
                    <option value="coupe">КУПЕ // COUPE</option>
                    <option value="wagon">УНИВЕРСАЛ // WAGON</option>
                </select>
            </div>

            <div class="checkbox-industrial-group" style="margin-top: 10px;">
                <label class="custom-check"><input type="checkbox" name="is_verified"> <span><?= $txt['adm_verified'] ?></span></label>
                <label class="custom-check"><input type="checkbox" name="one_owner"> <span><?= $txt['adm_owner'] ?></span></label>
                <label class="custom-check"><input type="checkbox" name="original_pts"> <span>ОРИГИНАЛ ПТС</span></label>
            </div>
        </div>
        
        <!-- ПРАВАЯ КОЛОНКА -->
        <div class="form-col">
            <div class="input-unit">
                <label><?= $txt['adm_year'] ?></label>
                <input type="number" name="year" placeholder="20XX" required>
            </div>
            <div class="input-unit">
                <label><?= $txt['adm_km'] ?></label>
                <input type="number" name="mileage" placeholder="0" required>
            </div>
            <div class="input-unit">
                <label><?= $txt['adm_hp'] ?></label>
                <input type="number" name="power" placeholder="0" required>
            </div>
            <div class="input-unit">
                <label><?= $txt['adm_trans'] ?></label>
                <select name="transmission_id">
                    <?php 
                    $tr = pg_query($conn, "SELECT * FROM transmissions ORDER BY name");
                    while($t = pg_fetch_assoc($tr)) echo "<option value='{$t['id']}'>{$t['name']}</option>"; 
                    ?>
                </select>
            </div>
        </div>

        <!-- НИЖНЯЯ ЧАСТЬ -->
        <div class="form-full">
            <div class="input-unit" style="margin-top: 40px;">
                <label><?= $txt['adm_desc'] ?></label>
                <textarea name="description" rows="6" placeholder="ТЕХНИЧЕСКИЙ ЛОГ ОБЪЕКТА..."></textarea>
            </div>
            
            <!-- ВЫБОР ФАЙЛОВ ПО ИМЕНАМ -->
            <div class="asset-library-box" style="margin-top: 40px; border: 1px solid var(--border); padding: 40px;">
                <label style="margin-bottom: 20px; display: block;"><?= $txt['adm_img'] ?> // FILE_MANIFEST</label>
                <p style="color: var(--text-muted); font-size: 0.7rem; margin-bottom: 30px;">
                    Выберите файлы для привязки к объекту. Первая выбранная позиция станет главной (IMAGE_MAIN).
                </p>

                <div class="asset-text-list">
                    <?php foreach ($files as $file): ?>
                        <label class="asset-text-item">
                            <input type="checkbox" name="selected_assets[]" value="<?= $file ?>">
                            <div class="asset-item-content">
                                <span class="file-name"><?= $file ?></span>
                                <span class="file-status">// READY</span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn-industrial-full">
                <?= $txt['adm_send'] ?>
            </button>
        </div>
    </form>
</main>

<?php require_once 'footer.php'; ?>
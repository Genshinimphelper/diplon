<?php
// 1. Инициализация и логика 
session_start();
require_once 'db.php';
require_once 'auth.php'; 
require_once 'lang.php'; 

$success = false;
$error = '';

// 2. Обработка формы оценки
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = (int)($_POST['year'] ?? 0);
    $mileage = (int)($_POST['mileage'] ?? 0);
    $phone = trim($_POST['phone'] ?? '');
    $user_id = $_SESSION['user']['id'] ?? null;

    if ($brand && $model && $phone) {
        $res = pg_query_params($conn, 
            "INSERT INTO car_evaluations (user_id, brand, model, year, mileage, phone, status) 
             VALUES ($1, $2, $3, $4, $5, $6, 'new')",
            [$user_id, $brand, $model, $year, $mileage, $phone]
        );
        if ($res) {
            $success = true;
        } else {
            $error = "DATABASE ERROR // INSERT FAILED";
        }
    } else {
        $error = "ALL FIELDS ARE REQUIRED // ERROR";
    }
}

// 3. Подключение визуальной части
require_once 'header.php';
?>

<main class="wrap page-evaluate">
    <div class="form-container-industrial">
        
        <?php if ($success): ?>
            <!-- Экран после успешной отправки -->
            <div class="success-screen">
                <div style="font-size: 0.7rem; color: #00FF66; font-weight: 900; letter-spacing: 3px; margin-bottom: 20px;">
                    STATUS DATA_RECEIVED
                </div>
                <h1>DONE<span>.</span></h1>
                <p style="color: var(--text-muted); margin: 20px 0 40px; max-width: 400px; margin-left: auto; margin-right: auto;">
                    Ваша заявка на оценку автомобиля занесена в реестр. Технический специалист свяжется с вами для уточнения деталей.
                </p>
                <a href="catalog.php" class="btn-search-industrial" style="text-decoration: none;">
                    <?= $txt['nav_catalog'] ?>  BACK
                </a>
            </div>
        <?php else: ?>
            
            <!-- Заголовок страницы -->
            <header class="form-header-industrial">
                <div style="font-size: 0.6rem; color: var(--accent); font-weight: 900; letter-spacing: 3px; margin-bottom: 10px;">
                   
                </div>
                <h1><?= $txt['eval_h'] ?></h1>
                <p><?= $txt['eval_p'] ?></p>
            </header>

            <?php if ($error): ?>
                <p style="color: var(--accent); font-weight: 900; margin-bottom: 30px;">// ERROR: <?= $error ?></p>
            <?php endif; ?>

            <!-- Форма -->
            <form method="POST" class="industrial-form-grid">
                <div class="form-col">
                    <label><?= $txt['eval_brand'] ?></label>
                    <input type="text" name="brand" placeholder="BMW / AUDI / TOYOTA" required>
                    
                    <label><?= $txt['eval_model'] ?></label>
                    <input type="text" name="model" placeholder="MODEL" required>
                </div>

                <div class="form-col">
                    <label><?= $txt['eval_year'] ?></label>
                    <input type="number" name="year" placeholder="20XX" required>
                    
                    <label><?= $txt['eval_km'] ?></label>
                    <input type="number" name="mileage" placeholder="KM" required>
                </div>

                <div class="form-full">
                    <label><?= $txt['eval_phone'] ?></label>
                    <input type="text" name="phone" placeholder="+7  --- --- -- --" required>
                    
                    <button type="submit" class="btn-search-industrial" style="width: 100%; margin-top: 30px; border: none;">
                        <?= $txt['eval_btn'] ?>
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'footer.php'; ?>
<?php
session_start();
require_once 'db.php';
require_once 'auth.php'; 
require_once 'lang.php';

// 2. Логика обработки формы
$amount = (int)($_GET['amount'] ?? 1500000);
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $loan_amount = (int)($_POST['amount'] ?? 0);
    $term = (int)($_POST['term'] ?? 60);
    $user_id = $_SESSION['user']['id'] ?? null;

    if ($full_name && $phone && $loan_amount > 0) {
        $res = pg_query_params($conn, 
            "INSERT INTO credit_applications (user_id, full_name, phone, loan_amount, term, status) VALUES ($1, $2, $3, $4, $5, 'new')",
            [$user_id, $full_name, $phone, $loan_amount, $term]
        );
        if ($res) {
            $success = true;
        } else {
            $error = "DATABASE ERROR // CONNECTION FAILED";
        }
    } else {
        $error = "REQUIRED FIELDS EMPTY // FILL ALL";
    }
}


require_once 'header.php';
?>

<main class="wrap page-auth">
    <div class="auth-box-industrial" style="max-width: 600px; width: 100%;">
        <?php if ($success): ?>
            <!-- Экран успеха (уже был в коде) -->
            <div style="text-align: center; padding: 40px 0;">
                <div class="status-tag" style="color: #00FF66; border-color: #00FF66; margin-bottom: 20px;">SYSTEM // APPROVED</div>
                <h1 style="font-size: 3.5rem;"><?= $txt['credit_success'] ?></h1>
                <p style="color: var(--text-muted); margin: 20px 0 40px;"><?= $txt['hero_p'] ?></p>
                <a href="index.php" class="btn-industrial-full" style="text-decoration: none;">
                    <?= $txt['credit_back'] ?>
                </a>
            </div>
        <?php else: ?>
            
            <header style="margin-bottom: 40px;">
                <div style="font-size: 0.6rem; color: var(--accent); font-weight: 900; letter-spacing: 3px; margin-bottom: 10px;">
                </div>
                <h1><?= $txt['credit_h'] ?></h1>
            </header>

            <?php if ($error): ?>
                <p class="error-msg" style="margin-bottom: 20px;"> <?= $error ?></p>
            <?php endif; ?>

            <form method="POST">
                <div class="input-unit">
                    <label><?= $txt['credit_name'] ?></label>
                    <input type="text" name="name" placeholder="IVAN IVANOV" required>
                </div>
                
                <div class="input-unit">
                    <label><?= $txt['credit_phone'] ?></label>
                    <input type="text" name="phone" placeholder="+7 --- --- -- --" required>
                </div>
                
                <div class="input-unit">
                    <label><?= $txt['credit_sum'] ?></label>
                    <input type="number" name="amount" value="<?= $amount ?>" required>
                </div>
                
                <div class="input-unit">
                    <label><?= $txt['credit_term'] ?></label>
                    <select name="term">
                        <option value="12">12 Месяцев (1 Год)</option>
                        <option value="36">36 Месяцев (3 Года)</option>
                        <option value="60" selected>60 Месяцев (5 Лет)</option>
                        <option value="84">84 Месяцев (7 Лет)</option>
                    </select>
                </div>

                <button type="submit" class="btn-industrial-full" style="margin-top: 20px;">
                    <?= $txt['calc_btn'] ?> 
                    
                </button>
                
                <div style="margin-top: 30px; font-family: monospace; font-size: 0.55rem; color: #333; text-align: center; letter-spacing: 1px;">
                    При отркавки данных, Вы соглашаетесь с политиокой сайта
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'footer.php'; ?>
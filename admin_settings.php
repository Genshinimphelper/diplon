<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';

// Доступ только для главного админа
requireAdmin();

$msg = '';

// Сохранение настроек
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    foreach ($_POST['set'] as $key => $val) {
        pg_query_params($conn, "UPDATE site_settings SET value = $1 WHERE key = $2", [trim($val), $key]);
    }
    $msg = "";
}

// Запрос всех настроек
$res = pg_query($conn, "SELECT * FROM site_settings ORDER BY id ASC");
$all_settings = pg_fetch_all($res);

require_once 'header.php';
?>

<main class="wrap page-admin">
    <header class="section-header-huge" style="margin-top:60px;">
        <div class="admin-status-line"></div>
        <h1><?= $txt['adm_settings_h'] ?></h1>
        <p><?= $txt['adm_settings_sub'] ?></p>
    </header>

    <?php if ($msg) echo "<div class='success-banner'>$msg</div>"; ?>

    <form method="POST" class="industrial-form-grid" style="margin-top: 40px;">
        <?php foreach ($all_settings as $s): ?>
            <div class="input-unit">
                <label><?= strtoupper(str_replace('_', ' ', $s['key'])) ?>  <?= $s['description'] ?></label>
                <input type="text" name="set[<?= $s['key'] ?>]" value="<?= htmlspecialchars($s['value']) ?>" required>
            </div>
        <?php endforeach; ?>

        <div class="form-full">
            <button type="submit" name="update_settings" class="btn-industrial-full" style="border: none; margin-top: 40px;">
                <?= $txt['btn_save_config'] ?>
            </button>
        </div>
    </form>
</main>

<?php require_once 'footer.php'; ?>
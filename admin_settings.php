<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';

// Доступ только для главного админа
requireAdmin();

$msg = '';

// 1. Сохранение настроек
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    foreach ($_POST['set'] as $key => $val) {
        // Добавляем public. перед именем таблицы
        pg_query_params($conn, "UPDATE public.site_settings SET value = $1 WHERE key = $2", [trim($val), $key]);
    }
    $msg = "SYSTEM // CONFIGURATION_UPDATED // OK";
}

// 2. Запрос всех настроек (Добавлена проверка на успех запроса)
$res = pg_query($conn, "SELECT * FROM public.site_settings ORDER BY id ASC");

if (!$res) {
    // Если таблица всё еще не найдена, выводим внятный текст вместо Fatal Error
    die("<div style='color:white; background:red; padding:20px;'>
            КРИТИЧЕСКАЯ ОШИБКА: Таблица public.site_settings не найдена в базе данных. <br>
            Убедитесь, что вы создали её в phpPgAdmin внутри схемы 'public'.
         </div>");
}

$all_settings = pg_fetch_all($res) ?: [];

require_once 'header.php';
?>

<main class="wrap page-admin">
    <header class="section-header-huge" style="margin-top:60px;">
        <div class="admin-status-line">SYSTEM // GLOBAL_CONFIG // WRITABLE</div>
        <h1><?= $txt['adm_settings_h'] ?></h1>
        <p><?= $txt['adm_settings_sub'] ?></p>
    </header>

    <?php if ($msg) echo "<div class='success-banner' style='background:#00FF66; color:#000; padding:20px; font-weight:900; text-align:center; margin-bottom:40px;'>$msg</div>"; ?>

    <form method="POST" class="industrial-form-grid" style="margin-top: 40px;">
        <?php foreach ($all_settings as $s): ?>
            <div class="input-unit">
                <label><?= strtoupper(str_replace('_', ' ', $s['key'])) ?> // <?= htmlspecialchars($s['description']) ?></label>
                <input type="text" name="set[<?= $s['key'] ?>]" value="<?= htmlspecialchars($s['value']) ?>" required 
                       style="background:#000; border:1px solid var(--border); color:#fff; padding:15px; width:100%; outline:none;">
            </div>
        <?php endforeach; ?>

        <div class="form-full" style="grid-column: span 2;">
            <button type="submit" name="update_settings" class="btn-search-industrial" style="width:100%; border: none; margin-top: 40px;">
                <?= $txt['btn_save_config'] ?>
            </button>
        </div>
    </form>
</main>

<?php require_once 'footer.php'; ?>
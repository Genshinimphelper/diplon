<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';
requireAdmin();

// Получаем все авто
$res = pg_query($conn, "
    SELECT c.*, b.name as brand 
    FROM cars c 
    JOIN brands b ON b.id = c.brand_id 
    ORDER BY c.id DESC
");
$all_cars = pg_fetch_all($res) ?: [];

require_once 'header.php';
?>

<main class="wrap page-admin">
    <header class="section-header-huge" style="margin-top: 60px;">
        <div class="admin-status-line">SYSTEM DATABASE INVENTORY</div>
        <h1><?= $txt['adm_inventory_h'] ?></h1>
        <p><?= $txt['adm_inventory_sub'] ?></p>
    </header>

    <div class="table-scroll-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>PHOTO</th>
                    <th>VEHICLE</th>
                    <th>PRICE</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_cars as $car): ?>
                <tr>
                    <td><small>#<?= str_pad($car['id'], 4, '0', STR_PAD_LEFT) ?></small></td>
                    <td style="width: 80px;">
                        <img src="images/<?= $car['image_main'] ?: 'no_photo.png' ?>" style="width: 60px; aspect-ratio: 1; object-fit: cover; border: 1px solid var(--border);">
                    </td>
                    <td>
                        <b><?= htmlspecialchars($car['brand']) ?></b><br>
                        <?= htmlspecialchars($car['model']) ?> (<?= $car['year'] ?>)
                    </td>
                    <td style="font-family: monospace; font-weight: 800;"><?= number_format($car['price'], 0, '', ' ') ?> ₽</td>
                    <td>
                        <span class="status-badge status-<?= $car['status'] ?>">
                            <?= strtoupper($car['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-flex">
                            <a href="car.php?id=<?= $car['id'] ?>" class="action-btn">VIEW</a>
                            <!-- Ссылка на удаление -->
                            <a href="admin_delete_car.php?id=<?= $car['id'] ?>" 
                               class="action-btn del" 
                               onclick="return confirm('<?= $txt['adm_confirm_delete'] ?>')">
                               <?= $txt['adm_btn_delete'] ?>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once 'footer.php'; ?>
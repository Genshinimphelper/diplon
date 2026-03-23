<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';

// Доступ только для персонала (Админ или Менеджер)
requireStaff();

// Определяем текущую вкладку (по умолчанию - звонки)
$type = $_GET['type'] ?? 'callback';
$status_filter = $_GET['status'] ?? 'all';

// --- ЛОГИКА ОБРАБОТКИ ДЕЙСТВИЙ (СТАТУС / УДАЛЕНИЕ) ---
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Сопоставление вкладок с таблицами БД
    $tables = [
        'callback'  => 'leads',
        'credit'    => 'credit_applications',
        'evaluate'  => 'car_evaluations',
        'testdrive' => 'test_drives',
        'booking'   => 'bookings'
    ];
    $table = $tables[$type] ?? 'leads';

    // 1. УДАЛЕНИЕ (Разрешено только админу)
    if ($_GET['action'] === 'delete' && isAdmin()) {
        
        // Специальная логика для бронирования: если удаляем бронь, освобождаем машину
        if ($type === 'booking') {
            $car_res = pg_query_params($conn, "SELECT car_id FROM bookings WHERE id = $1", [$id]);
            $car_id = pg_fetch_result($car_res, 0, 0);
            if ($car_id) {
                pg_query_params($conn, "UPDATE cars SET is_booked = FALSE WHERE id = $1", [$car_id]);
            }
        }
        
        pg_query_params($conn, "DELETE FROM $table WHERE id = $1", [$id]);
    } 
    
    // 2. ОБНОВЛЕНИЕ СТАТУСА
    elseif ($_GET['action'] === 'status') {
        $val = $_GET['val'] ?? 'processing';
        pg_query_params($conn, "UPDATE $table SET status = $1 WHERE id = $2", [$val, $id]);
    }
    
    header("Location: admin_leads.php?type=$type&status=$status_filter");
    exit;
}

require_once 'header.php';
?>

<main class="wrap page-admin">
    <header class="section-header-huge" style="margin-top:60px;">
        <div class="admin-status-line"></div>
        <h1>МЕнеджер <span>Заявок</span></h1>
        
        <!-- ВКЛАДКИ 01 - 05 -->
        <div class="admin-tabs-container">
            <a href="?type=callback" class="tab-link <?= $type == 'callback' ? 'active' : '' ?>">
                <span class="tab-code">01</span> Звонки 
            </a>
            <a href="?type=credit" class="tab-link <?= $type == 'credit' ? 'active' : '' ?>">
                <span class="tab-code">02</span> Финансы 
            </a>
            <a href="?type=evaluate" class="tab-link <?= $type == 'evaluate' ? 'active' : '' ?>">
                <span class="tab-code">03</span> TRADE-IN 
            </a>
            <a href="?type=testdrive" class="tab-link <?= $type == 'testdrive' ? 'active' : '' ?>">
                <span class="tab-code">04</span> TEST DRIVES 
            </a>
            <a href="?type=booking" class="tab-link <?= $type == 'booking' ? 'active' : '' ?>">
                <span class="tab-code">05</span> Бронирование 
            </a>
        </div>
    </header>

    <!-- ПАНЕЛЬ ФИЛЬТРАЦИИ МЕНЕДЖЕРА -->
    <div class="manager-filters-bar">
        <form method="GET" class="filter-flex-mini">
            <input type="hidden" name="type" value="<?= $type ?>">
            <div class="filter-unit">
                <label>FILTER_BY_STATUS</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>ВСЕ ОПЕРАЦИИ</option>
                    <option value="new" <?= $status_filter == 'new' ? 'selected' : '' ?>>НОВЫЕ</option>
                    <option value="processing" <?= $status_filter == 'processing' ? 'selected' : '' ?>>В РАБОТЕ</option>
                    <option value="closed" <?= $status_filter == 'closed' ? 'selected' : '' ?>>ЗАКРЫТЫЕ</option>
                    <?php if($type === 'booking'): ?>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>ОЖИДАНИЕ</option>
                    <?php endif; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- ТАБЛИЦА ДАННЫХ -->
    <div class="table-scroll-container">
        <table class="admin-table">
            <thead>
                <?php if ($type === 'callback'): ?>
                    <tr><th>Дата</th><th>Номер телефона</th><th>Т.С.</th><th>Статус</th><th>Действие</th></tr>
                <?php elseif ($type === 'credit'): ?>
                    <tr><th>Дата</th><th>Клиент</th><th>Номер телефона</th><th>Сумма</th><th>Статус</th><th>Действие</th></tr>
                <?php elseif ($type === 'evaluate'): ?>
                    <tr><th>Дата</th><th>Т.С. клиента</th><th>Год/KM</th><th>Номер телефона</th><th>Статус</th><th>Действие</th></tr>
                <?php elseif ($type === 'testdrive'): ?>
                    <tr><th>Дата</th><th>Т.С.</th><th>DRIVE DATE</th><th>Номер телефона</th><th>Статус</th><th>Действие</th></tr>
                <?php elseif ($type === 'booking'): ?>
                    <tr><th>Дата</th><th>Клиент</th><th>Т.С.</th><th>Номер телефона</th><th>Статус</th><th>Действие</th></tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php
                // Построение запроса с учетом фильтра
                $where = $status_filter !== 'all' ? " WHERE status = '$status_filter' " : "";
                
                if ($type === 'callback') {
                    $q = "SELECT l.*, b.name as brand, c.model FROM leads l LEFT JOIN cars c ON c.id = l.car_id LEFT JOIN brands b ON b.id = c.brand_id $where";
                } elseif ($type === 'credit') {
                    $q = "SELECT * FROM credit_applications $where";
                } elseif ($type === 'evaluate') {
                    $q = "SELECT * FROM car_evaluations $where";
                } elseif ($type === 'testdrive') {
                    $q = "SELECT t.*, b.name as brand, c.model FROM test_drives t JOIN cars c ON c.id = t.car_id JOIN brands b ON b.id = c.brand_id $where";
                } else {
                    $q = "SELECT bk.*, u.login, b.name as brand, c.model FROM bookings bk JOIN users u ON u.id = bk.user_id JOIN cars c ON c.id = bk.car_id JOIN brands b ON b.id = c.brand_id $where";
                }

                $res = pg_query($conn, $q . " ORDER BY created_at DESC");

                while ($row = pg_fetch_assoc($res)): ?>
                    <tr>
                        <td><?= date('d.m / H:i', strtotime($row['created_at'])) ?></td>
                        
                        <?php if ($type === 'callback'): ?>
                            <td><b><?= htmlspecialchars($row['phone']) ?></b></td>
                            <td><?= $row['brand'] ? htmlspecialchars($row['brand'].' '.$row['model']) : 'GENERAL' ?></td>

                        <?php elseif ($type === 'credit'): ?>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><b><?= htmlspecialchars($row['phone']) ?></b></td>
                            <td><?= number_format($row['loan_amount'],0,'',' ') ?> ₽</td>

                        <?php elseif ($type === 'evaluate'): ?>
                            <td><?= htmlspecialchars($row['model']) ?></td>
                            <td><?= $row['year'] ?> // <?= number_format($row['mileage'],0,'',' ') ?> KM</td>
                            <td><b><?= htmlspecialchars($row['phone']) ?></b></td>

                        <?php elseif ($type === 'testdrive'): ?>
                            <td><?= htmlspecialchars($row['brand'].' '.$row['model']) ?></td>
                            <td><b style="color:var(--accent);"><?= date('d.m.Y', strtotime($row['drive_date'])) ?></b></td>
                            <td><b><?= htmlspecialchars($row['phone']) ?></b></td>

                        <?php elseif ($type === 'booking'): ?>
                            <td><b><?= htmlspecialchars($row['login']) ?></b></td>
                            <td><?= htmlspecialchars($row['brand'].' '.$row['model']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                        <?php endif; ?>

                        <td>
                            <span class="status-badge status-<?= $row['status'] ?>">
                                <?= strtoupper($row['status']) ?>
                            </span>
                        </td>

                        <td>
                            <div class="action-flex">
                                <a href="?type=<?= $type ?>&action=status&val=processing&id=<?= $row['id'] ?>&status=<?= $status_filter ?>" class="action-btn">WORK</a>
                                <a href="?type=<?= $type ?>&action=status&val=closed&id=<?= $row['id'] ?>&status=<?= $status_filter ?>" class="action-btn">CLOSE</a>
                                <?php if (isAdmin()): ?>
                                    <a href="?type=<?= $type ?>&action=delete&id=<?= $row['id'] ?>&status=<?= $status_filter ?>" class="action-btn del" onclick="return confirm('ERASE RECORD?')">DEL</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                
                <?php if (pg_num_rows($res) === 0): ?>
                    <tr><td colspan="6" style="text-align:center; padding: 80px; color: var(--text-muted);"> NO DATA MATCHING CRITERIA</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once 'footer.php'; ?>
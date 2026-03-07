<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';
requireStaff();
// Доступ только для сотрудников
requireLogin();
if (!isAdmin() && $_SESSION['user']['role'] !== 'manager') {
    header("Location: index.php"); exit;
}

$type = $_GET['type'] ?? 'callback';
$status_filter = $_GET['status'] ?? 'all';

// --- ЛОГИКА ДЕЙСТВИЙ ---
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $tables = ['callback'=>'leads', 'credit'=>'credit_applications', 'evaluate'=>'car_evaluations', 'testdrive'=>'test_drives'];
    $table = $tables[$type] ?? 'leads';

    if ($_GET['action'] === 'delete' && isAdmin()) {
        pg_query_params($conn, "DELETE FROM $table WHERE id = $1", [$id]);
    } elseif ($_GET['action'] === 'status') {
        pg_query_params($conn, "UPDATE $table SET status = $1 WHERE id = $2", [$_GET['val'], $id]);
    }
    header("Location: admin_leads.php?type=$type&status=$status_filter"); exit;
}

require_once 'header.php';
?>

<main class="wrap page-admin">
    <header class="section-header-huge">
        <div class="admin-status-line">CRM // LEAD_MANAGEMENT_PROTOCOL</div>
        <h1>LEAD <span>CONTROL</span></h1>
        
        <div class="admin-tabs-container">
            <a href="?type=callback" class="tab-link <?= $type == 'callback' ? 'active' : '' ?>"><span class="tab-code">01</span> CALLS</a>
            <a href="?type=credit" class="tab-link <?= $type == 'credit' ? 'active' : '' ?>"><span class="tab-code">02</span> FINANCE</a>
            <a href="?type=evaluate" class="tab-link <?= $type == 'evaluate' ? 'active' : '' ?>"><span class="tab-code">03</span> TRADE-IN</a>
            <a href="?type=testdrive" class="tab-link <?= $type == 'testdrive' ? 'active' : '' ?>"><span class="tab-code">04</span> TESTS</a>
        </div>
    </header>

    <!-- ПАНЕЛЬ МЕНЕДЖЕРА (ФИЛЬТРЫ) -->
    <div class="manager-filters-bar">
        <form method="GET" class="filter-flex-mini">
            <input type="hidden" name="type" value="<?= $type ?>">
            <div class="filter-unit">
                <label>FILTER_BY_STATUS</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>ВСЕ ЗАЯВКИ</option>
                    <option value="new" <?= $status_filter == 'new' ? 'selected' : '' ?>>NEW (НОВЫЕ)</option>
                    <option value="processing" <?= $status_filter == 'processing' ? 'selected' : '' ?>>WORK (В РАБОТЕ)</option>
                    <option value="closed" <?= $status_filter == 'closed' ? 'selected' : '' ?>>DONE (ЗАКРЫТЫЕ)</option>
                </select>
            </div>
        </form>
    </div>

    <div class="table-scroll-container">
        <table class="admin-table">
            <thead>
                <?php if ($type === 'callback'): ?>
                    <tr><th>DATE</th><th>PHONE</th><th>VEHICLE</th><th>STATUS</th><th>ACTIONS</th></tr>
                <?php elseif ($type === 'credit'): ?>
                    <tr><th>DATE</th><th>NAME</th><th>PHONE</th><th>SUM</th><th>STATUS</th><th>ACTIONS</th></tr>
                <?php elseif ($type === 'evaluate'): ?>
                    <tr><th>DATE</th><th>VEHICLE</th><th>YEAR/KM</th><th>PHONE</th><th>STATUS</th></tr>
                <?php elseif ($type === 'testdrive'): ?>
                    <tr><th>DATE</th><th>VEHICLE</th><th>APPOINTMENT</th><th>PHONE</th><th>STATUS</th></tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php
                // Формирование запроса с фильтром
                $where = $status_filter !== 'all' ? " WHERE status = '$status_filter' " : "";
                
                if ($type === 'callback') $q = "SELECT l.*, b.name as brand, c.model FROM leads l LEFT JOIN cars c ON c.id = l.car_id LEFT JOIN brands b ON b.id = c.brand_id $where";
                elseif ($type === 'credit') $q = "SELECT * FROM credit_applications $where";
                elseif ($type === 'evaluate') $q = "SELECT * FROM car_evaluations $where";
                else $q = "SELECT t.*, b.name as brand, c.model FROM test_drives t JOIN cars c ON c.id = t.car_id JOIN brands b ON b.id = c.brand_id $where";

                $res = pg_query($conn, $q . " ORDER BY created_at DESC");
                while ($row = pg_fetch_assoc($res)): ?>
                    <tr>
                        <td><?= date('d.m / H:i', strtotime($row['created_at'])) ?></td>
                        <?php if ($type === 'callback'): ?>
                            <td><b><?= $row['phone'] ?></b></td>
                            <td><?= $row['brand'] ? $row['brand'].' '.$row['model'] : 'GENERAL' ?></td>
                        <?php elseif ($type === 'credit'): ?>
                            <td><?= $row['full_name'] ?></td>
                            <td><b><?= $row['phone'] ?></b></td>
                            <td><?= number_format($row['loan_amount'],0,'',' ') ?> ₽</td>
                        <?php elseif ($type === 'evaluate'): ?>
                            <td><?= $row['model'] ?></td>
                            <td><?= $row['year'] ?> <?= $row['mileage'] ?></td>
                            <td><b><?= $row['phone'] ?></b></td>
                        <?php elseif ($type === 'testdrive'): ?>
                            <td><?= $row['brand'].' '.$row['model'] ?></td>
                            <td><b style="color:var(--accent);"><?= date('d.m.Y', strtotime($row['drive_date'])) ?></b></td>
                            <td><b><?= $row['phone'] ?></b></td>
                        <?php endif; ?>

                        <td><span class="status-badge status-<?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                        <td>
                            <div class="action-flex">
                                <a href="?type=<?= $type ?>&action=status&val=processing&id=<?= $row['id'] ?>&status=<?= $status_filter ?>" class="action-btn">WORK</a>
                                <a href="?type=<?= $type ?>&action=status&val=closed&id=<?= $row['id'] ?>&status=<?= $status_filter ?>" class="action-btn">CLOSE</a>
                                <?php if (isAdmin()): ?>
                                    <a href="?type=<?= $type ?>&action=delete&id=<?= $row['id'] ?>&status=<?= $status_filter ?>" class="action-btn del" onclick="return confirm('ERASE?')">DEL</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once 'footer.php'; ?>
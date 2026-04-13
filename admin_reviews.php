<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';

// Проверка прав: админ или менеджер
requireLogin();
if (!isAdmin() && $_SESSION['user']['role'] !== 'manager') {
    header("Location: index.php"); exit;
}

// Определяем, какой список смотреть: pending (новые) или approved (опубликованные)
$view = $_GET['view'] ?? 'pending';

// --- ЛОГИКА ДЕЙСТВИЙ (ОДОБРЕНИЕ / УДАЛЕНИЕ) ---
// ЛОГИКА МОДЕРАЦИИ
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $view = $_GET['view'] ?? 'pending';
    
    if ($_GET['action'] === 'approve') {
        // Переводим в статус "одобрено"
        pg_query_params($conn, "UPDATE reviews SET status = 'approved' WHERE id = $1", [$id]);
    } elseif ($_GET['action'] === 'delete') {
        // Полностью удаляем из базы
        pg_query_params($conn, "DELETE FROM reviews WHERE id = $1", [$id]);
    }
    
    header("Location: admin_reviews.php?view=$view");
    exit;
}
// Запрос данных в зависимости от выбранной вкладки
$res = pg_query_params($conn, "
    SELECT r.*, u.login 
    FROM reviews r 
    JOIN users u ON u.id = r.user_id 
    WHERE r.status = $1 
    ORDER BY r.created_at DESC
", [$view]);

require_once 'header.php';
?>

<main class="wrap page-admin">
    <header class="section-header-huge" style="margin-top:60px;">
        <div class="admin-status-line"></div>
        <h1>Отзывы <span></span></h1>
        
        <!-- ВКЛАДКИ -->
        <div class="admin-tabs-container" style="margin-top: 30px;">
            <a href="?view=pending" class="tab-link <?= $view == 'pending' ? 'active' : '' ?>">
                <span class="tab-code">01</span> ОЧЕРЕДЬ
            </a>
            <a href="?view=approved" class="tab-link <?= $view == 'approved' ? 'active' : '' ?>">
                <span class="tab-code">02</span> НА САЙТЕ
            </a>
        </div>
    </header>

    <div class="table-scroll-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>ТС</th>
                    <th>Коммеентарий / Обратная связь</th>
                    <th>Дата</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($r = pg_fetch_assoc($res)): ?>
                    <tr>
                        <td><b><?= htmlspecialchars($r['login']) ?></b></td>
                        <td style="color: var(--accent); font-weight: 800; font-size: 0.7rem;">
                            <?= htmlspecialchars($r['car_info']) ?>
                        </td>
                        <td style="max-width: 450px; color: var(--text-muted); font-size: 0.85rem; line-height: 1.6;">
                            "<?= nl2br(htmlspecialchars($r['text'])) ?>"
                            <div style="margin-top: 8px; color: var(--accent); font-size: 0.7rem;">
                                <?= str_repeat('★', $r['rating']) ?>
                            </div>
                        </td>
                        <td style="font-size: 0.75rem; font-family: monospace;">
                            <?= date('d.m.Y', strtotime($r['created_at'])) ?>
                        </td>
                        <td>
    <div class="action-flex" style="display: flex; gap: 15px;">
        <?php if ($view === 'pending'): ?>
            <!-- Кнопка ОДОБРИТЬ (только в очереди) -->
            <a href="?action=approve&id=<?= $r['id'] ?>&view=pending" 
               class="action-btn approve-link">
               Одобрить
            </a>
        <?php endif; ?>
        
        <!-- Кнопка УДАЛИТЬ (есть всегда) -->
        <a href="?action=delete&id=<?= $r['id'] ?>&view=<?= $view ?>" 
           class="action-btn delete-link" 
           onclick="return confirm('DELETE THIS REVIEW PERMANENTLY?')">
           Удалить
        </a>
    </div>
</td>
                    </tr>
                <?php endwhile; ?>
                
                <?php if (pg_num_rows($res) === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 100px; color: var(--text-muted);">
                             Нет отзывов...
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once 'footer.php'; ?>
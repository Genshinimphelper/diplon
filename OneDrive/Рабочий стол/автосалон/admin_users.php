<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
require_once 'lang.php';
requireAdmin(); // Только для главного админа

// --- ЛОГИКА ИЗМЕНЕНИЙ ---
if (isset($_GET['id'], $_GET['action'])) {
    $uid = (int)$_GET['id'];
    
    // Не даем админу заблокировать самого себя
    if ($uid !== $_SESSION['user']['id']) {
        if ($_GET['action'] === 'toggle_block') {
            pg_query_params($conn, "UPDATE users SET is_blocked = NOT is_blocked WHERE id = $1", [$uid]);
        }
        if ($_GET['action'] === 'set_role' && isset($_GET['role'])) {
            $new_role = $_GET['role'];
            pg_query_params($conn, "UPDATE users SET role = $1 WHERE id = $2", [$new_role, $uid]);
        }
    }
    header("Location: admin_users.php"); exit;
}

$res = pg_query($conn, "SELECT * FROM users ORDER BY id ASC");
require_once 'header.php';
?>

<main class="wrap page-admin">
    <header class="section-header-huge" style="margin-top:60px;">
        <div class="admin-status-line">SYSTEM // USER_DATABASE // ACCESS_CONTROL</div>
        <h1><?= $txt['adm_users_h'] ?></h1>
        <p><?= $txt['adm_users_sub'] ?></p>
    </header>

    <div class="table-scroll-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>LOGIN</th>
                    <th><?= $txt['u_role'] ?></th>
                    <th><?= $txt['u_status'] ?></th>
                    <th><?= $txt['u_action'] ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = pg_fetch_assoc($res)): 
                    $isSelf = ($u['id'] == $_SESSION['user']['id']);
                ?>
                <tr style="<?= $u['is_blocked'] === 't' ? 'opacity: 0.5; background: rgba(255,0,0,0.05);' : '' ?>">
                    <td>#<?= str_pad($u['id'], 3, '0', STR_PAD_LEFT) ?></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <?php if ($u['avatar']): ?>
                                <img src="avatars/<?= $u['avatar'] ?>" style="width:30px; height:30px; border-radius:50%; object-fit:cover;">
                            <?php endif; ?>
                            <b><?= htmlspecialchars($u['login']) ?></b> <?= $isSelf ? '<small>(YOU)</small>' : '' ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($isSelf): ?>
                            <span class="status-badge"><?= strtoupper($u['role']) ?></span>
                        <?php else: ?>
                            <select onchange="location.href='?id=<?= $u['id'] ?>&action=set_role&role=' + this.value" style="padding: 5px; font-size: 0.7rem;">
                                <option value="user" <?= $u['role'] == 'user' ? 'selected' : '' ?>>USER</option>
                                <option value="manager" <?= $u['role'] == 'manager' ? 'selected' : '' ?>>MANAGER</option>
                                <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>ADMIN</option>
                            </select>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge" style="color: <?= $u['is_blocked'] === 't' ? '#ff3333' : '#00FF66' ?>;">
                            <?= $u['is_blocked'] === 't' ? 'BLOCKED' : 'ACTIVE' ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!$isSelf): ?>
                            <a href="?id=<?= $u['id'] ?>&action=toggle_block" class="action-btn <?= $u['is_blocked'] === 't' ? '' : 'del' ?>">
                                <?= $u['is_blocked'] === 't' ? $txt['btn_unblock'] : $txt['btn_block'] ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once 'footer.php'; ?>
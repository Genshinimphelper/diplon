<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
requireLogin();

$user = $_SESSION['user'];
require_once 'header.php';
?>

<main class="wrap page-profile">
    <div class="profile-header-industrial">
        <div class="profile-avatar-big">
            <?php if ($user['avatar']): ?>
                <img src="avatars/<?= $user['avatar'] ?>">
            <?php else: ?>
                <?= strtoupper(substr($user['login'], 0, 1)) ?>
            <?php endif; ?>
        </div>
        <div class="profile-meta">
            <span class="role-tag"><?= strtoupper($user['role']) ?> </span>
            <h1><?= htmlspecialchars($user['login']) ?></h1>
            <a href="logout.php" class="logout-link">TERMINATE SESSION</a>
        </div>
    </div>

    <section class="profile-favorites">
        <h2>SAVED <span>OBJECTS</span></h2>
        <div class="car-grid">
            <?php
            $res = pg_query_params($conn, "
                SELECT c.*, b.name as brand FROM favorites f 
                JOIN cars c ON c.id = f.car_id 
                JOIN brands b ON b.id = c.brand_id 
                WHERE f.user_id = $1", [$user['id']]);
            $favs = pg_fetch_all($res);
            
            if ($favs):
                foreach ($favs as $car) include 'car_card_template.php';
            else:
                echo "<p class='muted'>NO SAVED ITEMS FOUND.</p>";
            endif;
            ?>
        </div>
    </section>
</main>

<?php require_once 'footer.php'; ?>
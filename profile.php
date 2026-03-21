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
    <!-- Вставь это в profile.php после секции Избранного -->
<section class="profile-section" style="margin-top: 80px;">
    <div class="admin-status-line">USER_RESERVATIONS ACTIVE_SESSIONS</div>
    <h2 class="description-title"><?= $txt['nav_my_bookings'] ?></h2>

    <div class="car-grid">
        <?php
        $res_book = pg_query_params($conn, "
            SELECT c.*, b.name as brand, bk.created_at as book_date, bk.status as book_status
            FROM bookings bk
            JOIN cars c ON c.id = bk.car_id
            JOIN brands b ON b.id = c.brand_id
            WHERE bk.user_id = $1 ORDER BY bk.created_at DESC", [$user['id']]);
        
        $my_bookings = pg_fetch_all($res_book);
        
        if ($my_bookings):
            foreach ($my_bookings as $car): ?>
                <div class="industrial-card">
                    <div class="card-visual">
                        <img src="images/<?= $car['image_main'] ?>" class="card-img">
                        <div class="card-overlay-status"><span><?= strtoupper($car['book_status']) ?></span></div>
                    </div>
                    <div class="card-data">
                        <h3 class="data-title"><?= $car['brand'] ?> <?= $car['model'] ?></h3>
                        <p style="font-size: 0.7rem; color: var(--text-muted);">DATE: <?= date('d.m.Y', strtotime($car['book_date'])) ?></p>
                        <a href="car.php?id=<?= $car['id'] ?>" class="view-all-link" style="margin-top: 15px; display: inline-block;">VIEW OBJECT</a>
                    </div>
                </div>
            <?php endforeach;
        else:
            echo "<p class='muted'> NO ACTIVE RESERVATIONS FOUND.</p>";
        endif;
        ?>
    </div>
</section>
</main>

<?php require_once 'footer.php'; ?>
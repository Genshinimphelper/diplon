<?php
session_start();
require_once 'db.php';
$error = ''; $msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $pass = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($pass !== $confirm) {
        $error = "PASSWORDS DO NOT MATCH // ERROR";
    } else {
        $avatar = null;
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $avatar = time() . '_' . $login . '.jpg';
            move_uploaded_file($_FILES['avatar']['tmp_name'], 'avatars/' . $avatar);
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $res = pg_query_params($conn, 
            "INSERT INTO users (login, password, avatar, role) VALUES ($1, $2, $3, 'user')",
            [$login, $hash, $avatar]
        );

        if ($res) {
            $msg = "REGISTRATION COMPLETE. <a href='login.php'>LOGIN</a>";
        } else {
            $error = "USER ALREADY EXISTS // TERMINATED";
        }
    }
}
require_once 'header.php';
?>

<main class="wrap page-auth">
<main class="wrap page-auth">
    <div class="auth-box-industrial">
        <div class="admin-status-line"></div>
        <h1>СОЗДАНИЕ <span>АККАУНТА</span></h1>
        
        <?php 
            if ($error) echo "<p class='error-msg'>$error</p>"; 
            if ($msg) echo "<p class='success-banner'>$msg</p>";
        ?>

        <form method="POST" enctype="multipart/form-data" class="industrial-form">
            <div class="input-unit">
                <label>LOGIN</label>
                <input type="text" name="login" required>
            </div>

            <div class="input-unit">
                <label>PASSWORD</label>
                <input type="password" name="password" required>
            </div>

            <div class="input-unit">
                <label>CONFIRM PASSWORD</label>
                <input type="password" name="confirm" required>
            </div>

            <div class="input-unit">
                <label>AVATAR IMAGE</label>
                <input type="file" name="avatar" accept="image/*" class="industrial-file-input">
            </div>

            <button type="submit" class="btn-industrial-full">
                СОЗДАТЬ
            </button>
        </form>

        <div class="auth-footer-industrial">
            <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>
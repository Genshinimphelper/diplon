<?php
session_start();
require_once 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    $res = pg_query_params($conn, "SELECT * FROM users WHERE login = $1", [$login]);
    $user = pg_fetch_assoc($res);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header("Location: index.php"); exit;
    } else {
        $error = "Некорректные данные!";
    }

    if ($user && password_verify($password, $user['password'])) {
    // ПРОВЕРКА БЛОКИРОВКИ
    if ($user['is_blocked'] === 't') {
        $error = "Вы были заблокированы!";
    } else {
        $_SESSION['user'] = $user;
        header("Location: index.php"); exit;
    }
}
}
require_once 'header.php';
?>

<main class="wrap page-auth">
    <div class="auth-box-industrial">
        <div class="admin-status-line"></div>
        <h1>ВХОД <span>В АККАУНТ</span></h1>
        
        <?php if ($error) echo "<p class='error-msg'>$error</p>"; ?>
        
        <form method="POST" class="industrial-form">
            <div class="input-unit">
                <label>Ваше Имя(Логин)</label>
                <input type="text" name="login" placeholder="ВВОД ЛОГИНА" required>
            </div>

            <div class="input-unit">
                <label>Пароль</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-industrial-full">
                ВОЙТИ
            </button>
        </form>

        <div class="auth-footer-industrial">
            <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?>
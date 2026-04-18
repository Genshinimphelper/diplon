<?php
$host = "postgresql-diplon.alwaysdata.net";
$port = "5432";
$dbname = "diplon_db";
$user = "diplon";     
$password = "qazplm1538";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Ошибка: Не удалось подключиться к БД.");
}

// ПРИНУДИТЕЛЬНО ПЕРЕКЛЮЧАЕМ НА ПРАВИЛЬНУЮ СХЕМУ
pg_query($conn, "SET search_path TO public");

// === БЛОК ДИАГНОСТИКИ (удалишь потом) ===
$debug_res = pg_query($conn, "SELECT current_database(), current_user");
$debug = pg_fetch_assoc($debug_res);
// Выведет инфо в консоль браузера или лог Render
error_log("Connected to DB: " . $debug['current_database'] . " as User: " . $debug['current_user']);
// =======================================

$site = [];
// Пробуем получить настройки
$res = @pg_query($conn, 'SELECT key, value FROM "site_settings"');
if ($res) {
    while($row = pg_fetch_assoc($res)) {
        $site[$row['key']] = $row['value'];
    }
}
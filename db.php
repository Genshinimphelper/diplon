<?php
$host = "postgresql-diplon.alwaysdata.net";
$port = "5432";
$dbname = "diplon_db";
$user = "diplon";
$password = "qazplm1538"; // Твой пароль

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Error: Unable to connect to database.");
}

// ПРИНУДИТЕЛЬНО УСТАНАВЛИВАЕМ ПУТЬ К ТАБЛИЦАМ
pg_query($conn, "SET search_path TO public");

// Безопасная загрузка настроек
$site = [];
$res = pg_query($conn, "SELECT key, value FROM site_settings");
if ($res) {
    while($row = pg_fetch_assoc($res)) {
        $site[$row['key']] = $row['value'];
    }
}

// Заглушки, если в базе всё-таки пусто (чтобы сайт не падал)
if (!isset($site['site_phone'])) $site['site_phone'] = '+7 (900) 123-45-67';
if (!isset($site['site_address'])) $site['site_address'] = 'Москва, Автомобильная 15';
<?php
// Данные подключения
$host = "postgresql-diplon.alwaysdata.net";
$port = "5432";
$dbname = "diplon_db";
$user = "diplon";
$password = "qazplm1538";

// 1. Подключаемся
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Критическая ошибка: Не удалось подключиться к базе данных.");
}

// 2. ПРИНУДИТЕЛЬНО указываем искать в схеме public
// Это исправит ошибку "relation does not exist"
pg_query($conn, "SET search_path TO public");

// 3. Загружаем настройки (используем явный префикс public. на всякий случай)
$site = [];
$res = @pg_query($conn, "SELECT key, value FROM public.site_settings");

if ($res) {
    while($row = pg_fetch_assoc($res)) {
        $site[$row['key']] = $row['value'];
    }
}

// 4. Заглушки (если база данных всё равно не ответила, сайт не упадет)
if (empty($site['site_phone'])) $site['site_phone'] = '+7 (900) 123-45-67';
if (empty($site['site_address'])) $site['site_address'] = 'Москва, Автомобильная 15';
if (empty($site['credit_rate'])) $site['credit_rate'] = '12';
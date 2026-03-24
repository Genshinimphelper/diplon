<?php
$host = "postgresql-diplon.alwaysdata.net";
$port = "5432";
$dbname = "diplon_db"; 
$user = "diplon";
$password = "qazplm1538";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Ошибка подключения к базе данных.");
}

// 2. БЕЗОПАСНАЯ загрузка настроек (БЕЗ строки на 15 линии)
$site = [];

// Проверяем существование таблицы, подавляя ошибки через @
$check_table = @pg_query($conn, "SELECT 1 FROM information_schema.tables WHERE table_name = 'site_settings'");

if ($check_table && pg_num_rows($check_table) > 0) {
    // Если таблица есть, загружаем данные. 
    // На всякий случай указываем схему public.site_settings
    $s_query = pg_query($conn, "SELECT key, value FROM public.site_settings");
    if ($s_query) {
        while($s_row = pg_fetch_assoc($s_query)) {
            $site[$s_row['key']] = $s_row['value'];
        }
    }
} else {
    // Если таблицы нет, задаем значения по умолчанию, чтобы сайт не был пустым
    $site['site_phone'] = '+7 (000) 000-00-00';
    $site['site_address'] = 'Адрес не установлен';
}
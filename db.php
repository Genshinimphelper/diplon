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

// Глобальная загрузка настроек сайта
$s_query = pg_query($conn, "SELECT key, value FROM site_settings");
$site = [];
while($s_row = pg_fetch_assoc($s_query)) {
    $site[$s_row['key']] = $s_row['value'];
}
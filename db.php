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


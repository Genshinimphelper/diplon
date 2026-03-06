<?php
$host = "localhost";
$port = "5432";
$dbname = "car4"; 
$user = "postgres";
$password = "1538";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Ошибка подключения к базе данных.");
}
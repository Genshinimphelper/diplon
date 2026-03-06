<?php
session_start();
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    pg_query_params($conn, "INSERT INTO leads (car_id, phone) VALUES ($1, $2)", 
        [(int)$_POST['car_id'], $_POST['phone']]);
}
header("Location: " . $_SERVER['HTTP_REFERER']);
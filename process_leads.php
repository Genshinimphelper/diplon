<?php
session_start();
require_once 'db.php';

$type = $_GET['type'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($type === 'evaluate') {
        // Оценка авто (Trade-in)
        pg_query_params($conn, 
            "INSERT INTO car_evaluations (brand, model, year, mileage, phone, status) VALUES ($1, $2, $3, $4, $5, 'new')",
            ['Trade-in Request', $_POST['car_info'], (int)$_POST['year'], (int)$_POST['mileage'], $_POST['phone']]
        );
    } elseif ($type === 'testdrive') {
        // Тест-драйв
        pg_query_params($conn, 
            "INSERT INTO test_drives (car_id, phone, drive_date, status) VALUES ($1, $2, $3, 'new')",
            [(int)$_POST['car_id'], $_POST['phone'], $_POST['drive_date']]
        );
    }
}

header("Location: index.php?success=1");
exit;
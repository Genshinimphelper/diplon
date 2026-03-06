<?php
session_start();
require_once 'db.php';
require_once 'auth.php';

requireLogin(); // Бронировать могут только авторизованные

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = (int)$_POST['car_id'];
    $user_id = $_SESSION['user']['id'];

    // 1. Создаем запись о брони
    pg_query_params($conn, "INSERT INTO bookings (car_id, user_id, phone, status) VALUES ($1, $2, $3, 'pending')", 
        [$car_id, $user_id, $_SESSION['user']['login']]);

    // 2. Меняем статус авто
    pg_query_params($conn, "UPDATE cars SET is_booked = TRUE WHERE id = $1", [$car_id]);
    
    header("Location: car.php?id=$car_id&booked=1");
    exit;
}
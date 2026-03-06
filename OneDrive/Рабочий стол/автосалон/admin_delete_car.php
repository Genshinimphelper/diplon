<?php
session_start();
require_once 'db.php';
require_once 'auth.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // 1. Сначала найдем все картинки этого авто в папке images/
    $res_imgs = pg_query_params($conn, "SELECT image FROM car_images WHERE car_id = $1", [$id]);
    $images = pg_fetch_all($res_imgs) ?: [];
    
    // Также берем главную картинку
    $res_main = pg_query_params($conn, "SELECT image_main FROM cars WHERE id = $1", [$id]);
    $main_img = pg_fetch_result($res_main, 0, 0);

    // 2. Удаляем файлы с сервера
    foreach ($images as $img_row) {
        $file = 'images/' . $img_row['image'];
        if (file_exists($file) && $img_row['image'] !== 'no_photo.png') {
            unlink($file);
        }
    }
    if ($main_img && file_exists('images/' . $main_img) && $main_img !== 'no_photo.png') {
        unlink('images/' . $main_img);
    }

    // 3. Удаляем записи из БД
    // (Если в SQL созданы связи с ON DELETE CASCADE, то car_images удалится само)
    pg_query_params($conn, "DELETE FROM car_images WHERE car_id = $1", [$id]);
    pg_query_params($conn, "DELETE FROM favorites WHERE car_id = $1", [$id]);
    pg_query_params($conn, "DELETE FROM leads WHERE car_id = $1", [$id]);
    pg_query_params($conn, "DELETE FROM cars WHERE id = $1", [$id]);
}

// Возвращаемся в инвентаризацию
header("Location: admin_inventory.php");
exit;
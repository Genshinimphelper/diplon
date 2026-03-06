<?php
session_start();
if (!isset($_SESSION['compare'])) $_SESSION['compare'] = [];

$id = (int)($_POST['car_id'] ?? 0);

if ($id > 0) {
    $index = array_search($id, $_SESSION['compare']);
    
    if ($index !== false) {
        // Если уже есть в списке — удаляем
        array_splice($_SESSION['compare'], $index, 1);
        echo "removed";
    } else {
        // Если нет — проверяем лимит (макс 4 машины)
        if (count($_SESSION['compare']) < 4) {
            $_SESSION['compare'][] = $id;
            echo "added";
        } else {
            echo "limit";
        }
    }
}
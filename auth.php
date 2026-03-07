<?php
// Проверка на главного админа (полный доступ)
function isAdmin() {
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

// Проверка на менеджера
function isManager() {
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'manager';
}

// Проверка: является ли пользователь сотрудником (Админ ИЛИ Менеджер)
function isStaff() {
    return isAdmin() || isManager();
}

// Защита страниц только для админа
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: index.php");
        exit;
    }
}

// Защита страниц для сотрудников (админ и менеджер)
function requireStaff() {
    if (!isStaff()) {
        header("Location: login.php");
        exit;
    }
}

function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit;
    }
}
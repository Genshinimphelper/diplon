<?php
session_start(); require_once 'db.php';
if (!isset($_SESSION['user'])) { echo "auth_required"; exit; }
$uid = $_SESSION['user']['id']; $cid = (int)$_POST['car_id'];
$check = pg_query_params($conn, "SELECT 1 FROM favorites WHERE user_id=$1 AND car_id=$2", [$uid, $cid]);
if (pg_num_rows($check) > 0) { pg_query_params($conn, "DELETE FROM favorites WHERE user_id=$1 AND car_id=$2", [$uid, $cid]); echo "removed"; }
else { pg_query_params($conn, "INSERT INTO favorites (user_id, car_id) VALUES ($1, $2)", [$uid, $cid]); echo "added"; }
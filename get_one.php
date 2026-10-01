<?php
include 'koneksi.php';
$id = intval($_GET['id']);
$r = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
echo json_encode($r);
?>
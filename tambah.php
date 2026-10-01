<?php
include 'koneksi.php';
$d = json_decode(file_get_contents("php://input"), true);
$stmt = $conn->prepare("INSERT INTO users (name,nisn,ttl,gender,email,address) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("ssssss", $d['name'],$d['nisn'],$d['ttl'],$d['gender'],$d['email'],$d['address']);
echo json_encode(["status" => $stmt->execute() ? "ok" : "gagal"]);
?>

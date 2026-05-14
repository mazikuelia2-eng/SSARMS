<?php
session_start();
include '../db.php';

// Example: fetch admin info from session or DB
$user_id = $_SESSION['user_id'];

// Get admin data
$stmt = $conn->prepare("SELECT full_name, profile_pic FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$name = $user['full_name'];
$profile = $user['profile_pic'] ? $user['profile_pic'] : '../images/default.png';
?>


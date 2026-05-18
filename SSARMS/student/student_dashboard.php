<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= GET STUDENT INFO ================= */
$student_query = mysqli_query($conn, "
    SELECT 
        s.student_id, 
        u.full_name, 
        u.profile_pic,
        c.class_name
    FROM student s
    JOIN users u ON s.user_id = u.user_id
    JOIN class c ON s.class_id = c.class_id
    WHERE s.user_id = '$user_id'
");

$student = mysqli_fetch_assoc($student_query);

if (!$student) {
    die("Student record not found");
}

/* PROFILE IMAGE */
$profile_pic = !empty($student['profile_pic']) 
    ? $student['profile_pic'] 
    : 'default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family: Arial, sans-serif;
}

body{
    background:#f4f6f9;
    color:#111;
}

/* ================= SIDEBAR ================= */
.sidebar{
    position:fixed;
    left:0;
    top:0;
    width:260px;
    height:100vh;
    background:#0b1220;
    padding:20px 15px;
    display:flex;
    flex-direction:column;
}

/* PROFILE */
.profile{
    text-align:center;
    padding-bottom:15px;
    margin-bottom:15px;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

.profile img{
    width:75px;
    height:75px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #fff;
    margin-bottom:10px;
}

.profile h4{
    color:#fff;
    font-size:15px;
}

.profile p{
    color:#cbd5e1;
    font-size:13px;
}

/* MENU */
.sidebar a{
    color:#cbd5e1;
    text-decoration:none;
    padding:12px 14px;
    margin-bottom:6px;
    border-radius:10px;
    display:flex;
    align-items:center;
    gap:10px;
    font-size:14px;
    transition:0.2s;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.08);
    color:#fff;
    transform:translateX(3px);
}

.sidebar a.active{
    background:#fff;
    color:#0b1220;
    font-weight:bold;
}

.sidebar .bottom{
    margin-top:auto;
}

.sidebar .logout{
    color:#ff6b6b;
}

/* ================= TOPBAR ================= */
.topbar{
    position:fixed;
    left:260px;
    top:0;
    right:0;
    height:60px;
    background:#fff;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 20px;
    border-bottom:1px solid #eee;
}

.topbar .info{
    font-weight:bold;
    color:#111;
}

/* ================= MAIN ================= */
.main{
    margin-left:260px;
    padding:80px 20px;
}

.card{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 2px 8px rgba(0,0,0,0.05);
    max-width:500px;
}

.label{
    font-size:12px;
    color:#666;
    text-transform:uppercase;
    margin-bottom:5px;
}

.value{
    font-size:18px;
    font-weight:bold;
    color:#111;
    margin-bottom:15px;
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:20px;
}
</style>
</head>

<body>
    
<?php include 'student_sidebar.php'; ?>

<!-- TOPBAR -->
<div class="topbar">
    <div class="info">
        Dashboard Overview
    </div>
</div>

<div class="main">

    <div class="grid">

        <div class="card">

            <div class="label">Full Name</div>
            <div class="value">
                <?= htmlspecialchars($student['full_name']); ?>
            </div>

            <div class="label">Class</div>
            <div class="value">
                <?= htmlspecialchars($student['class_name']); ?>
            </div>

            <div class="label">Student ID</div>
            <div class="value">
                <?= $student['student_id']; ?>
            </div>

        </div>

    </div>

</div>

</body>
</html>
<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= GET STUDENT ================= */
$student_query = mysqli_query($conn, "
    SELECT 
        s.student_id,
        s.class_id,
        u.full_name,
        c.class_name
    FROM student s
    JOIN users u ON s.user_id = u.user_id
    JOIN class c ON s.class_id = c.class_id
    WHERE s.user_id = '$user_id'
");

$student = mysqli_fetch_assoc($student_query);

if (!$student) {
    die("Student not found");
}

$class_id = $student['class_id'];

/* ================= GET SUBJECTS (STUDENT CLASS ONLY) ================= */
$subjects = mysqli_query($conn, "
    SELECT subject_id, subject_name
    FROM subject
    WHERE class_id = '$class_id'
    ORDER BY subject_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Subjects</title>

<style>
body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
    color:#111;
}

/* SIDEBAR */
.sidebar{
    position:fixed;
    left:0;
    top:0;
    width:260px;
    height:100vh;
    background:#0b1220;
    padding:20px;
    color:white;
}

.sidebar h3{
    margin-bottom:10px;
}

.sidebar p{
    color:#cbd5e1;
    font-size:13px;
}

/* MAIN */
.main{
    margin-left:260px;
    padding:30px;
}

.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

h2{
    margin-bottom:20px;
    color:#111;
}

/* SUBJECT LIST */
.subject{
    padding:12px;
    border-bottom:1px solid #eee;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.subject:last-child{
    border-bottom:none;
}

.badge{
    background:#eee;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
}
</style>
</head>

<body>

<?php include 'student_sidebar.php'; ?>

<!-- MAIN -->
<div class="main">

<div class="card">

<h2>📘 My Subjects</h2>

<?php if (mysqli_num_rows($subjects) > 0) { ?>

    <?php while ($row = mysqli_fetch_assoc($subjects)) { ?>

        <div class="subject">
            <div>
                <?= htmlspecialchars($row['subject_name']); ?>
            </div>

            <span class="badge">Studying</span>
        </div>

    <?php } ?>

<?php } else { ?>

    <p style="color:red;">No subjects assigned for your class</p>

<?php } ?>

</div>

</div>

</body>
</html>
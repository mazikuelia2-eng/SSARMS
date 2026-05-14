<?php
session_start();
include '../db.php';

/* =========================
   AUTH CHECK (TEACHER ONLY)
========================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =========================
   GET TEACHER ID
========================= */
$teacher = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT teacher_id FROM teacher WHERE user_id = '$user_id'"
));

if (!$teacher) {
    die("Teacher not found");
}

$teacher_id = $teacher['teacher_id'];

/* =========================
   CLASS ID
========================= */
$class_id = $_GET['class_id'] ?? null;

if (!$class_id) {
    die("No class selected");
}

/* =========================
   VERIFY CLASS BELONGS TO TEACHER
   USING SUBJECT TABLE
========================= */
$check = mysqli_query($conn, "
    SELECT *
    FROM subject
    WHERE teacher_id = '$teacher_id'
    AND class_id = '$class_id'
");

if (mysqli_num_rows($check) == 0) {
    die("❌ You are not assigned to this class");
}

/* =========================
   GET CLASS INFO
========================= */
$class = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT class_name
    FROM class
    WHERE class_id = '$class_id'
"));

/* =========================
   GET STUDENTS
========================= */
$students = mysqli_query($conn, "
    SELECT 
        s.student_id,
        u.full_name,
        u.email

    FROM student s

    JOIN users u
        ON s.user_id = u.user_id

    WHERE s.class_id = '$class_id'

    ORDER BY u.full_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Class Students</title>

<style>

body{
    font-family:Arial,sans-serif;
    background:#f1f5f9;
    padding:20px;
}

.container{
    max-width:1000px;
    margin:auto;
}

.card{
    background:white;
    padding:20px;
    border-radius:14px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    margin-bottom:20px;
}

h2{
    color:#1e293b;
    margin:0;
}

.back{
    display:inline-block;
    margin-bottom:15px;
    background:#2563eb;
    color:white;
    padding:10px 16px;
    border-radius:8px;
    text-decoration:none;
}

.back:hover{
    background:#1d4ed8;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#1e293b;
    color:white;
    padding:14px;
    text-align:left;
}

td{
    padding:14px;
    border-bottom:1px solid #e5e7eb;
}

tr:hover{
    background:#f8fafc;
}

.badge{
    background:#dbeafe;
    color:#1d4ed8;
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
}

.empty{
    text-align:center;
    padding:30px;
    color:#64748b;
}

</style>

</head>

<body>

<div class="container">

<a href="my_classes.php" class="back">
    ⬅ Back
</a>

<div class="card">
    <h2>
        📚 <?php echo htmlspecialchars($class['class_name']); ?> Students
    </h2>
</div>

<div class="card">

<?php if(mysqli_num_rows($students) > 0){ ?>

<table>

<tr>
    <th>#</th>
    <th>Student Name</th>
    <th>Email</th>
</tr>

<?php
$i = 1;

while($row = mysqli_fetch_assoc($students)){
?>

<tr>

    <td>
        <?php echo $i++; ?>
    </td>

    <td>
        <span class="badge">
            <?php echo htmlspecialchars($row['full_name']); ?>
        </span>
    </td>

    <td>
        <?php echo htmlspecialchars($row['email']); ?>
    </td>

</tr>

<?php } ?>

</table>

<?php } else { ?>

<div class="empty">
    ❌ No students found in this class
</div>

<?php } ?>

</div>

</div>

</body>
</html>
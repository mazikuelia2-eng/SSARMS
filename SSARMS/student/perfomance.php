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
    SELECT s.student_id, u.full_name, c.class_name
    FROM student s
    JOIN users u ON s.user_id = u.user_id
    JOIN class c ON s.class_id = c.class_id
    WHERE s.user_id = '$user_id'
");

$student = mysqli_fetch_assoc($student_query);

if (!$student) {
    die("Student not found");
}

$student_id = $student['student_id'];

/* ================= AVERAGE MARK ================= */
$avg_query = mysqli_query($conn, "
    SELECT AVG(marks) AS avg_mark
    FROM marks
    WHERE student_id = '$student_id'
");

$avg = mysqli_fetch_assoc($avg_query)['avg_mark'] ?? 0;

/* ================= SUBJECT PERFORMANCE ================= */
$subject_query = mysqli_query($conn, "
    SELECT 
        s.subject_name,
        AVG(m.marks) AS avg_mark
    FROM marks m
    JOIN subject s ON m.subject_id = s.subject_id
    WHERE m.student_id = '$student_id'
    GROUP BY s.subject_id
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Performance</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body{
    font-family: Arial;
    background:#f4f6f9;
    margin:0;
}

.container{
    margin-left:260px;
    padding:20px;
}

/* TOP CARD */
.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 2px 8px rgba(0,0,0,0.05);
    margin-bottom:20px;
}

/* TITLE */
h2{
    margin-bottom:15px;
    color:#111;
}

/* BIG SCORE */
.score{
    font-size:40px;
    font-weight:bold;
    color:#111;
}

/* LABEL */
.label{
    font-size:13px;
    color:#666;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#f1f1f1;
    padding:12px;
    text-align:left;
    color:#111;
}

td{
    padding:12px;
    border-bottom:1px solid #eee;
}

/* BADGE */
.badge{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
    background:#e5e7eb;
    color:#111;
}

/* GOOD / BAD */
.good{
    background:#dcfce7;
    color:#166534;
}

.mid{
    background:#fef9c3;
    color:#854d0e;
}

.low{
    background:#fee2e2;
    color:#991b1b;
}
</style>
</head>

<body>

<?php include 'student_sidebar.php'; ?>

<div class="container">

    <!-- SUMMARY -->
    <div class="card">
        <h2>📊 My Performance</h2>

        <div class="label">Student Name</div>
        <div><b><?= htmlspecialchars($student['full_name']); ?></b></div>

        <div class="label" style="margin-top:10px;">Class</div>
        <div><b><?= htmlspecialchars($student['class_name']); ?></b></div>

        <div class="label" style="margin-top:15px;">Overall Average</div>

        <div class="score">
            <?= round($avg, 1); ?>%
        </div>

        <?php
        $status = "Low";
        $class = "low";

        if ($avg >= 70) {
            $status = "Excellent";
            $class = "good";
        } elseif ($avg >= 50) {
            $status = "Average";
            $class = "mid";
        }
        ?>

        <span class="badge <?= $class; ?>">
            <?= $status; ?>
        </span>
    </div>

    <!-- SUBJECT PERFORMANCE -->
    <div class="card">

        <h2>📚 Subject Performance</h2>

        <table>
            <tr>
                <th>Subject</th>
                <th>Average Marks</th>
                <th>Status</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($subject_query)) { ?>

                <?php
                $mark = round($row['avg_mark'], 1);

                if ($mark >= 70) {
                    $st = "Excellent";
                    $c = "good";
                } elseif ($mark >= 50) {
                    $st = "Average";
                    $c = "mid";
                } else {
                    $st = "Low";
                    $c = "low";
                }
                ?>

                <tr>
                    <td><?= htmlspecialchars($row['subject_name']); ?></td>
                    <td><?= $mark; ?>%</td>
                    <td>
                        <span class="badge <?= $c; ?>">
                            <?= $st; ?>
                        </span>
                    </td>
                </tr>

            <?php } ?>

        </table>

    </div>

</div>

</body>
</html>
<?php
session_start();
include '../db.php';

/* ================= CHECK TEACHER ================= */

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= GET TEACHER INFO ================= */

$user_id = $_SESSION['user_id'];

$teacher = mysqli_fetch_assoc(mysqli_query($conn, "

    SELECT
        t.teacher_id,
        u.full_name

    FROM teacher t

    INNER JOIN users u
        ON t.user_id = u.user_id

    WHERE t.user_id = '$user_id'

"));

$teacher_id = $teacher['teacher_id'];
$teacher_name = $teacher['full_name'];

/* ================= COUNT SUBJECTS ================= */

$subjects = mysqli_fetch_assoc(mysqli_query($conn, "

    SELECT COUNT(*) AS total

    FROM teacher_subject

    WHERE teacher_id = '$teacher_id'

"))['total'];

/* ================= COUNT CLASSES ================= */

$classes = mysqli_fetch_assoc(mysqli_query($conn, "

    SELECT COUNT(DISTINCT class_id) AS total

    FROM teacher_class

    WHERE teacher_id = '$teacher_id'

"))['total'];

/* ================= COUNT STUDENTS ================= */

/*
Assumes student table has class_id
Modify if your structure differs
*/

$students = mysqli_fetch_assoc(mysqli_query($conn, "

    SELECT COUNT(*) AS total

    FROM student s

    INNER JOIN teacher_class tc
        ON s.class_id = tc.class_id

    WHERE tc.teacher_id = '$teacher_id'

"))['total'];

?>

<!DOCTYPE html>
<html>

<head>

<title>Teacher Dashboard</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

body{
    display:flex;
    background:#f1f5f9;
}

/* MAIN */

.main{
    margin-left:260px;
    padding:25px;
    width:100%;
}

/* HEADER */

.header{
    background:white;
    padding:18px 20px;
    border-radius:12px;
    margin-bottom:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

.header h2{
    color:#0f172a;
    font-size:22px;
}

.header a{
    text-decoration:none;
    background:#2563eb;
    color:white;
    padding:10px 16px;
    border-radius:8px;
    font-size:14px;
}

.header a:hover{
    background:#1d4ed8;
}

/* GRID */

.grid{
    display:grid;
    grid-template-columns:
        repeat(auto-fit, minmax(250px,1fr));
    gap:20px;
}

/* CARD */

.card{
    background:white;
    padding:22px;
    border-radius:14px;
    box-shadow:0 6px 15px rgba(0,0,0,0.06);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-4px);
}

.card h3{
    font-size:16px;
    color:#475569;
    margin-bottom:12px;
}

/* STAT */

.stat{
    font-size:34px;
    font-weight:bold;
    color:#2563eb;
}

/* ACTIONS */

.actions a{
    display:block;
    margin:10px 0;
    padding:10px;
    border-radius:8px;
    background:#f8fafc;
    color:#1e293b;
    text-decoration:none;
    transition:0.2s;
}

.actions a:hover{
    background:#e2e8f0;
}

/* RESPONSIVE */

@media(max-width:768px){

    .main{
        margin-left:0;
        padding:15px;
    }

    .header{
        flex-direction:column;
        align-items:flex-start;
        gap:12px;
    }
}

</style>

</head>

<body>

<?php include 'teacher_sidebar.php'; ?>

<!-- MAIN CONTENT -->

<div class="main">

    <!-- HEADER -->

    <div class="header">

        <h2>
            Welcome,
            <?php echo htmlspecialchars($teacher_name); ?>
        </h2>

        <a href="../profile.php">
            My Profile
        </a>

    </div>

    <!-- DASHBOARD -->

    <div class="grid">

        <!-- SUBJECTS -->

        <div class="card">

            <h3>Assigned Subjects</h3>

            <div class="stat">
                <?php echo $subjects; ?>
            </div>

        </div>

        <!-- CLASSES -->

        <div class="card">

            <h3>My Classes</h3>

            <div class="stat">
                <?php echo $classes; ?>
            </div>

        </div>

        <!-- STUDENTS -->

        <div class="card">

            <h3>Total Students</h3>

            <div class="stat">
                <?php echo $students; ?>
            </div>

        </div>

        <!-- QUICK ACTIONS -->

        <div class="card">

            <h3>Quick Actions</h3>

            <div class="actions">

                <a href="enter_marks.php">
                    Enter Marks
                </a>

                <a href="my_classes.php">
                    View Classes
                </a>

                <a href="view_students.php">
                    View Students
                </a>

                <a href="my_subjects.php">
                    My Subjects
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>
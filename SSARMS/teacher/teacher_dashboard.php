<?php
session_start();
include '../db.php';

// CHECK TEACHER
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

// GET TEACHER INFO
$user_id = $_SESSION['user_id'];

$teacher = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT t.teacher_id, u.full_name 
     FROM teacher t 
     JOIN users u ON t.user_id = u.user_id
     WHERE t.user_id = '$user_id'"
));

$teacher_id = $teacher['teacher_id'];
$teacher_name = $teacher['full_name'];

// COUNT SUBJECTS
$subjects = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM subject WHERE teacher_id = '$teacher_id'"
))['total'];
?>



<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial;
        }

        body {
            display: flex;
            background: #f1f5f9;
        }

        /* MAIN CONTENT */
        .main {
            margin-left: 260px; /* space for included sidebar */
            padding: 25px;
            width: 100%;
        }

        /* HEADER */
        .header {
            background: white;
            padding: 18px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .header h2 {
            font-size: 20px;
            color: #0f172a;
        }

        .header a {
            text-decoration: none;
            color: white;
            background: #2563eb;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 14px;
        }

        .header a:hover {
            background: #1d4ed8;
        }

        /* GRID */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        /* CARDS */
        .card {
            background: white;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.06);
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            font-size: 16px;
            color: #475569;
            margin-bottom: 10px;
        }

        .stat {
            font-size: 30px;
            font-weight: bold;
            color: #2563eb;
        }

        /* QUICK ACTIONS */
        .actions a {
            display: block;
            margin: 8px 0;
            text-decoration: none;
            color: #1e293b;
            font-size: 14px;
            padding: 8px;
            border-radius: 6px;
            background: #f8fafc;
        }

        .actions a:hover {
            background: #e2e8f0;
        }

    </style>
</head>

<body>

<?php include 'teacher_sidebar.php'; ?>

<!-- MAIN CONTENT -->
<div class="main">

    <!-- HEADER -->
    <div class="header">
        <h2>Welcome, <?php echo $teacher_name; ?></h2>
        <a href="../profile.php">My Profile</a>
    </div>

    <!-- DASHBOARD CARDS -->
    <div class="grid">

        <div class="card">
            <h3>My Subjects</h3>
            <div class="stat"><?php echo $subjects; ?></div>
        </div>

        <div class="card">
            <h3>Quick Actions</h3>
            <div class="actions">
                <a href="enter_marks.php">Enter Marks</a>
                <a href="my_classes.php">View Classes</a>
                <a href="view_students.php">Students</a>
            </div>
        </div>

        <div class="card">
            <h3>Performance</h3>
            <div class="stat">85%</div>
        </div>

    </div>

</div>

</body>
</html>
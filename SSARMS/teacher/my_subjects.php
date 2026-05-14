<?php
session_start();
include '../db.php';

// CHECK ROLE
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* GET STUDENT INFO   */
$student_query = mysqli_query($conn, "
    SELECT student_id, class_id 
    FROM student
    WHERE user_id = '$user_id'
");

$student = mysqli_fetch_assoc($student_query);
$class_id = $student['class_id'];

/* FETCH SUBJECTS + TEACHER NAME */
$subjects_query = mysqli_query($conn, "
    SELECT 
        sub.subject_name,
        u.full_name AS teacher_name
    FROM subject sub
    LEFT JOIN teacher t ON sub.teacher_id = t.teacher_id
    LEFT JOIN users u ON t.user_id = u.user_id
    WHERE sub.class_id = '$class_id'
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Subjects</title>

    <style>
        body {
            font-family: Arial;
            background: #eef2ff;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 70%;
            margin: auto;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #1e3a8a;
            color: white;
        }

        tr:hover {
            background: #f1f5f9;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: red;
        }
    </style>
</head>

<body>
    <?php include 'teacher_sidebar.php'; ?>

<?php include 'auth.php'; ?>

<h2>📚 My Subjects</h2>

<table>
    <tr>
        <th>Subject Name</th>
        <th>Teacher</th>
    </tr>

    <?php if (mysqli_num_rows($subjects_query) > 0) { ?>

        <?php while ($row = mysqli_fetch_assoc($subjects_query)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                <td><?php echo htmlspecialchars($row['teacher_name'] ?? 'Not Assigned'); ?></td>
            </tr>
        <?php } ?>

    <?php } else { ?>
        <tr>
            <td colspan="2" class="no-data">No subjects assigned</td>
        </tr>
    <?php } ?>

</table>

</body>
</html>
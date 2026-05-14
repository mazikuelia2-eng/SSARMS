<?php
session_start();
include '../db.php';

// CHECK ROLE
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// GET STUDENT ID
$student_query = mysqli_query($conn, "
    SELECT student_id, class_id 
    FROM student
    WHERE user_id = '$user_id'
");

$student = mysqli_fetch_assoc($student_query);
$student_id = $student['student_id'];

// FILTERS
$term = $_GET['term'] ?? 'Term 1';
$year = $_GET['year'] ?? date('Y');

// FETCH MARKS (ONLY APPROVED)
$marks_query = mysqli_query($conn, "
    SELECT s.subject_name, m.marks
    FROM marks m
    JOIN subject s ON m.subject_id = s.subject_id
    WHERE m.student_id = '$student_id'
    AND m.term = '$term'
    AND m.academic_year = '$year'
    AND m.status = 'approved'
");

// CALCULATIONS
$total = 0;
$count = 0;

$marks_data = [];

while ($row = mysqli_fetch_assoc($marks_query)) {
    $marks_data[] = $row;
    $total += $row['marks'];
    $count++;
}

$average = ($count > 0) ? $total / $count : 0;

// GRADE FUNCTION
function getGrade($avg) {
    if ($avg >= 75) return 'A';
    elseif ($avg >= 65) return 'B';
    elseif ($avg >= 45) return 'C';
    elseif ($avg >= 30) return 'D';
    else return 'F';
}

// DIVISION FUNCTION
function getDivision($avg) {
    if ($avg >= 75) return 'Division I';
    elseif ($avg >= 65) return 'Division II';
    elseif ($avg >= 45) return 'Division III';
    else return 'Division IV';
}

$grade = getGrade($average);
$division = getDivision($average);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Results</title>

    <style>
        body {
            font-family: Arial;
            background: #eef2ff;
            padding: 20px;
        }

        h2 {
            text-align: center;
        }

        .filter {
            text-align: center;
            margin-bottom: 20px;
        }

        select {
            padding: 6px;
            margin: 5px;
        }

        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #1e3a8a;
            color: white;
        }

        .summary {
            width: 50%;
            margin: 20px auto;
            background: white;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
</head>

<body>

<?php include 'auth.php'; ?>

<h2>📊 My Results</h2>

<div class="filter">
    <form method="GET">
        <select name="term">
            <option <?php if($term=='Term 1') echo 'selected'; ?>>Term 1</option>
            <option <?php if($term=='Term 2') echo 'selected'; ?>>Term 2</option>
        </select>

        <select name="year">
            <option><?php echo date('Y'); ?></option>
            <option><?php echo date('Y')-1; ?></option>
        </select>

        <button type="submit">Filter</button>
    </form>
</div>

<table>
    <tr>
        <th>Subject</th>
        <th>Marks</th>
    </tr>

    <?php if (count($marks_data) > 0) { ?>
        <?php foreach ($marks_data as $row) { ?>
            <tr>
                <td><?php echo $row['subject_name']; ?></td>
                <td><?php echo $row['marks']; ?></td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="2">No approved results available</td>
        </tr>
    <?php } ?>
</table>

<div class="summary">
    <h3>Summary</h3>
    <p><strong>Total:</strong> <?php echo $total; ?></p>
    <p><strong>Average:</strong> <?php echo number_format($average,2); ?></p>
    <p><strong>Grade:</strong> <?php echo $grade; ?></p>
    <p><strong>Division:</strong> <?php echo $division; ?></p>
</div>

</body>
</html>
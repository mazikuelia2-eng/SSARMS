<?php
session_start();
include 'db.php';

// CHECK LOGIN
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

/* ================= GRADE FUNCTION ================= */
function getGrade($marks) {
    if ($marks >= 75) return 'A';
    elseif ($marks >= 65) return 'B';
    elseif ($marks >= 50) return 'C';
    elseif ($marks >= 40) return 'D';
    else return 'F';
}

/* ================= ADD RESULT ================= */
if (isset($_POST['add_result'])) {

    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];
    $marks = $_POST['marks'];
    $term = $_POST['term'];
    $academic_year = $_POST['academic_year'];

    $grade = getGrade($marks);

    // prevent duplicate entry
    $check = mysqli_query($conn, "
        SELECT * FROM subject_result 
        WHERE student_id='$student_id'
        AND subject_id='$subject_id'
        AND term='$term'
        AND academic_year='$academic_year'
    ");

    if (mysqli_num_rows($check) > 0) {
        $error = "Result already exists!";
    } else {
        mysqli_query($conn, "
            INSERT INTO subject_result (student_id, subject_id, marks, grade, term, academic_year)
            VALUES ('$student_id', '$subject_id', '$marks', '$grade', '$term', '$academic_year')
        ");
        $success = "Result added successfully!";
    }
}

/* ================= FETCH DATA ================= */
$students = mysqli_query($conn, "
    SELECT s.student_id, u.full_name 
    FROM student s
    JOIN users u ON s.user_id = u.user_id
");

$subjects = mysqli_query($conn, "SELECT * FROM subject");

$results = mysqli_query($conn, "
    SELECT sr.*, u.full_name, sub.subject_name
    FROM subject_result sr
    JOIN student s ON sr.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    JOIN subject sub ON sr.subject_id = sub.subject_id
    ORDER BY sr.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Result Entry</title>
    <style>
        body {
            font-family: Arial;
            background: #eef2ff;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
        }
        button {
            background: #2563eb;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 10px;
        }
        .success { color: green; }
        .error { color: red; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>

<body>


<div class="container">

    <h2>📊 Enter Student Results</h2>

    <!-- MESSAGES -->
    <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

    <!-- ADD RESULT -->
    <div class="card">
        <h3>Add Result</h3>

        <form method="POST">

            <select name="student_id" required>
                <option value="">Select Student</option>
                <?php while($s = mysqli_fetch_assoc($students)) { ?>
                    <option value="<?= $s['student_id'] ?>">
                        <?= $s['full_name'] ?>
                    </option>
                <?php } ?>
            </select>

            <select name="subject_id" required>
                <option value="">Select Subject</option>
                <?php while($sub = mysqli_fetch_assoc($subjects)) { ?>
                    <option value="<?= $sub['subject_id'] ?>">
                        <?= $sub['subject_name'] ?>
                    </option>
                <?php } ?>
            </select>

            <select name="term" required>
                <option value="">Select Term</option>
                <option>Term 1</option>
                <option>Term 2</option>
                <option>Term 3</option>
            </select>

            <input type="text" name="academic_year" placeholder="e.g 2025" required>

            <input type="number" name="marks" placeholder="Enter marks (0-100)" required>

            <button type="submit" name="add_result">Save Result</button>

        </form>
    </div>

    <!-- RESULT TABLE -->
    <div class="card">
        <h3>All Entered Results</h3>

        <table>
            <tr>
                <th>Student</th>
                <th>Subject</th>
                <th>Marks</th>
                <th>Grade</th>
                <th>Term</th>
                <th>Year</th>
            </tr>

            <?php while($r = mysqli_fetch_assoc($results)) { ?>
                <tr>
                    <td><?= $r['full_name'] ?></td>
                    <td><?= $r['subject_name'] ?></td>
                    <td><?= $r['marks'] ?></td>
                    <td><?= $r['grade'] ?></td>
                    <td><?= $r['term'] ?></td>
                    <td><?= $r['academic_year'] ?></td>
                </tr>
            <?php } ?>

        </table>
    </div>

</div>

</body>
</html>
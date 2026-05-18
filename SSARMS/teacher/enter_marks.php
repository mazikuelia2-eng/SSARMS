<?php
session_start();
include '../db.php';


/* =========================
   AUTH CHECK
========================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =========================
   GET TEACHER ID
========================= */
$teacher = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT teacher_id 
    FROM teacher 
    WHERE user_id = '$user_id'
"));

if (!$teacher) {
    die("Teacher not found");
}

$teacher_id = $teacher['teacher_id'];

/* =========================
   GET TEACHER CLASSES (IMPORTANT FIX)
========================= */
$classes = mysqli_query($conn, "
    SELECT c.class_id, c.class_name
    FROM teacher_class tc
    JOIN class c ON tc.class_id = c.class_id
    WHERE tc.teacher_id = '$teacher_id'
");

/* =========================
   SELECT CLASS
========================= */
$class_id = $_GET['class_id'] ?? null;

/* =========================
   GET SUBJECTS (teacher + class)
========================= */
$subjects = null;
if ($class_id) {
    $subjects = mysqli_query($conn, "

    SELECT
        s.subject_id,
        s.subject_name

    FROM teacher_subject ts

    INNER JOIN subject s
        ON ts.subject_id = s.subject_id

    WHERE ts.teacher_id = '$teacher_id'
    AND ts.class_id = '$class_id'

    ORDER BY s.subject_name ASC

");
}

/* =========================
   GET STUDENTS
========================= */
$students = null;
if ($class_id) {
    $students = mysqli_query($conn, "
        SELECT s.student_id, u.full_name
        FROM student s
        JOIN users u ON s.user_id = u.user_id
        WHERE s.class_id = '$class_id'
        ORDER BY u.full_name ASC
    ");
}

/* =========================
   SAVE MARKS
========================= */
if (isset($_POST['save_marks'])) {

    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $term = $_POST['term'];
    $year = $_POST['academic_year'];

    $student_ids = $_POST['student_id'];
    $marks = $_POST['marks'];

    for ($i = 0; $i < count($student_ids); $i++) {

        $student_id = $student_ids[$i];
        $mark = trim($marks[$i]);

        if ($mark !== '') {

            /* CHECK EXISTING */
            $check = mysqli_query($conn, "
                SELECT mark_id
                FROM marks
                WHERE student_id = '$student_id'
                AND subject_id = '$subject_id'
                AND term = '$term'
                AND academic_year = '$year'
            ");

            if (mysqli_num_rows($check) > 0) {

                mysqli_query($conn, "
                    UPDATE marks
                    SET marks = '$mark', status='published'
                    WHERE student_id = '$student_id'
                    AND subject_id = '$subject_id'
                    AND term = '$term'
                    AND academic_year = '$year'
                ");

            } else {

                mysqli_query($conn, "
                    INSERT INTO marks (
                        student_id,
                        subject_id,
                        class_id,
                        teacher_id,
                        marks,
                        term,
                        academic_year,
                        status
                    ) VALUES (
                        '$student_id',
                        '$subject_id',
                        '$class_id',
                        '$teacher_id',
                        '$mark',
                        '$term',
                        '$year',
                        'published'
                    )
                ");
            }
        }
    }

    echo "<script>
        alert('Marks saved successfully');
        window.location.href='enter_marks.php?class_id=$class_id';
    </script>";
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Enter Marks</title>

<style>
body{
    font-family: Arial;
    background:#f1f5f9;
    margin:0;
}

.container{
    max-width:900px;
    margin:40px auto;
}

.card{
    background:white;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

h2{
    text-align:center;
    color:#1e293b;
}

select,input{
    width:100%;
    padding:10px;
    margin-top:8px;
    margin-bottom:15px;
    border-radius:8px;
    border:1px solid #ddd;
}

button{
    width:100%;
    padding:12px;
    border:none;
    background:#2563eb;
    color:white;
    border-radius:8px;
    cursor:pointer;
}

button:hover{
    background:#1d4ed8;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#f8fafc;
    padding:10px;
}

td{
    padding:10px;
    border-bottom:1px solid #eee;
}

.badge{
    background:#dbeafe;
    color:#1d4ed8;
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
}
</style>

</head>
<body>

<?php include 'teacher_sidebar.php'; ?>

<?php include 'teacher_navbar.php'; ?>

<div class="container">

<h2>Enter Student Marks</h2>

<!-- CLASS SELECT -->
<div class="card">
<form method="GET">
    <label>Select Class</label>
    <select name="class_id" required>
        <option value="">Choose Class</option>

        <?php while($c = mysqli_fetch_assoc($classes)) { ?>
            <option value="<?= $c['class_id']; ?>"
                <?= ($class_id == $c['class_id']) ? 'selected' : ''; ?>>
                <?= $c['class_name']; ?>
            </option>
        <?php } ?>

    </select>

    <button type="submit">Load Class</button>
</form>
</div>

<?php if ($class_id) { ?>

<div class="card">

<form method="POST">

<input type="hidden" name="class_id" value="<?= $class_id; ?>">

<!-- SUBJECT -->
<label>Subject</label>
<select name="subject_id" required>
    <option value="">Select Subject</option>
    <?php while($s = mysqli_fetch_assoc($subjects)) { ?>
        <option value="<?= $s['subject_id']; ?>">
            <?= $s['subject_name']; ?>
        </option>
    <?php } ?>
</select>

<!-- TERM -->
<label>Term</label>
<select name="term">
    <option>Term 1</option>
    <option>Term 2</option>
</select>

<!-- YEAR -->
<label>Academic Year</label>
<input type="text" name="academic_year"
value="<?= date('Y') . '-' . (date('Y')+1); ?>">

<!-- STUDENTS -->
<table>
<tr>
    <th>Student</th>
    <th>Marks</th>
</tr>

<?php while($st = mysqli_fetch_assoc($students)) { ?>
<tr>
    <td>
        <span class="badge"><?= $st['full_name']; ?></span>
        <input type="hidden" name="student_id[]" value="<?= $st['student_id']; ?>">
    </td>
    <td>
        <input type="number" name="marks[]" min="0" max="100">
    </td>
</tr>
<?php } ?>

</table>

<br>
<button type="submit" name="save_marks">Save Marks</button>

</form>

</div>

<?php } ?>

</div>

</body>
</html>
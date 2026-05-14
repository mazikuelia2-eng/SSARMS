<?php
session_start();
include '../db.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= SAVE SUBJECTS ================= */
if (isset($_POST['save_subjects'])) {

    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $subjects = $_POST['subjects'] ?? [];

    foreach ($subjects as $subject_name) {

        $subject_name = mysqli_real_escape_string($conn, $subject_name);

        if (!empty($subject_name)) {

            mysqli_query($conn, "
                INSERT INTO subject (subject_name, class_id)
                VALUES ('$subject_name', '$class_id')
            ");
        }
    }

    echo "<script>
        alert('Subjects saved successfully');
        window.location.href='manage_class_subjects.php';
    </script>";
    exit();
}

/* ================= FETCH CLASSES ================= */
$classes = mysqli_query($conn, "
    SELECT * FROM class ORDER BY class_name ASC
");

/* ================= FETCH EXISTING SUBJECTS ================= */
$subjects_list = mysqli_query($conn, "
    SELECT 
        s.subject_id,
        s.subject_name,
        c.class_name
    FROM subject s
    LEFT JOIN class c ON s.class_id = c.class_id
    ORDER BY s.subject_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Class Subjects</title>

<style>
body{
    font-family: Arial;
    background:#f1f5f9;
}

.container{
    max-width:900px;
    margin:auto;
    padding:20px;
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

select, input{
    width:100%;
    padding:10px;
    margin:8px 0;
    border:1px solid #ccc;
    border-radius:8px;
}

button{
    width:100%;
    padding:10px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

button:hover{
    background:#1d4ed8;
}

.subject-box input{
    margin-bottom:10px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#1e293b;
    color:white;
    padding:10px;
}

td{
    padding:10px;
    border-bottom:1px solid #eee;
}

</style>

<script>
function generateSubjects() {

    let count = document.getElementById("count").value;
    let container = document.getElementById("subjectContainer");

    container.innerHTML = "";

    for (let i = 0; i < count; i++) {

        container.innerHTML += `
            <input type="text" name="subjects[]" placeholder="Subject ${i+1}" required>
        `;
    }
}
</script>

</head>
<body>

<div class="container">

<h2>📘 Assign Subjects to Class</h2>

<!-- ================= ADD SUBJECTS ================= -->
<div class="card">

<form method="POST">

    <!-- CLASS -->
    <label>Select Class</label>
    <select name="class_id" required>
        <option value="">Choose Class</option>
        <?php while($c = mysqli_fetch_assoc($classes)) { ?>
            <option value="<?= $c['class_id'] ?>">
                <?= htmlspecialchars($c['class_name']) ?>
            </option>
        <?php } ?>
    </select>

    <!-- NUMBER OF SUBJECTS -->
    <label>How many subjects?</label>
    <input type="number" id="count" min="1" onkeyup="generateSubjects()" placeholder="e.g. 5">

    <!-- DYNAMIC INPUTS -->
    <div id="subjectContainer" class="subject-box"></div>

    <button type="submit" name="save_subjects">
        ➕ Save Subjects
    </button>

</form>

</div>

<!-- ================= LIST SUBJECTS ================= -->
<div class="card">

<h3>Existing Subjects</h3>

<table>
<tr>
    <th>Subject</th>
    <th>Class</th>
</tr>

<?php while($s = mysqli_fetch_assoc($subjects_list)) { ?>
<tr>
    <td><?= htmlspecialchars($s['subject_name']) ?></td>
    <td><?= htmlspecialchars($s['class_name']) ?></td>
</tr>
<?php } ?>

</table>

</div>

</div>

</body>
</html>
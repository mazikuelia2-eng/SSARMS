<?php
session_start();
include '../db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ================= CHECK ADMIN ================= */

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ================= SAVE SUBJECTS ================= */

if (isset($_POST['save_subjects'])) {

    $class_id = mysqli_real_escape_string(
        $conn,
        $_POST['class_id']
    );

    $subjects = $_POST['subjects'] ?? [];
    $teachers = $_POST['teachers'] ?? [];

    for ($i = 0; $i < count($subjects); $i++) {

        $subject_name = mysqli_real_escape_string(
            $conn,
            trim($subjects[$i])
        );

        $teacher_id = mysqli_real_escape_string(
            $conn,
            $teachers[$i]
        );

        if (!empty($subject_name)) {

            /* CHECK SUBJECT EXISTS */

            $check_subject = mysqli_query($conn, "
                SELECT *
                FROM subject
                WHERE subject_name = '$subject_name'
                AND class_id = '$class_id'
            ");

            if (mysqli_num_rows($check_subject) == 0) {

                /* INSERT SUBJECT */

                mysqli_query($conn, "
                    INSERT INTO subject (
                        subject_name,
                        class_id
                    )
                    VALUES (
                        '$subject_name',
                        '$class_id'
                    )
                ");

                $subject_id = mysqli_insert_id($conn);

                /* ASSIGN TEACHER */

                if (!empty($teacher_id)) {

                    mysqli_query($conn, "
                        INSERT INTO teacher_subject (
                            teacher_id,
                            class_id,
                            subject_id
                        )
                        VALUES (
                            '$teacher_id',
                            '$class_id',
                            '$subject_id'
                        )
                    ");
                }
            }
        }
    }

    echo "<script>
        alert('Subjects assigned successfully');
        window.location.href='manage_subjects.php';
    </script>";

    exit();
}

/* ================= FETCH CLASSES ================= */

$classes = mysqli_query($conn, "
    SELECT *
    FROM class
    ORDER BY class_name ASC
");

/* ================= FETCH TEACHERS ================= */

$teachers_query = mysqli_query($conn, "
    SELECT
        t.teacher_id,
        t.specialization,
        u.full_name,
        c.class_id,
        c.class_name

    FROM teacher t

    INNER JOIN users u
        ON t.user_id = u.user_id

    INNER JOIN teacher_class tc
        ON t.teacher_id = tc.teacher_id

    INNER JOIN class c
        ON tc.class_id = c.class_id

    ORDER BY u.full_name ASC
");

/* ================= FETCH SUBJECTS ================= */

$subjects_list = mysqli_query($conn, "
    SELECT
        s.subject_id,
        s.subject_name,
        c.class_name,
        u.full_name,
        t.specialization

    FROM subject s

    LEFT JOIN class c
        ON s.class_id = c.class_id

    LEFT JOIN teacher_subject ts
        ON s.subject_id = ts.subject_id

    LEFT JOIN teacher t
        ON ts.teacher_id = t.teacher_id

    LEFT JOIN users u
        ON t.user_id = u.user_id

    ORDER BY s.subject_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Subjects</title>

<style>

body{
    margin:0;
    padding:0;
    background:#f1f5f9;
    font-family:Arial, sans-serif;
}

.container{
    max-width:1100px;
    margin:auto;
    padding:20px;
}

.card{
    background:#fff;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

h2{
    color:#1e293b;
    text-align:center;
    margin-bottom:20px;
}

h3{
    color:#334155;
}

label{
    font-weight:bold;
    color:#334155;
}

input,
select{
    width:100%;
    padding:10px;
    margin-top:8px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:8px;
    font-size:14px;
}

button{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#2563eb;
    color:white;
    font-size:15px;
    cursor:pointer;
}

button:hover{
    background:#1d4ed8;
}

.subject-box{
    background:#f8fafc;
    border:1px solid #ddd;
    padding:15px;
    border-radius:10px;
    margin-bottom:15px;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    background:#1e293b;
    color:white;
    padding:12px;
}

table td{
    padding:12px;
    border-bottom:1px solid #eee;
}

tr:hover{
    background:#f8fafc;
}

.teacher-badge{
    background:#dbeafe;
    color:#1e40af;
    padding:4px 10px;
    border-radius:20px;
    font-size:13px;
    display:inline-block;
}

</style>

<script>

let teachersData = <?php

$teachers_array = [];

mysqli_data_seek($teachers_query, 0);

while($teacher = mysqli_fetch_assoc($teachers_query)) {

    $teachers_array[] = $teacher;
}

echo json_encode($teachers_array);

?>;

function generateSubjects() {

    let count =
        document.getElementById("count").value;

    let class_id =
        document.getElementById("class_id").value;

    let container =
        document.getElementById("subjectContainer");

    container.innerHTML = "";

    /* FILTER TEACHERS BY CLASS */

    let filteredTeachers = teachersData.filter(
        teacher => teacher.class_id == class_id
    );

    for (let i = 0; i < count; i++) {

        let teacherOptions =
            `<option value="">Select Teacher</option>`;

        filteredTeachers.forEach(teacher => {

            teacherOptions += `
                <option value="${teacher.teacher_id}">
                    ${teacher.full_name}
                    (${teacher.specialization || 'No Specialization'})
                </option>
            `;
        });

        container.innerHTML += `

            <div class="subject-box">

                <label>
                    Subject ${i + 1}
                </label>

                <input
                    type="text"
                    name="subjects[]"
                    placeholder="Enter subject name"
                    required
                >

                <label>
                    Assign Teacher
                </label>

                <select
                    name="teachers[]"
                    required
                >
                    ${teacherOptions}
                </select>

            </div>
        `;
    }
}

</script>

</head>

<body>

<?php include 'admin_sidebar.php'; ?>

<div class="container">

<h2>📘 Manage Subjects</h2>

<!-- ================= ADD SUBJECTS ================= -->

<div class="card">

<form method="POST">

    <!-- CLASS -->

    <label>Select Class</label>

    <select
        name="class_id"
        id="class_id"
        required
        onchange="generateSubjects()"
    >

        <option value="">
            Choose Class
        </option>

        <?php while($class = mysqli_fetch_assoc($classes)) { ?>

            <option value="<?= $class['class_id']; ?>">

                <?= htmlspecialchars($class['class_name']); ?>

            </option>

        <?php } ?>

    </select>

    <!-- NUMBER OF SUBJECTS -->

    <label>Number of Subjects</label>

    <input
        type="number"
        id="count"
        min="1"
        placeholder="Example: 5"
        onkeyup="generateSubjects()"
    >

    <!-- SUBJECT CONTAINER -->

    <div id="subjectContainer"></div>

    <!-- SAVE BUTTON -->

    <button
        type="submit"
        name="save_subjects"
    >
        ➕ Save Subjects
    </button>

</form>

</div>

<!-- ================= SUBJECT LIST ================= -->

<div class="card">

<h3>Existing Subjects</h3>

<table>

<tr>
    <th>Subject</th>
    <th>Class</th>
    <th>Teacher</th>
</tr>

<?php while($subject = mysqli_fetch_assoc($subjects_list)) { ?>

<tr>

    <!-- SUBJECT -->

    <td>
        <?= htmlspecialchars($subject['subject_name']); ?>
    </td>

    <!-- CLASS -->

    <td>
        <?= htmlspecialchars($subject['class_name']); ?>
    </td>

    <!-- TEACHER -->

    <td>

        <?php if(!empty($subject['full_name'])) { ?>

            <span class="teacher-badge">

                <?= htmlspecialchars($subject['full_name']); ?>

                (<?= htmlspecialchars($subject['specialization'] ?? 'N/A'); ?>)

            </span>

        <?php } else { ?>

            Not Assigned

        <?php } ?>

    </td>

</tr>

<?php } ?>

</table>

</div>

</div>

</body>

</html>
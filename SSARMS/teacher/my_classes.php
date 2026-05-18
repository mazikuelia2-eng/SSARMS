<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* =========================
   GET TEACHER ID
========================= */
$teacher_query = mysqli_query($conn, "
    SELECT teacher_id
    FROM teacher
    WHERE user_id = '$user_id'
");

$teacher = mysqli_fetch_assoc($teacher_query);

if (!$teacher) {
    die("Teacher not found");
}

$teacher_id = $teacher['teacher_id'];
/* =========================
   GET CLASSES + SUBJECTS
========================= */

$class_query = mysqli_query($conn, "

    SELECT

        c.class_id,
        c.class_name,

        GROUP_CONCAT(
            DISTINCT s.subject_name
            SEPARATOR ', '
        ) AS subjects

    FROM teacher_class tc

    INNER JOIN class c
        ON tc.class_id = c.class_id

    LEFT JOIN teacher_subject ts
        ON c.class_id = ts.class_id
        AND ts.teacher_id = '$teacher_id'

    LEFT JOIN subject s
        ON ts.subject_id = s.subject_id

    WHERE tc.teacher_id = '$teacher_id'

    GROUP BY c.class_id, c.class_name

    ORDER BY c.class_name ASC

");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Classes</title>

    <style>
        body{
            font-family: Arial;
            background:#f1f5f9;
            padding:20px;
        }

        .container{
            max-width:1000px;
            margin:auto;
        }

        .card{
            background:white;
            padding:25px;
            border-radius:15px;
            box-shadow:0 4px 10px rgba(0,0,0,0.05);
        }

        h2{
            margin-bottom:20px;
            color:#1e293b;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th{
            background:#2563eb;
            color:white;
            padding:14px;
            text-align:left;
        }

        td{
            padding:14px;
            border-bottom:1px solid #e2e8f0;
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
            margin:2px;
            display:inline-block;
        }

        .btn{
            background:#2563eb;
            color:white;
            padding:8px 14px;
            border-radius:8px;
            text-decoration:none;
            font-size:13px;
        }

        .btn:hover{
            background:#1d4ed8;
        }

        .empty{
            color:#94a3b8;
            font-size:13px;
        }

        .no-data{
            text-align:center;
            color:red;
            padding:20px;
        }
    </style>
</head>

<body>

<?php include 'teacher_sidebar.php'; ?>

<div class="container">

<div class="card">

<h2>📚 My Classes & Subjects</h2>

<table>

<tr>
    <th>Class</th>
    <th>Subjects</th>
    <th>Action</th>
</tr>

<?php if(mysqli_num_rows($class_query) > 0){ ?>

    <?php while($row = mysqli_fetch_assoc($class_query)){ ?>

    <tr>

        <!-- CLASS NAME -->
        <td>
            <strong>
                <?php echo htmlspecialchars($row['class_name']); ?>
            </strong>
        </td>

        <!-- SUBJECTS -->
        <td>
            <?php
            if (!empty($row['subjects'])) {

                $subjects = explode(',', $row['subjects']);

                foreach ($subjects as $subject) {
                    echo "<span class='badge'>"
                        . htmlspecialchars(trim($subject)) .
                    "</span>";
                }

            } else {
                echo "<span class='empty'>No subjects assigned yet</span>";
            }
            ?>
        </td>

        <!-- ACTION -->
        <td>
            <a class="btn"
               href="view_students.php?class_id=<?php echo $row['class_id']; ?>">
               👨‍🎓 View Students
            </a>
        </td>

    </tr>

    <?php } ?>

<?php } else { ?>

<tr>
    <td colspan="3" class="no-data">
        ❌ No classes assigned to you
    </td>
</tr>

<?php } ?>

</table>

</div>

</div>

</body>
</html>
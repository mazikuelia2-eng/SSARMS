<?php
session_start();
include '../db.php';

// CHECK ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* =========================
   ADD TEACHER
========================= */
if (isset($_POST['add_teacher'])) {

    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $phone = mysqli_real_escape_string($conn, $_POST['phone_no']);
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // INSERT USER
    $insert_user = mysqli_query($conn, "
        INSERT INTO users (
            full_name,
            email,
            password,
            role
        )
        VALUES (
            '$name',
            '$email',
            '$password',
            'teacher'
        )
    ");

    if ($insert_user) {

        $user_id = mysqli_insert_id($conn);

        // INSERT TEACHER
        mysqli_query($conn, "
            INSERT INTO teacher (
                user_id,
                phone_no,
                specialization,
                status
            )
            VALUES (
                '$user_id',
                '$phone',
                '$specialization',
                '$status'
            )
        ");

        $teacher_id = mysqli_insert_id($conn);

        // ASSIGN CLASSES
        if (!empty($_POST['class_ids'])) {

            foreach ($_POST['class_ids'] as $class_id) {

                mysqli_query($conn, "
                    INSERT INTO teacher_class (
                        teacher_id,
                        class_id
                    )
                    VALUES (
                        '$teacher_id',
                        '$class_id'
                    )
                ");
            }
        }

        header("Location: manage_teachers.php");
        exit();
    }
}

/* =========================
   DELETE TEACHER
========================= */
if (isset($_GET['delete'])) {

    $teacher_id = (int)$_GET['delete'];

    // delete relationships first
    mysqli_query($conn, "DELETE FROM teacher_class WHERE teacher_id = '$teacher_id'");

    mysqli_query($conn, "DELETE FROM subject WHERE teacher_id = '$teacher_id'");

    mysqli_query($conn, "DELETE FROM teacher WHERE teacher_id = '$teacher_id'");

    header("Location: manage_teachers.php");
    exit();
}

/*    FETCH CLASSES */
$classes = mysqli_query($conn, "
    SELECT *
    FROM class
    ORDER BY class_name ASC
");

/* =========================
   FETCH TEACHERS
/* =========================
   FETCH TEACHERS (FIXED)
========================= */
$teachers = mysqli_query($conn, "
    SELECT 
        t.teacher_id,
        u.full_name,
        u.email,
        t.phone_no,
        t.specialization,
        t.status,

        GROUP_CONCAT(DISTINCT c.class_name SEPARATOR ', ') AS classes

    FROM teacher t

    JOIN users u 
        ON t.user_id = u.user_id

    LEFT JOIN teacher_class tc 
        ON t.teacher_id = tc.teacher_id

    LEFT JOIN class c 
        ON tc.class_id = c.class_id

    GROUP BY t.teacher_id
    ORDER BY t.teacher_id DESC
");
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Teachers Admin Panel</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
  
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Roboto, Arial, sans-serif;
      background: #eef2ff;
    }

    /* Sidebar simulation (adjust spacing as if admin_sidebar exists) */
    .container {
      margin-left: 260px;
      padding: 30px;
    }

    /* simple card style */
    .card {
      background: white;
      padding: 24px;
      border-radius: 20px;
      margin-bottom: 28px;
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.03), 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    h2 {
      font-size: 26px;
      font-weight: 600;
      margin-bottom: 20px;
      color: #0f172a;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    h2 i {
      color: #2563eb;
      font-size: 28px;
    }

    h3 {
      font-size: 20px;
      font-weight: 500;
      margin-bottom: 18px;
      color: #1e293b;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    h3 i {
      color: #3b82f6;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 18px;
    }

    .input-group {
      display: flex;
      flex-direction: column;
    }

    .input-group label {
      font-weight: 600;
      font-size: 13px;
      margin-bottom: 6px;
      color: #334155;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .input-group label i {
      width: 18px;
      color: #4b5563;
      font-size: 13px;
    }

    input, select {
      padding: 10px 12px;
      border: 1px solid #cbd5e1;
      border-radius: 12px;
      font-size: 14px;
      transition: 0.2s;
      background: #fefefe;
    }

    input:focus, select:focus {
      border-color: #3b82f6;
      outline: none;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
    }

    button {
      background: #2563eb;
      color: white;
      border: none;
      font-weight: 600;
      padding: 11px 18px;
      border-radius: 40px;
      cursor: pointer;
      font-size: 14px;
      transition: 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    button i {
      font-size: 14px;
    }

    button:hover {
      background: #1d4ed8;
      transform: scale(0.98);
    }

    /* table styles - simple & clean */
    table {
      width: 100%;
      border-collapse: collapse;
    }

    th {
      text-align: left;
      padding: 14px 10px;
      background: #f8fafc;
      font-weight: 600;
      color: #0f172a;
      border-bottom: 2px solid #e2e8f0;
    }

    td {
      padding: 14px 10px;
      border-bottom: 1px solid #e9edf2;
      vertical-align: middle;
    }

    tr:hover {
      background: #fafcff;
    }

    .badge {
      background: #e0f2fe;
      color: #0369a1;
      padding: 5px 12px;
      border-radius: 40px;
      font-size: 12px;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      width: fit-content;
    }

    .badge i {
      font-size: 11px;
    }

    .status-active {
      background: #dcfce7;
      color: #15803d;
      padding: 5px 12px;
      border-radius: 40px;
      font-size: 12px;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .status-inactive {
      background: #ffe4e2;
      color: #b91c1c;
      padding: 5px 12px;
      border-radius: 40px;
      font-size: 12px;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .delete-btn {
      background: #ef4444;
      color: white;
      padding: 7px 14px;
      border-radius: 30px;
      text-decoration: none;
      font-size: 12px;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: 0.2s;
    }

    .delete-btn i {
      font-size: 12px;
    }

    .delete-btn:hover {
      background: #dc2626;
    }

    select[multiple] {
      height: 110px;
    }

    /* simple responsive */
    @media (max-width: 768px) {
      .container {
        margin-left: 0;
        padding: 18px;
      }
      table {
        display: block;
        overflow-x: auto;
      }
    }

    /* hint / no extra stuff */
    .fa, .fas, .far {
      pointer-events: none;
    }
  </style>
</head>
<body>

<!-- Sidebar include (assuming admin_sidebar.php provides the left menu) -->
<?php include 'admin_sidebar.php'; ?>

<div class="container">

  <!-- Page title with icon -->
  <h2>
    <i class="fas fa-chalkboard-user"></i> 
    Manage Teachers
  </h2>

  <!-- ========= ADD TEACHER CARD ========= -->
  <div class="card">
    <h3>
      <i class="fas fa-user-plus"></i> 
      Add Teacher
    </h3>

    <form method="POST">
      <div class="form-grid">
        <!-- Full Name -->
        <div class="input-group">
          <label><i class="fas fa-user-graduate"></i> Full Name</label>
          <input type="text" name="full_name" placeholder="e.g., Sarah Johnson" required>
        </div>
        <!-- Email -->
        <div class="input-group">
          <label><i class="fas fa-envelope"></i> Email</label>
          <input type="email" name="email" placeholder="teacher@school.com" required>
        </div>
        <!-- Password -->
        <div class="input-group">
          <label><i class="fas fa-lock"></i> Password</label>
          <input type="password" name="password" placeholder="••••••" required>
        </div>
        <!-- Phone Number -->
        <div class="input-group">
          <label><i class="fas fa-phone-alt"></i> Phone Number</label>
          <input type="text" name="phone_no" placeholder="+1 234 567 8900">
        </div>
        <!-- Specialization -->
        <div class="input-group">
          <label><i class="fas fa-flask"></i> Specialization</label>
          <input type="text" name="specialization" placeholder="Math, Physics, Literature">
        </div>
        <!-- Status -->
        <div class="input-group">
          <label><i class="fas fa-toggle-on"></i> Status</label>
          <select name="status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <!-- Assign Class -->
       <!-- Assign Classes -->
<!-- Assign Classes -->
<div class="input-group">
  <label><i class="fas fa-school"></i> Assign Classes</label>

  <div style="max-height:200px; overflow:auto; padding:10px; border:1px solid #ddd; border-radius:10px; background:#fff;">

    <?php
    mysqli_data_seek($classes, 0);

    while ($class = mysqli_fetch_assoc($classes)) {
    ?>
        <label style="display:block; margin-bottom:8px; cursor:pointer;">
            <input type="checkbox" name="class_ids[]" value="<?php echo $class['class_id']; ?>">
            <?php echo htmlspecialchars($class['class_name']); ?>
        </label>
    <?php } ?>

  </div>
</div>

<!-- Assign Subjects -->

      </div>
      <br>
      <button type="submit" name="add_teacher">
        <i class="fas fa-save"></i> Save Teacher
      </button>
    </form>
  </div>


  <div class="card">
    <h3>
      <i class="fas fa-users-viewfinder"></i> 
      Teachers List
    </h3>

    <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Specialization</th>
            <th>Classes</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

    <?php
    // Teachers listing
    if (isset($teachers) && mysqli_num_rows($teachers) > 0) {

        mysqli_data_seek($teachers, 0);

        while ($row = mysqli_fetch_assoc($teachers)) {
    ?>

        <tr>

            <!-- ID -->
            <td>
                <strong>
                    <?php echo $row['teacher_id']; ?>
                </strong>
            </td>

            <!-- NAME -->
            <td>
                <?php echo htmlspecialchars($row['full_name']); ?>
            </td>

            <!-- EMAIL -->
            <td>
                <?php echo htmlspecialchars($row['email']); ?>
            </td>

            <!-- PHONE -->
            <td>
                <?php
                if ($row['phone_no']) {
                    echo htmlspecialchars($row['phone_no']);
                } else {
                    echo '<span style="opacity:0.6;">—</span>';
                }
                ?>
            </td>

            <!-- SPECIALIZATION -->
            <td>
                <?php if(!empty($row['specialization'])) { ?>
                    <span class="badge">
                        <?php echo htmlspecialchars($row['specialization']); ?>
                    </span>
                <?php } else { ?>
                    <span style="color:#94a3b8;">—</span>
                <?php } ?>
            </td>

            <!-- CLASSES -->
            <td>
                <?php
                if (!empty($row['classes'])) {
                    echo htmlspecialchars($row['classes']);
                } else {
                    echo '<span style="color:#94a3b8;">Not Assigned</span>';
                }
                ?>
            </td>

            <!-- STATUS -->
            <td>
                <?php if($row['status'] == 'active') { ?>
                    <span class="status-active">Active</span>
                <?php } else { ?>
                    <span class="status-inactive">Inactive</span>
                <?php } ?>
            </td>

            <!-- ACTION -->
            <td>

                <a class="edit-btn"
                   href="edit_teacher.php?teacher_id=<?php echo $row['teacher_id']; ?>">
                    Edit
                </a>

                <a class="delete-btn"
                   href="?delete=<?php echo $row['teacher_id']; ?>"
                   onclick="return confirm('Delete teacher permanently?')">
                    Delete
                </a>

            </td>

        </tr>

    <?php
        }

    } else {
    ?>

        <tr>
            <td colspan="8" style="text-align:center; padding:36px;">
                No teachers found. To make them available add them.
            </td>
        </tr>

    <?php } ?>

    </tbody>
</table>
  </div>

  <!-- Optional: Dummy notice to ensure icons appear everywhere -->
  <div style="font-size:12px; text-align:center; margin-top:12px; color:#64748b;">
    <i class="fas fa-shield-alt"></i> Teacher management and assign classes
  </div>
</div>

</body>
</html>
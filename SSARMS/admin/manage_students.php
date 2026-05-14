<?php
session_start();
include '../db.php';

// CHECK ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_POST['add_student'])) {

    $name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $class_id = $_POST['class_id'];
    $dob = $_POST['date_of_birth'];
    $academic_year = date("Y");
    $phone = $_POST['phone'];
    $gender = $_POST['gender'];

    $insert_user = mysqli_query($conn, "
        INSERT INTO users (full_name, email, password, role, phone, gender)
        VALUES ('$name', '$email', '$password', 'student', '$phone', '$gender')
    ");

    if ($insert_user) {
        $user_id = mysqli_insert_id($conn);

        mysqli_query($conn, "
            INSERT INTO student (user_id, class_id, date_of_birth, academic_year)
            VALUES ('$user_id', '$class_id', '$dob', '$academic_year')
        ");

        // RETURN JSON ONLY
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
            echo json_encode(["status"=>"success"]);
            exit();
        }
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    mysqli_query($conn, "DELETE FROM student WHERE student_id='$id'");
}

$students = mysqli_query($conn, "
    SELECT s.student_id, u.full_name, u.email, u.phone, u.gender, c.class_name, s.academic_year
    FROM student s
    JOIN users u ON s.user_id = u.user_id
    JOIN class c ON s.class_id = c.class_id
    WHERE u.role = 'student'
");


$classes = mysqli_query($conn, "SELECT * FROM class");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>Manage Students</title>
  <!-- Fonts & Icons (minimal) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{
      background:#eef2ff;
      font-family:'Inter',sans-serif;
      padding:2rem 1.5rem;
      min-height:100vh;
    }
    .container{max-width:1280px;margin:0 auto;}
    /* header */
    .page-header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:1.8rem;}
    .title-section h2{
      font-size:1.8rem;font-weight:700;
      background:linear-gradient(135deg,#1e2a5e,#2c3e66);
      -webkit-background-clip:text;background-clip:text;color:transparent;
      display:flex;align-items:center;gap:10px;
    }
    .stat-badge{
      background:#fff;padding:6px 18px;border-radius:40px;font-weight:600;font-size:0.85rem;
      box-shadow:0 4px 12px rgba(0,0,0,0.03);border:1px solid rgba(59,130,246,0.2);
    }
    /* cards ultra clean */
    .card{
      background:white;border-radius:28px;margin-bottom:2rem;
      box-shadow:0 12px 30px -12px rgba(0,0,0,0.08);
      transition:0.2s;
    }
    .card-inner{padding:1.5rem 1.8rem;}
    @media (max-width:640px){.card-inner{padding:1.2rem;}}
    /* form grid */
    .form-grid{
      display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;
      align-items:end;
    }
    .input-group{display:flex;flex-direction:column;gap:0.4rem;}
    .input-group label{font-size:0.7rem;font-weight:600;text-transform:uppercase;color:#4b5563;}
    input,select{
      padding:0.65rem 0.9rem;border-radius:24px;border:1.5px solid #e2e8f0;
      font-size:0.85rem;transition:0.2s;outline:none;width:100%;
    }
    input:focus,select:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.2);}
    .btn-primary{
      background:#2563eb;border:none;padding:0.65rem 1rem;border-radius:40px;
      font-weight:600;color:white;cursor:pointer;display:flex;align-items:center;
      justify-content:center;gap:8px;width:100%;transition:0.2s;
    }
    .btn-primary:hover{background:#1d4ed8;transform:scale(0.97);}
    /* table styling */
    .table-wrapper{overflow-x:auto;border-radius:20px;}
    .student-table{width:100%;border-collapse:collapse;}
    .student-table th{
      text-align:left;padding:0.9rem 1rem;background:#f8fafc;
      font-size:0.7rem;font-weight:600;text-transform:uppercase;color:#334155;
      border-bottom:1.5px solid #e9eef3;
    }
    .student-table td{
      padding:0.85rem 1rem;border-bottom:1px solid #edf2f7;color:#1e293b;
      vertical-align:middle;
    }
    .student-table tr:hover td{background:#fefce8;}
    .badge-class{
      background:#e0f2fe;padding:4px 12px;border-radius:40px;font-size:0.7rem;
      font-weight:600;color:#0369a1;display:inline-block;
    }
    .year-badge{
      background:#e9f5ef;padding:4px 10px;border-radius:40px;font-size:0.7rem;
      font-weight:500;color:#2b7e3a;
    }
    .delete-btn{
      background:none;border:none;color:#ef4444;font-size:0.8rem;cursor:pointer;
      display:inline-flex;align-items:center;gap:6px;padding:6px 12px;
      border-radius:40px;background:rgba(239,68,68,0.08);transition:0.2s;
      font-weight:500;
    }
    .delete-btn:hover{background:#fee2e2;transform:scale(0.96);}
    .empty-row td{text-align:center;padding:2rem;color:#6c757d;font-style:italic;}
    .subnote{font-size:0.65rem;color:#475569;margin-top:0.8rem;text-align:center;}
    .flex-between{display:flex;justify-content:space-between;align-items:center;}
    /* toast & confirm animations */
    .toast-msg{
      position:fixed;bottom:24px;right:24px;background:#0f172ad9;color:white;
      padding:10px 22px;border-radius:60px;font-size:0.85rem;backdrop-filter:blur(4px);
      z-index:1000;animation:fadeInUp 0.2s ease;pointer-events:none;
    }
    @keyframes fadeInUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
    hr{margin:0.5rem 0 1rem;border:0;height:2px;background:linear-gradient(90deg,#cbd5e1,transparent);}
  </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>



<div class="container">
  <div class="page-header">
    <div class="title-section"><h2><i class="fas fa-graduation-cap"></i> Student<span style="color:#2563eb;">Flow</span></h2></div>
    <div class="stat-badge"><i class="fas fa-users"></i> <span id="studentCountSpan">0</span> enrolled</div>
  </div>

  <!-- ADD STUDENT CARD -->
  <div class="card">
    <div class="card-inner">
      <div class="flex-between" style="margin-bottom:1rem;">
        <h3 style="font-weight:600;font-size:1.2rem;"><i class="fas fa-user-plus" style="color:#3b82f6;"></i> Register student</h3>
        <i class="fas fa-id-card" style="color:#a0c4ff;"></i>
      </div>
      <form method="POST" id="addStudentForm">
        <div class="form-grid">
          <div class="input-group">
            
          <label><i class="far fa-user"></i> Full name</label><input type="text" name="full_name" placeholder="Student name" required></div>
          <div class="input-group"><label><i class="far fa-envelope"></i> Email</label><input type="email" name="email" placeholder="student@example.com" required></div>
          <div class="input-group"><label><i class="fas fa-lock"></i> Password</label><input type="password" name="password" placeholder="********" required></div>
          <div class="input-group"><label><i class="fas fa-calendar-alt"></i> Date of birth</label><input type="date" name="date_of_birth" required></div>
          <div class="input-group"><label><i class="fas fa-school"></i> Class</label>
            <select name="class_id" required>
              <option value="">-- Select Class --</option>
              <?php
              // Reset class pointer
              mysqli_data_seek($classes, 0);
              while($c = mysqli_fetch_assoc($classes)) echo '<option value="'.$c['class_id'].'">'.htmlspecialchars($c['class_name']).'</option>';
              ?>
            </select>
          </div>
          <div class="input-group"><label><i class="fas fa-phone"></i> Phone</label><input type="text" name="phone" placeholder="07XXXXXXXX" required></div>
          <div class="input-group"><label><i class="fas fa-venus-mars"></i> Gender</label>
            <select name="gender" required><option value="">Gender</option><option value="male">Male</option><option value="female">Female</option></select>
          </div>
          <div class="input-group"><label><i class="fas fa-calendar"></i> Academic Year</label><input type="number" name="academic_year" id="academicYear" readonly></div>
          <div class="input-group"><label>&nbsp;</label><button type="submit" name="add_student" class="btn-primary"><i class="fas fa-save"></i> Add student</button></div>
        </div>
      </form>
      <div class="subnote"><i class="fas fa-shield-alt"></i> Secure enrollment</div>
    </div>
  </div>

  <!-- STUDENT LIST CARD -->
  <div class="card">
    <div class="card-inner">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:1rem;">
        <i class="fas fa-table-list" style="color:#2c3e66;"></i>
        <h3 style="font-weight:600;">Enrolled students</h3>
      </div>
      <div class="table-wrapper">
        <table class="student-table" id="studentTable">
          <thead><tr><th>Full name</th><th>Email</th><th>Phone</th><th>Gender</th><th>Class</th><th>Academic Year</th><th style="text-align:center">Action</th></tr></thead>
          <tbody id="studentTableBody">
          <?php 
          if (isset($students) && mysqli_num_rows($students) > 0) {
              mysqli_data_seek($students, 0);
              while($row = mysqli_fetch_assoc($students)) {
                  echo '<tr>
                          <td>'.htmlspecialchars($row['full_name']).'</td>
                          <td>'.htmlspecialchars($row['email']).'</td>
                          <td>'.htmlspecialchars($row['phone']).'</td>
                          <td>'.htmlspecialchars($row['gender']).'</td>
                          <td><span class="badge-class">'.htmlspecialchars($row['class_name']).'</span></td>
                          <td><span class="year-badge">'.htmlspecialchars($row['academic_year']).'</span></td>
                          <td style="text-align:center"><button class="delete-btn" data-id="'.$row['student_id'].'" data-name="'.$row['full_name'].'"><i class="fas fa-trash-alt"></i> Delete</button></td>
                        </tr>';
              }
          } else {
              echo '<tr class="empty-row"><td colspan="7"><i class="fas fa-chalkboard-user"></i> No students yet. Add your first student ✨</td></tr>';
          }
          ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
(function(){

  const studentTableBody = document.getElementById('studentTableBody');
  const studentCountSpan = document.getElementById('studentCountSpan');

  const yearInput = document.getElementById('academicYear');
      if(yearInput){
      yearInput.value = new Date().getFullYear();
      }

  // update count
  function updateCount(){
    const rows = document.querySelectorAll('#studentTableBody tr:not(.empty-row)');
    if(studentCountSpan) studentCountSpan.innerText = rows.length;
  }
  updateCount();

  // toast
  function showToast(msg, isError=false){
    let toast = document.querySelector('.toast-msg');
    if(toast) toast.remove();

    const div = document.createElement('div');
    div.className = 'toast-msg';
    div.innerHTML = `<i class="fas ${isError ? 'fa-exclamation-triangle' : 'fa-check-circle'}"></i> ${msg}`;
    document.body.appendChild(div);

    setTimeout(()=>{
      div.style.opacity='0';
      setTimeout(()=>div.remove(),300);
    },2500);
  }

  // confirm modal
  function confirmModal(title, message, onConfirm){
    const overlay = document.createElement('div');
    overlay.style.cssText = `
      position:fixed;top:0;left:0;width:100%;height:100%;
      background:rgba(0,0,0,0.4);display:flex;
      align-items:center;justify-content:center;z-index:3000;
    `;

    const modal = document.createElement('div');
    modal.style.cssText = `
      background:white;border-radius:28px;
      max-width:360px;width:90%;padding:1.5rem;text-align:center;
    `;

    modal.innerHTML = `
      <i class="fas fa-trash-alt" style="font-size:2.5rem;color:#ef4444;"></i>
      <h3 style="margin:10px 0;">${title}</h3>
      <p style="margin-bottom:1rem;">${message}</p>
      <button id="okBtn" style="background:#ef4444;color:white;padding:8px 20px;border:none;border-radius:20px;">Delete</button>
      <button id="cancelBtn" style="margin-left:10px;padding:8px 20px;border:none;border-radius:20px;">Cancel</button>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    modal.querySelector('#cancelBtn').onclick = ()=> overlay.remove();
    modal.querySelector('#okBtn').onclick = ()=>{
      overlay.remove();
      onConfirm();
    };
  }

  // ADD STUDENT (still reload for now)
  const addForm = document.getElementById('addStudentForm');
  if(addForm){
    addForm.addEventListener('submit', async (e)=>{
      e.preventDefault();

      const formData = new FormData(addForm);
      formData.append('add_student', '1');

      

      try{
        await fetch(window.location.href, {
          method:'POST',
          body:formData,
          headers:{'X-Requested-With':'XMLHttpRequest'}
        });

        showToast(' Student added');
        window.location.reload(); // (next step tutaondoa hii)

      }catch{
        showToast(' Add student failed', true);
      }
    });
  }

  // DELETE student (NO RELOAD)
  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', (e)=>{
      e.preventDefault();

      const studentId = btn.getAttribute('data-id');
      const studentName = btn.getAttribute('data-name');

      confirmModal(`Remove ${studentName}?`, `This action is permanent.`, ()=>{

        fetch(`${window.location.pathname}?delete=${studentId}`, {
          method:'GET',
          headers:{'X-Requested-With':'XMLHttpRequest'}
        })
        .then(res => res.text())
        .then(()=>{

          showToast(`${studentName} deleted`);

          // remove row instantly
          const row = btn.closest('tr');
          if(row) row.remove();

          updateCount();

          // if empty
          if(document.querySelectorAll('#studentTableBody tr').length === 0){
            studentTableBody.innerHTML = `
              <tr class="empty-row">
                <td colspan="7">No students yet ✨</td>
              </tr>
            `;
          }

        })
        .catch(()=>{
          showToast('Delete error', true);
        });

      });
    });
  });

  // animation
  const rows = document.querySelectorAll('#studentTableBody tr:not(.empty-row)');
  rows.forEach((r,i)=>{
    r.style.animation = `fadeInUp 0.2s ease ${i*0.03}s backwards`;
  });

})();
</script>
</body>
</html>

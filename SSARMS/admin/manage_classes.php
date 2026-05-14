<?php
session_start();
include '../db.php';

// CHECK ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// ================= HANDLE AJAX & FORM REQUESTS =================
// ADD CLASS (AJAX or normal POST)
if (isset($_POST['add_class'])) {
    $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
    $stream = mysqli_real_escape_string($conn, $_POST['stream']);
    
    $result = mysqli_query($conn, "INSERT INTO class (class_name, stream) VALUES ('$class_name', '$stream')");
    
    // If AJAX request, return JSON response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if ($result) {
            $new_id = mysqli_insert_id($conn);
            echo json_encode(['success' => true, 'class_id' => $new_id, 'class_name' => $class_name, 'stream' => $stream]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        exit();
    }
}

// DELETE CLASS (AJAX or normal GET)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $result = mysqli_query($conn, "DELETE FROM class WHERE class_id='$id'");
    
    // If AJAX request, return JSON response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        if ($result && mysqli_affected_rows($conn) > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Class not found']);
        }
        exit();
    }
    
    // Redirect after normal delete
    header("Location: manage_classes.php");
    exit();
}

// ================= FETCH CLASSES =================
$classes = mysqli_query($conn, "SELECT * FROM class ORDER BY class_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSARMS - Manage Classes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* SHORT, CLEAN CSS - NO ANIMATIONS */
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:#f1f5f9;font-family:'Inter',sans-serif;padding:40px 24px;}
        .container{max-width:1100px;margin:0 auto;}
        .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;flex-wrap:wrap;gap:16px;}
        .header h1{font-size:28px;font-weight:700;color:#0f172a;}
        .header h1 i{color:#3b82f6;margin-right:12px;}
        .stats{background:white;padding:8px 20px;border-radius:40px;font-size:14px;font-weight:600;color:#1e293b;border:1px solid #e2e8f0;}
        .stats i{color:#3b82f6;margin-right:8px;}
        .card{background:white;border-radius:20px;border:1px solid #e2e8f0;margin-bottom:32px;}
        .card-header{padding:20px 24px;border-bottom:1px solid #eef2ff;display:flex;align-items:center;gap:12px;}
        .card-header i{font-size:20px;color:#3b82f6;}
        .card-header h2{font-size:18px;font-weight:600;color:#0f172a;}
        .card-body{padding:24px;}
        .form-group{display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end;}
        .input-wrapper{flex:1;min-width:200px;}
        .input-wrapper label{display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;margin-bottom:6px;}
        .input-wrapper label i{margin-right:6px;}
        .input-wrapper input{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:'Inter',sans-serif;background:white;}
        .input-wrapper input:focus{border-color:#3b82f6;outline:none;}
        .btn-primary{background:#3b82f6;border:none;padding:10px 24px;border-radius:12px;font-weight:600;font-size:14px;color:white;cursor:pointer;display:inline-flex;align-items:center;gap:8px;font-family:'Inter',sans-serif;}
        .btn-primary:hover{background:#2563eb;}
        .table-wrapper{overflow-x:auto;}
        table{width:100%;border-collapse:collapse;}
        th{text-align:left;padding:14px 16px;background:#f8fafc;font-weight:600;font-size:13px;color:#475569;border-bottom:1px solid #e2e8f0;}
        td{padding:14px 16px;border-bottom:1px solid #f1f5f9;color:#1e293b;font-size:14px;font-weight:500;}
        .class-badge{background:#dbeafe;padding:4px 12px;border-radius:20px;font-size:13px;font-weight:500;color:#1e40af;display:inline-block;}
        .stream-text{color:#475569;}
        .delete-btn{background:none;border:none;color:#ef4444;cursor:pointer;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;}
        .delete-btn:hover{background:#fef2f2;}
        .empty-row td{text-align:center;padding:48px;color:#94a3b8;}
        .empty-row i{font-size:40px;margin-bottom:12px;display:block;}
        /* TOAST - NO ANIMATION, JUST SIMPLE */
        .toast{position:fixed;bottom:24px;right:24px;background:#1e293b;color:white;padding:12px 20px;border-radius:12px;font-size:14px;font-weight:500;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.15);display:flex;align-items:center;gap:10px;}
        .toast.success{background:#10b981;}
        .toast.error{background:#ef4444;}
        /* MODAL - NO OVERLAY ANIMATION */
        .modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:2000;}
        .modal{background:white;border-radius:20px;padding:24px;max-width:380px;width:90%;text-align:center;}
        .modal i{font-size:48px;color:#ef4444;margin-bottom:12px;}
        .modal h3{font-size:20px;margin-bottom:8px;color:#0f172a;}
        .modal p{color:#475569;margin-bottom:24px;}
        .modal-buttons{display:flex;gap:12px;justify-content:center;}
        .modal-btn{padding:8px 20px;border-radius:10px;font-weight:600;cursor:pointer;border:none;font-family:'Inter',sans-serif;}
        .modal-btn.cancel{background:#e2e8f0;color:#475569;}
        .modal-btn.confirm{background:#ef4444;color:white;}
        @media (max-width:640px){body{padding:20px 16px;}.card-body{padding:16px;}.form-group{flex-direction:column;}.input-wrapper{width:100%;}.btn-primary{width:100%;justify-content:center;}}
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>



<div class="container">
    <div class="header">
        <h1><i class="fas fa-chalkboard"></i> Manage Classes</h1>
        <div class="stats">
            <i class="fas fa-layer-group"></i> <span id="classCount">0</span> classes
        </div>
    </div>

    <!-- Add Class Card -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus-circle"></i>
            <h2>Add New Class</h2>
        </div>
        <div class="card-body">
            <form id="addClassForm">
                <div class="form-group">
                    <div class="input-wrapper">
                        <label><i class="fas fa-school"></i> Class Name </label>
                        <input type="text" name="class_name" id="className" placeholder="e.g., Form 1, Grade 10" required>
                    </div>
                    <div class="input-wrapper">
                        <label><i class="fas fa-code-branch"></i> Stream (Optional)</label>
                        <input type="text" name="stream" id="stream" placeholder="e.g., A, B, Science">
                    </div>
                    <div class="input-wrapper">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Add Class</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Classes List Card -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-table-list"></i>
            <h2>Class Directory</h2>
        </div>
        <div class="card-body">
            <div class="table-wrapper">
                <table id="classTable">
                    <thead>
                        <tr><th>ID</th><th>Class Name</th><th style="width:100px">Action</th></tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if (mysqli_num_rows($classes) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($classes)): ?>
                                <tr data-id="<?php echo $row['class_id']; ?>">
                                    <td><?php echo htmlspecialchars($row['class_id']); ?></td>
    <td>
        <span class="class-badge">
         <?php 
            echo htmlspecialchars($row['class_name']);

            if (!empty($row['stream'])) {
                echo ' ' . htmlspecialchars($row['stream']);
            }
         ?>
        </span>
    </td>
                                    <td><button class="delete-btn" data-id="<?php echo $row['class_id']; ?>" data-name="<?php echo htmlspecialchars($row['class_name'] . ($row['stream'] ? ' ' . $row['stream'] : '')); ?>"><i class="fas fa-trash-alt"></i> Delete</button></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="empty-row"><td colspan="3"><i class="fas fa-door-open"></i> No classes yet. Add your first class above.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// SHORT & FUNCTIONAL JS - NO EXCESS ANIMATIONS

function showToast(msg, type='info'){
    let old = document.querySelector('.toast');
    if(old) old.remove();
    let t = document.createElement('div');
    t.className = 'toast ' + (type==='success'?'success':type==='error'?'error':'');
    t.innerHTML = (type==='success'?'<i class="fas fa-check-circle"></i>':type==='error'?'<i class="fas fa-exclamation-circle"></i>':'<i class="fas fa-info-circle"></i>') + ' ' + msg;
    document.body.appendChild(t);
    setTimeout(()=>{if(t) t.remove();}, 2500);
}

function updateCount(){
    let rows = document.querySelectorAll('#tableBody tr:not(.empty-row)');
    document.getElementById('classCount').innerText = rows.length;
}

function escapeHtml(str){
    if(!str) return '';
    return str.replace(/[&<>]/g, function(m){
        if(m==='&') return '&amp;';
        if(m==='<') return '&lt;';
        if(m==='>') return '&gt;';
        return m;
    });
}

// delete with confirmation modal
function deleteClass(cid, cname, btnEl){
    let ov = document.createElement('div');
    ov.className = 'modal-overlay';
    let mod = document.createElement('div');
    mod.className = 'modal';
    mod.innerHTML = `<i class="fas fa-trash-alt"></i><h3>Delete Class?</h3><p>Are you sure you want to delete "<strong>${escapeHtml(cname)}</strong>"? This cannot be undone.</p><div class="modal-buttons"><button class="modal-btn cancel" id="modalCancel">Cancel</button><button class="modal-btn confirm" id="modalConfirm">Delete</button></div>`;
    ov.appendChild(mod);
    document.body.appendChild(ov);
    
    let cancelBtn = mod.querySelector('#modalCancel');
    let confirmBtn = mod.querySelector('#modalConfirm');
    cancelBtn.onclick = ()=> ov.remove();
    confirmBtn.onclick = ()=>{
        ov.remove();
        fetch(window.location.pathname + '?delete=' + cid, {
            method: 'GET',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(r=>r.json())
        .then(data=>{
            if(data.success){
                let row = btnEl.closest('tr');
                row.remove();
                let remaining = document.querySelectorAll('#tableBody tr');
                if(remaining.length===0 || (remaining.length===1 && remaining[0].classList.contains('empty-row'))){
                    document.getElementById('tableBody').innerHTML = `<tr class="empty-row"><td colspan="4"><i class="fas fa-door-open"></i> No classes yet. Add your first class above.</td></tr>`;
                }
                updateCount();
                showToast('Class deleted', 'success');
            } else {
                showToast('Error deleting class', 'error');
            }
        })
        .catch(()=> showToast('Network error', 'error'));
    };
    ov.onclick = (e)=>{ if(e.target===ov) ov.remove(); };
}

// handle add class ajax
document.getElementById('addClassForm').addEventListener('submit', function(e){
    e.preventDefault();
    let className = document.getElementById('className').value.trim();
    let stream = document.getElementById('stream').value.trim();
    if(!className){
        showToast('Class name required', 'error');
        return;
    }
    let fd = new FormData();
    fd.append('add_class', '1');
    fd.append('class_name', className);
    fd.append('stream', stream);
    fetch(window.location.pathname, {
        method: 'POST',
        body: fd,
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r=>r.json())
    .then(data=>{
        if(data.success){
            let emptyRow = document.querySelector('#tableBody .empty-row');
            if(emptyRow) emptyRow.remove();
            let tbody = document.getElementById('tableBody');
            let newRow = document.createElement('tr');
            newRow.setAttribute('data-id', data.class_id);
            let streamDisplay = data.stream ? '<span class="stream-text">'+escapeHtml(data.stream)+'</span>' : '<span style="color:#94a3b8;">—</span>';
            newRow.innerHTML = `
                <td>${data.class_id}</td>
                <td><span class="class-badge">${escapeHtml(data.class_name)}</span></td>
                <td>${streamDisplay}</td>
                <td><button class="delete-btn" data-id="${data.class_id}" data-name="${escapeHtml(data.class_name + (data.stream ? ' ' + data.stream : ''))}"><i class="fas fa-trash-alt"></i> Delete</button></td>
            `;
            tbody.appendChild(newRow);
            let newBtn = newRow.querySelector('.delete-btn');
            newBtn.addEventListener('click', function(){ deleteClass(this.getAttribute('data-id'), this.getAttribute('data-name'), this); });
            document.getElementById('addClassForm').reset();
            updateCount();
            showToast('Class added', 'success');
        } else {
            showToast('Error adding class', 'error');
        }
    })
    .catch(()=> showToast('Network error', 'error'));
});

// attach delete events to existing delete buttons
document.querySelectorAll('.delete-btn').forEach(btn=>{
    btn.addEventListener('click', function(){ deleteClass(this.getAttribute('data-id'), this.getAttribute('data-name'), this); });
});

updateCount();
</script>
</body>
</html>
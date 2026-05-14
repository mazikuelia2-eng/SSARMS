<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ================= UPDATE PROFILE ================= */
if (isset($_POST['update_profile'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    mysqli_query($conn, "
        UPDATE users 
        SET email='$email',
            phone='$phone'
        WHERE user_id='$user_id'
    ");

    $msg = "Profile updated successfully!";
}

/* ================= CHANGE PASSWORD ================= */
if (isset($_POST['change_password'])) {

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $get = mysqli_query($conn, "
        SELECT password 
        FROM users 
        WHERE user_id='$user_id'
    ");

    $row = mysqli_fetch_assoc($get);

    if (password_verify($current, $row['password'])) {

        if ($new === $confirm) {

            $hashed = password_hash($new, PASSWORD_DEFAULT);

            mysqli_query($conn, "
                UPDATE users 
                SET password='$hashed'
                WHERE user_id='$user_id'
            ");

            $msg2 = "Password changed successfully!";
        } else {
            $msg2 = "New passwords do not match!";
        }

    } else {
        $msg2 = "Current password is incorrect!";
    }
}

/* ================= FETCH USER ================= */
$user_query = mysqli_query($conn, "
    SELECT full_name, email, phone, gender
    FROM users
    WHERE user_id='$user_id'
");

$user = mysqli_fetch_assoc($user_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Profile</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
body{
    font-family: Arial;
    background:#eef2ff;
    margin:0;
    padding:20px;
}

.container{
    max-width:600px;
    margin:auto;
}

.card{
    background:white;
    padding:25px;
    border-radius:15px;
    margin-bottom:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

h2{
    text-align:center;
    color:#1e293b;
    margin-bottom:10px;
}

h3{
    margin-bottom:15px;
    color:#334155;
}

.input-group{
    margin-bottom:15px;
    display:flex;
    flex-direction:column;
}

label{
    font-size:13px;
    font-weight:bold;
    margin-bottom:5px;
    color:#475569;
}

input{
    padding:12px;
    border:1px solid #cbd5e1;
    border-radius:10px;
    outline:none;
}

input:focus{
    border-color:#2563eb;
}

button{
    background:#2563eb;
    color:white;
    border:none;
    padding:12px;
    border-radius:10px;
    cursor:pointer;
    width:100%;
    font-weight:bold;
}

button:hover{
    background:#1d4ed8;
}

.info{
    text-align:center;
    margin-bottom:20px;
    color:#64748b;
    font-size:14px;
}

.alert{
    padding:10px;
    margin-bottom:15px;
    border-radius:8px;
    font-size:14px;
}

.success{
    background:#dcfce7;
    color:#166534;
}

.error{
    background:#fee2e2;
    color:#991b1b;
}
</style>
</head>

<body>

<?php include 'auth.php'; ?>
<?php include '.php'; ?>

<div class="container">

<h2><i class="fas fa-user-circle"></i> My Profile</h2>

<div class="info">
    Welcome, <?php echo htmlspecialchars($user['full_name']); ?>
</div>

<!-- SUCCESS / ERROR MESSAGES -->
<?php if(isset($msg)): ?>
    <div class="alert success">
        <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
    </div>
<?php endif; ?>

<?php if(isset($msg2)): ?>
    <div class="alert error">
        <i class="fas fa-exclamation-circle"></i> <?php echo $msg2; ?>
    </div>
<?php endif; ?>

<!-- PROFILE UPDATE -->
<div class="card">

<h3><i class="fas fa-edit"></i> Update Profile</h3>

<form method="POST">

    <div class="input-group">
        <label>Email</label>
        <input type="email" name="email"
        value="<?php echo htmlspecialchars($user['email']); ?>"
        required>
    </div>

    <div class="input-group">
        <label>Phone</label>
        <input type="text" name="phone"
        value="<?php echo htmlspecialchars($user['phone']); ?>">
    </div>

    <button type="submit" name="update_profile">
        <i class="fas fa-save"></i> Update Profile
    </button>

</form>

</div>

<!-- PASSWORD CHANGE -->
<div class="card">

<h3><i class="fas fa-lock"></i> Change Password</h3>

<form method="POST">

    <div class="input-group">
        <label>Current Password</label>
        <input type="password" name="current_password" required>
    </div>

    <div class="input-group">
        <label>New Password</label>
        <input type="password" name="new_password" required>
    </div>

    <div class="input-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>
    </div>

    <button type="submit" name="change_password">
        <i class="fas fa-shield-alt"></i> Change Password
    </button>

</form>

</div>

</div>

</body>
</html>
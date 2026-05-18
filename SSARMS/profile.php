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

    /* -------- PROFILE IMAGE UPLOAD -------- */
    $profile_pic = null;

    if (!empty($_FILES['profile_pic']['name'])) {

        $target_dir = "uploads/";

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $file_name;

        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);

        $profile_pic = $file_name;

        mysqli_query($conn, "
            UPDATE users 
            SET email='$email',
                phone='$phone',
                profile_pic='$profile_pic'
            WHERE user_id='$user_id'
        ");

    } else {

        mysqli_query($conn, "
            UPDATE users 
            SET email='$email',
                phone='$phone'
            WHERE user_id='$user_id'
        ");
    }

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
    SELECT full_name, email, phone, gender, profile_pic
    FROM users
    WHERE user_id='$user_id'
");

$user = mysqli_fetch_assoc($user_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile</title>

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

h2,h3{text-align:center;}

.input-group{
    margin-bottom:15px;
}

label{
    font-size:13px;
    font-weight:bold;
    display:block;
    margin-bottom:5px;
}

input{
    width:100%;
    padding:12px;
    border:1px solid #cbd5e1;
    border-radius:10px;
}

button{
    width:100%;
    padding:12px;
    border:none;
    background:#111;
    color:white;
    border-radius:10px;
    cursor:pointer;
}

button:hover{
    background:#333;
}

/* PROFILE PIC */
.profile-box{
    text-align:center;
    margin-bottom:20px;
}

.profile-box img{
    width:110px;
    height:110px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #111;
}
</style>
</head>

<body>

<div class="container">

<h2>My Profile</h2>

<?php if(isset($msg)): ?>
    <p style="color:green;text-align:center;"><?= $msg ?></p>
<?php endif; ?>

<?php if(isset($msg2)): ?>
    <p style="color:red;text-align:center;"><?= $msg2 ?></p>
<?php endif; ?>

<!-- PROFILE PIC -->
<div class="card">

<div class="profile-box">
    <img src="uploads/profiles/<?= $user['profile_pic'] ?? 'default.png'; ?>">
</div>

<form method="POST" enctype="multipart/form-data">

    <div class="input-group">
        <label>Change Profile Picture</label>
        <input type="file" name="profile_pic" accept="image/*">
    </div>

    <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= $user['email']; ?>">
    </div>

    <div class="input-group">
        <label>Phone</label>
        <input type="text" name="phone" value="<?= $user['phone']; ?>">
    </div>

    <button type="submit" name="update_profile">
        Update Profile
    </button>

</form>

</div>

<!-- PASSWORD -->
<div class="card">

<h3>Change Password</h3>

<form method="POST">

    <input type="password" name="current_password" placeholder="Current Password" required><br><br>
    <input type="password" name="new_password" placeholder="New Password" required><br><br>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required><br><br>

    <button type="submit" name="change_password">
        Change Password
    </button>

</form>

</div>

</div>

</body>
</html>
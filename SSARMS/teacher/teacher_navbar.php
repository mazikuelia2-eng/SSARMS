<?php
$user_id = $_SESSION['user_id'];

$teacher = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT u.full_name
    FROM users u
    WHERE u.user_id = '$user_id'
"));
?>

<style>
.teacher-topbar{
    background:white;
    padding:12px 20px;
    border-radius:12px;
    margin-bottom:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    margin-left:260px;
    width: calc(100% - 260px);
}

.teacher-topbar h2{
    font-size:18px;
    color:#0f172a;
}

.teacher-user{
    display:flex;
    align-items:center;
    gap:10px;
}

.teacher-user img{
    width:40px;
    height:40px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #2563eb;
}

.teacher-user a{
    text-decoration:none;
    background:#2563eb;
    color:white;
    padding:7px 12px;
    border-radius:8px;
    font-size:13px;
}
</style>

<div class="teacher-topbar">

    <h2>
        Welcome, <?= htmlspecialchars($teacher['full_name']); ?>
    </h2>

    <div class="teacher-user">

        <img src="../uploads/default.png">

        <a href="../profile.php">My Profile</a>

    </div>

</div>
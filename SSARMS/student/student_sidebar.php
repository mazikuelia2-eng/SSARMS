
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>


<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

.sidebar{
    position:fixed;
    left:0;
    top:0;
    width:260px;
    height:100vh;
    background:#0b1220;
    display:flex;
    flex-direction:column;
    padding:20px 15px;
    color:white;
}

/* PROFILE */
.profile{
    text-align:center;
    padding-bottom:15px;
    margin-bottom:15px;
    border-bottom:1px solid rgba(255,255,255,0.08);
}

.profile img{
    width:75px;
    height:75px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid #fff;
    margin-bottom:10px;
}

.profile h4{
    font-size:14px;
    color:#cbd5e1;
    font-weight:normal;
}

.profile p{
    font-size:15px;
    font-weight:bold;
    color:#fff;
    margin-top:3px;
}

/* MENU */
.sidebar a{
    display:flex;
    align-items:center;
    gap:10px;
    padding:12px 14px;
    margin-bottom:6px;
    border-radius:10px;
    text-decoration:none;
    color:#cbd5e1;
    font-size:14px;
    transition:0.2s;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.08);
    color:#fff;
    transform:translateX(3px);
}

.sidebar a.active{
    background:#fff;
    color:#0b1220;
    font-weight:bold;
}

/* BOTTOM */
.bottom{
    margin-top:auto;
}

.logout{
    color:#ff6b6b !important;
}
.logout:hover{
    background:rgba(255,0,0,0.1);
}
</style>
<body>


<div class="sidebar">

    <!-- PROFILE -->
    <div class="profile">
        <img src="../uploads/<?= htmlspecialchars($profile_pic); ?>" alt="Profile">
        <h4>Welcome</h4>
        <p><?= htmlspecialchars($student['full_name']); ?></p>
    </div>

    <!-- MENU -->
    <a href="student_dashboard.php" class="active">
        <i class="fas fa-chart-line"></i> Dashboard
    </a>

    <a href="my_subjects.php">
        <i class="fas fa-book"></i> My Subjects
    </a>

    <a href="my_result.php">
        <i class="fas fa-file-alt"></i> Results
    </a>

    <a href="my_classes.php">
        <i class="fas fa-school"></i> Classes
    </a>

    <a href="perfomance.php">
        <i class="fas fa-chart-pie"></i> Performance
    </a>

    <!-- BOTTOM -->
    <div class="bottom">

        <a href="../profile.php">
            <i class="fas fa-user"></i> Profile
        </a>

        <a href="../auth/logout.php" class="logout">
            <i class="fas fa-right-from-bracket"></i> Logout
        </a>

    </div>

</div>
</body>
</html>
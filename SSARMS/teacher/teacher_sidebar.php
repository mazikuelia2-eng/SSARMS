<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Sidebar</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>

body {
    margin: 0;
    font-family: Arial;
    background: #f1f5f9;
}

/* SIDEBAR */
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 250px;
    height: 100vh;
    background: #1e293b;
    padding: 20px;
    color: white;
}

/* TITLE */
.sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 20px;
    border-bottom: 1px solid #334155;
    padding-bottom: 10px;
}

/* LINKS */
.sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    margin-bottom: 10px;
    color: #cbd5e1;
    text-decoration: none;
    border-radius: 8px;
    transition: 0.3s;
    font-size: 15px;
}

.sidebar a:hover {
    background: #334155;
    color: white;
}

/* LOGOUT STYLE */
.logout {
    margin-top: 20px;
    color: #ef4444 !important;
}

.logout:hover {
    background: #7f1d1d;
    color: white !important;
}

/* CONTENT SHIFT */
.content {
    margin-left: 260px;
    padding: 20px;
}

</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <h2>
        <i class="fas fa-user-graduate"></i>
        Teacher Panel
    </h2>

    <a href="teacher_dashboard.php">
        <i class="fas fa-house"></i>
        Dashboard
    </a>

    <a href="my_classes.php">
        <i class="fas fa-school"></i>
        My Classes
    </a>

    <a href="enter_marks.php">
        <i class="fas fa-pen-to-square"></i>
        Enter Marks
    </a>

    <a href="../profile.php">
        <i class="fas fa-user"></i>
        Profile
    </a>

    <a href="../auth/logout.php" class="logout">
        <i class="fas fa-right-from-bracket"></i>
        Logout
    </a>

</div>


</body>
</html>
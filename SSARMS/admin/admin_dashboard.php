<?php
session_start();
include '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch counts$students = 0;
$teachers = 0;
$subjects = 0;

// students
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM student");
if ($result) {
    $students = mysqli_fetch_assoc($result)['total'];
}

// teachers
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM teacher");
if ($result) {
    $teachers = mysqli_fetch_assoc($result)['total'];
}

// subjects
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM subject");
if ($result) {
    $subjects = mysqli_fetch_assoc($result)['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <link rel="stylesheet" href="css/all.min.css">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SSARMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Inter', system-ui, Arial, sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
        }

    
        .main {
            margin-left: 260px;
            padding: 30px 35px;
        }

        .main h1 {
            font-size: 32px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 28px;
            border-left: 4px solid #2c6e9e;
            padding-left: 18px;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 22px 24px;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        .card h3 {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .card p {
            font-size: 42px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }

        /* Recent activity section */
        .recent-section {
            margin-top: 30px;
        }

        .recent-section h2 {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 18px;
        }

        .activity-list {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            border-bottom: 1px solid #eef2f6;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            background: #eef2ff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
            font-size: 18px;
        }

        .activity-details {
            flex: 1;
        }

        .activity-text {
            font-weight: 500;
            color: #1e293b;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .activity-time {
            font-size: 11px;
            color: #94a3b8;
        }

        /* Mobile responsive */
        .menu-toggle {
            display: none;
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 101;
            background: #1e2a3a;
            color: white;
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.2s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main {
                margin-left: 0;
                padding: 20px 18px;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .card p {
                font-size: 36px;
            }
            
            .main h1 {
                font-size: 26px;
                margin-top: 10px;
            }
        }

        /* Scrollbar simple */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #e2e8f0;
        }
        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>


<button class="menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')">☰</button>



<div class="main">
    <h1>Welcome Admin</h1>

    <div class="stats-container">
        <div class="card">
            <h3>Total Students</h3>
            <p><?php echo isset($students) ? number_format($students) : '0'; ?></p>
        </div>

        <div class="card">
            <h3>Total Teachers</h3>
            <p><?php echo isset($teachers) ? number_format($teachers) : '0'; ?></p>
        </div>

        <div class="card">
            <h3>Total Subjects</h3>
            <p><?php echo isset($subjects) ? number_format($subjects) : '0'; ?></p>
        </div>
    </div>

  
</div>

<script>
    // Close sidebar on mobile when clicking a link
    document.querySelectorAll('.sidebar a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                document.querySelector('.sidebar').classList.remove('active');
            }
        });
    });
    
    // Highlight current page link
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.sidebar a').forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'admin/admin_dashboard.php')) {
            link.classList.add('active');
        }
    });
    
    // Close sidebar when clicking outside on mobile (optional)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.querySelector('.menu-toggle');
            if (sidebar.classList.contains('active') && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
</script>

</body>
</html>
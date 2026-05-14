<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sidebar - SSARMS</title>
    <!-- Font Awesome for simple icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
        }

        /* SIDEBAR - clean, no animations */
        .sidebar {
            width: 260px;
            height: 100vh;
            background: #0f172a;
            position: fixed;
            top: 0;
            left: 0;
            padding: 24px 16px;
            overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0,0,0,0.05);
        }

        /* TITLE with icon */
        .sidebar h2 {
            color: white;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 32px;
            text-align: center;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-bottom: 1px solid #334155;
            padding-bottom: 16px;
        }
        .sidebar h2 i {
            color: #3b82f6;
            font-size: 22px;
        }

        /* LINKS - icon + text, clean spacing */
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #cbd5e1;
            text-decoration: none;
            padding: 12px 14px;
            margin-bottom: 8px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            transition: none; /* no animation */
        }

        /* Hover - simple color change, no transition effects */
        .sidebar a:hover {
            background: #1e293b;
            color: white;
        }

        /* Active link style (optional, can be used with PHP current page) */
        .sidebar a.active {
            background: #3b82f6;
            color: white;
        }

        .sidebar a i {
            width: 22px;
            font-size: 16px;
            text-align: center;
        }

        /* Logout link different subtle styling */
        .sidebar a:last-child {
            margin-top: 20px;
            border-top: 1px solid #334155;
            padding-top: 16px;
            border-radius: 0;
        }
        .sidebar a:last-child:hover {
            background: transparent;
            color: #f87171;
        }
        .sidebar a:last-child i {
            color: #f87171;
        }

        /* simple scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: #1e293b;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }
    </style>
</head>
<body>


<div class="sidebar">
    <h2>
        <i class="fas fa-school"></i> 
        SSARMS Admin
    </h2>

    <a href="admin_dashboard.php">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>

    <a href="manage_students.php">
        <i class="fas fa-user-graduate"></i> Manage Students
    </a>

    <a href="manage_teachers.php">
        <i class="fas fa-chalkboard-user"></i> Manage Teachers
    </a>

    <a href="manage_classes.php">
        <i class="fas fa-layer-group"></i> Manage Classes
    </a>

    <a href="manage_subjects.php">
        <i class="fas fa-book-open"></i> Manage Subjects
    </a>

    <a href="../auth/logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<!-- Simple script to highlight current page (no animations, just pure logic) -->
<script>
    (function() {
        let currentPage = window.location.pathname.split('/').pop();
        if(currentPage === '' || currentPage === 'admin_dashboard.php') currentPage = 'admin_dashboard.php';
        let links = document.querySelectorAll('.sidebar a');
        for(let link of links) {
            let href = link.getAttribute('href');
            if(href === currentPage) {
                link.classList.add('active');
                break;
            }
        }
    })();
</script>

</body>
</html>
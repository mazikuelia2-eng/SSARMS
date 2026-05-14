<?php
session_start();
include '../db.php';

// AUTH CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ===============================
   GET STUDENT INFO (FIXED LOGIC)
================================ */
$student_query = mysqli_query($conn, "
    SELECT s.student_id, u.full_name, c.class_name
    FROM student s
    JOIN users u ON s.user_id = u.user_id
    JOIN class c ON s.class_id = c.class_id
    WHERE s.user_id = '$user_id'
");

$student = mysqli_fetch_assoc($student_query);

if (!$student) {
    die("Student record not found. Check database structure.");
}

$student_id = $student['student_id'];

/* ===============================
   GET LATEST RESULT
================================ */
$result_query = mysqli_query($conn, "
    SELECT *
    FROM result_summary
    WHERE student_id = '$student_id'
    ORDER BY term DESC
    LIMIT 1
");

$result = mysqli_fetch_assoc($result_query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Student Dashboard | Academic Portal</title>
    <!-- Google Fonts + Simple Icons (using Font Awesome for clean icons, but lightweight) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #0f172a;
            line-height: 1.5;
        }

        /* ---------- SIDEBAR (simple, crisp) ---------- */
        .sidebar {
            width: 260px;
            height: 100vh;
            background: linear-gradient(145deg, #0f172a 0%, #0a0f1f 100%);
            position: fixed;
            left: 0;
            top: 0;
            padding: 32px 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.05);
            z-index: 20;
            overflow-y: auto;
        }

        .sidebar h3 {
            color: #ffffff;
            font-weight: 600;
            font-size: 1.4rem;
            letter-spacing: -0.2px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 3px solid #3b82f6;
            padding-left: 14px;
        }

        .sidebar h3 i {
            color: #3b82f6;
            font-size: 1.5rem;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            margin-bottom: 6px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 14px;
            transition: all 0.2s ease;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .sidebar a i {
            width: 24px;
            font-size: 1.1rem;
            text-align: center;
        }

        .sidebar a:hover {
            background: rgba(59, 130, 246, 0.12);
            color: white;
            transform: translateX(2px);
        }

        /* active simulation (visual hint) */
        .sidebar a:first-of-type {
            background: rgba(59, 130, 246, 0.2);
            color: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .sidebar a:last-child {
            margin-top: auto;
            margin-bottom: 0;
            color: #f87171;
        }

        .sidebar a:last-child:hover {
            background: rgba(248, 113, 113, 0.15);
            color: #ffb4b4;
        }

        /* ---------- TOPBAR (clean, minimal) ---------- */
        .topbar {
            position: fixed;
            left: 260px;
            right: 0;
            top: 0;
            height: 70px;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(2px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 32px;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            z-index: 15;
        }

        .topbar .info {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .badge {
            background: #f1f5f9;
            color: #1e293b;
            padding: 6px 16px;
            border-radius: 40px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            letter-spacing: -0.2px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            border: 1px solid #e2e8f0;
        }

        .badge i {
            color: #3b82f6;
            font-size: 0.9rem;
        }

        .logout-btn {
            background: #fee2e2;
            color: #b91c1c;
            padding: 8px 18px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #fecaca;
        }

        .logout-btn i {
            font-size: 0.85rem;
        }

        .logout-btn:hover {
            background: #fecaca;
            color: #991b1b;
            transform: scale(0.97);
        }

        /* ---------- MAIN CONTENT (breathing space) ---------- */
        .main {
            margin-left: 260px;
            padding: 100px 40px 50px;
            min-height: 100vh;
        }

        /* Header area */
        .hero-section {
            margin-bottom: 32px;
        }

        .hero-section h2 {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            margin-bottom: 6px;
        }

        .hero-section p {
            color: #475569;
            font-size: 1rem;
            margin-top: 4px;
            border-left: 3px solid #3b82f6;
            padding-left: 12px;
        }

        /* CARD modern + micro-interactions */
        .card {
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.02);
            max-width: 580px;
            transition: all 0.2s ease;
            border: 1px solid #edf2f7;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 24px 28px 8px 28px;
            border-bottom: 1px solid #f1f5f9;
        }

        .card-header h3 {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #0f172a;
        }

        .card-header h3 i {
            color: #3b82f6;
            font-size: 1.6rem;
        }

        .stats-container {
            padding: 12px 28px 28px 28px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .stat-block {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .stat-label {
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }

        .stat-value {
            font-size: 2.8rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            display: flex;
            align-items: baseline;
            gap: 8px;
            flex-wrap: wrap;
        }

        .stat-number {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
        }

        .stat-unit {
            font-size: 1rem;
            font-weight: 500;
            color: #64748b;
        }

        .grade-chip {
            background: #eef2ff;
            display: inline-flex;
            align-items: center;
            padding: 4px 16px;
            border-radius: 40px;
            font-weight: 700;
            font-size: 1.9rem;
            color: #1e40af;
            letter-spacing: 1px;
            box-shadow: inset 0 0 0 1px rgba(59,130,246,0.2);
        }

        hr {
            margin: 10px 0;
            border: none;
            border-top: 1px solid #eef2ff;
        }

        /* simple divider inside card */
        .divider {
            height: 1px;
            background: #eef2ff;
            margin: 6px 0;
        }

        /* responsiveness */
        @media (max-width: 820px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                flex-direction: row;
                flex-wrap: wrap;
                padding: 16px 20px;
                gap: 8px;
                z-index: 25;
            }
            .sidebar h3 {
                width: 100%;
                margin-bottom: 12px;
            }
            .sidebar a {
                display: inline-flex;
                width: auto;
                margin-bottom: 0;
                padding: 8px 14px;
            }
            .sidebar a:last-child {
                margin-left: auto;
            }
            .topbar {
                left: 0;
                position: sticky;
                top: 0;
                width: 100%;
            }
            .main {
                margin-left: 0;
                padding: 30px 20px;
            }
            .hero-section h2 {
                font-size: 1.7rem;
            }
            .card {
                max-width: 100%;
            }
        }

        @media (max-width: 550px) {
            .topbar {
                flex-direction: column;
                height: auto;
                gap: 12px;
                padding: 16px;
                align-items: stretch;
            }
            .topbar .info {
                justify-content: space-between;
            }
            .logout-btn {
                justify-content: center;
            }
            .main {
                padding-top: 20px;
            }
            .stat-value {
                font-size: 2.2rem;
            }
        }

        /* subtle loader / placeholder effect for dynamic PHP data */
        .card {
            animation: fadeSlideUp 0.3s ease-out;
        }

        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* additional polish for inline icons */
        i, .fas, .far {
            pointer-events: none;
        }
        .small-note {
            font-size: 0.7rem;
            color: #94a3b8;
            margin-top: 8px;
            text-align: right;
        }
    </style>
</head>
<body>

<!-- SIDEBAR - clean, simple icons and spacing -->
<div class="sidebar">
    <h3>
        <i class="fas fa-graduation-cap"></i> 
        Student Panel
    </h3>
    <a href="student_dashboard.php">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="my_result.php">
        <i class="fas fa-chart-line"></i> My Results
    </a>
    <a href="my_subjects.php">
        <i class="fas fa-book-open"></i> My Subjects
    </a>
    <a href="my_performance.php">
        <i class="fas fa-chart-simple"></i> Performance
    </a>
    <a href="../auth/logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<!-- TOPBAR with simulated dynamic student data using PHP variables -->
<div class="topbar">
    <div class="info">
        <span class="badge">
            <i class="fas fa-user-circle"></i> 
            <?php 
                // Simulated student data – in real scenario this comes from backend.
                // We provide safe defaults using null coalescing.
                $student = $student ?? ['full_name' => 'Alex Morgan', 'class_name' => 'Grade 12 - Science'];
                echo htmlspecialchars($student['full_name']); 
            ?>
        </span>
        <span class="badge">
            <i class="fas fa-school"></i> 
            <?php echo htmlspecialchars($student['class_name']); ?>
        </span>
    </div>
    <a href="logout.php" class="logout-btn">
        <i class="fas fa-arrow-right-from-bracket"></i> Logout
    </a>
</div>

<div class="main">

    <div class="hero-section">
        <h2>Dashboard</h2>
        <p>Here is your academic overview and performance summary</p>
    </div>

    <!-- QUICK STATS GRID -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">

        <!-- STUDENT INFO CARD -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user-graduate"></i> Student Info</h3>
            </div>
            <div class="stats-container">

                <div class="stat-block">
                    <div class="stat-label">Full Name</div>
                    <div style="font-weight:700;">
                        <?php echo htmlspecialchars($student['full_name']); ?>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="stat-block">
                    <div class="stat-label">Class</div>
                    <div style="font-weight:700;">
                        <?php echo htmlspecialchars($student['class_name']); ?>
                    </div>
                </div>

            </div>
        </div>


    </div>

</div>


</body>
</html>
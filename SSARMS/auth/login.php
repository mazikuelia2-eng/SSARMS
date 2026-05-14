<?php




session_start();
include '../db.php';
#require_once __DIR__ . '/../db.php';

// Initialize message
$error = "";

// Only run when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill all fields!";
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            if (password_verify($password, $row['password'])) {

                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['role'] = $row['role'];

                if ($row['role'] == 'admin') {
                    header("Location: ../admin/admin_dashboard.php");
                    exit();
                } elseif ($row['role'] == 'teacher') {
                    header("Location: ../teacher/teacher_dashboard.php");
                    exit();
                } else {
                    header("Location: ../student/student_dashboard.php");
                    exit();
                }

            } else {
                $error = "Wrong password!";
            }

        } else {
            $error = "User not found!";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSARMS - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #eef2f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Simple card container */
        .login-container {
            background: white;
            width: 100%;
            max-width: 400px;
            padding: 36px 32px 40px;
            border-radius: 28px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .logo-wrapper {
            margin-bottom: 20px;
        }

        .logo-wrapper img {
            width: 70px;
            height: auto;
            display: inline-block;
        }

        h2 {
            font-size: 28px;
            font-weight: 700;
            color: #0f2b3d;
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }

        .subhead {
            color: #5b6e8c;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 32px;
            border-bottom: 1.5px solid #e9edf2;
            display: inline-block;
            padding-bottom: 6px;
        }

        /* Error message (simple) */
        .error-message {
            background: #fee2e2;
            color: #b91c1c;
            padding: 10px 16px;
            border-radius: 60px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 24px;
            text-align: center;
            border-left: 3px solid #ef4444;
        }

        /* Form fields - clean and minimal */
        .input-group {
            margin-bottom: 24px;
            text-align: left;
        }


        .form-options {
            text-align: right;
            margin-top: 5px;
        }

        .forgot-link {
           font-size: 13px;
           color: #4f46e5;
           text-decoration: none;
           font-weight: 500;
           transition: all 0.3s ease;
        }

        .forgot-link:hover {
           color: #1e40af;
           text-decoration: underline;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #2c3f55;
            margin-bottom: 6px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 14px;
            font-size: 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            background: white;
            font-family: inherit;
            outline: none;
            transition: 0.15s;
        }

        .input-group input:focus {
            border-color: #2c6e9e;
            box-shadow: 0 0 0 2px rgba(44, 110, 158, 0.1);
        }

        /* Button */
        button {
            width: 100%;
            background: #1e4a6b;
            color: white;
            font-weight: 600;
            font-size: 15px;
            padding: 12px 16px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            margin-top: 8px;
            transition: 0.15s;
        }

        button:hover {
            background: #0f3a54;
        }

        /* Footer simple */
        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #eef2f5;
            font-size: 11px;
            color: #7c8b9f;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 28px 22px 32px;
            }
            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="logo-wrapper">
        <img src="../images/tyler.jpg">
    </div>
    <h2>SSARMS</h2>
    <div class="subhead">Academic Record System</div>

    <?php if (!empty($error)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="input-group">
            <label>📧 Email Address</label>
            <input type="email" name="email" placeholder="user@example.com" required>
        </div>

        <div class="input-group">
            <label>🔒 Password</label>
            <input type="password" name="password" placeholder="•••" required>
        </div>

        <button type="submit">Login</button>

       <div class="form-options">
    <a href="forget_password.php" class="forgot-link">
        Forgot Password?
    </a>
</div>
    </form>

    <div class="footer">
        <span>© 2026 SSARMS</span>
    </div>
</div>
</body>
</html>
<?php
session_start();
include 'config.php'; // รวมไฟล์ config.php

$message = ""; // ตัวแปรสำหรับข้อความแจ้งเตือน

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // ใช้ Prepared Statement เพื่อดึงข้อมูลผู้ใช้
    $query = "SELECT username, password, role FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        // ตรวจสอบรหัสผ่านด้วย password_verify
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // เก็บ role ในเซสชัน
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง!";
        }
    } else {
        $message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Meeting System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow-y: auto;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            margin: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
            border-radius: 50%;
            background: #4dabf7;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(77, 171, 247, 0.4);
        }
        .login-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
            letter-spacing: 1px;
        }
        .login-form h2 {
            text-align: center;
            color: #4dabf7;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 25px;
        }
        .input-group {
            margin-bottom: 20px;
            position: relative;
        }
        .input-group label {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
            font-weight: 500;
        }
        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dcdcdc;
            border-radius: 10px;
            font-size: 15px;
            color: #2c3e50;
            background: #f9fafb;
            transition: all 0.3s ease;
        }
        .input-group input:focus {
            outline: none;
            border-color: #4dabf7;
            box-shadow: 0 0 10px rgba(77, 171, 247, 0.3);
            background: #fff;
        }
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 13px;
            color: #57606f;
        }
        .options label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .options input[type="checkbox"] {
            margin-right: 8px;
            accent-color: #4dabf7;
        }
        .options .forgot-password {
            color: #4dabf7;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .options .forgot-password:hover {
            color: #37b24d;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #4dabf7, #37b24d);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: linear-gradient(90deg, #37b24d, #4dabf7);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(55, 178, 77, 0.4);
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #747d8c;
        }
        .register-link a {
            color: #4dabf7;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .register-link a:hover {
            color: #37b24d;
        }
        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            animation: slideIn 0.5s ease-in-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message.error {
            background: #fff5f5;
            color: #ff4757;
            border: 1px solid #ffe3e3;
            box-shadow: 0 5px 15px rgba(255, 71, 87, 0.2);
        }
        .message i {
            font-size: 18px;
        }
        .login-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #a4b0be;
        }
        @media (max-width: 480px) {
            .login-container {
                padding: 25px;
                max-width: 90%;
            }
            .login-form h2 {
                font-size: 22px;
            }
            .login-header h1 {
                font-size: 24px;
            }
            .btn-login {
                padding: 12px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">MS</div> <!-- Logo ทันสมัย -->
            <h1>Meeting System</h1>
        </div>
        <form method="POST" class="login-form">
            <h2>เข้าสู่ระบบ</h2>

            <!-- ข้อความแจ้งเตือน -->
            <?php if (!empty($message)) { ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $message; ?>
                </div>
            <?php } ?>

            <div class="input-group">
                <label for="username">ชื่อผู้ใช้</label>
                <input type="text" id="username" name="username" placeholder="กรอกชื่อผู้ใช้" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>

            <div class="input-group">
                <label for="password">รหัสผ่าน</label>
                <input type="password" id="password" name="password" placeholder="กรอกรหัสผ่าน" required>
            </div>

            <div class="options">
                <label><input type="checkbox" name="remember"> จดจำฉัน</label>
                <a href="#" class="forgot-password">ลืมรหัสผ่าน?</a>
            </div>

            <button type="submit" name="submit" class="btn-login">เข้าสู่ระบบ</button>

            <p class="register-link">ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิกที่นี่</a></p>
        </form>
        <div class="login-footer">
            <p>© <?php echo date('Y'); ?>Snowjor All rights reserved.</p>
        </div>
    </div>
</body>
</html>
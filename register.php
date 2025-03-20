<?php
include 'config.php'; // รวมไฟล์ config.php

$message = ""; // ตัวแปรสำหรับข้อความแจ้งเตือน

if (isset($_POST['submit'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password']; // เพิ่มช่องยืนยันรหัสผ่าน
    $position = trim($_POST['position']);
    $department = trim($_POST['department']);
    $division = $_POST['division'];
    $role = $_POST['role'];

    // ตรวจสอบข้อมูล
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($position) || empty($department) || empty($division) || empty($role)) {
        $message = "กรุณากรอกข้อมูลให้ครบทุกช่อง!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "รูปแบบอีเมลไม่ถูกต้อง!";
    } elseif (strlen($password) < 6) {
        $message = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร!";
    } elseif ($password !== $confirm_password) { // ตรวจสอบว่ารหัสผ่านตรงกันหรือไม่
        $message = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน!";
    } else {
        // ตรวจสอบ username หรือ email ซ้ำ
        $check_query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $message = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่ในระบบแล้ว!";
        } else {
            // เข้ารหัสรหัสผ่าน
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (first_name, last_name, username, email, password, position, department, division, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "sssssssss", $first_name, $last_name, $username, $email, $hashed_password, $position, $department, $division, $role);

            if (mysqli_stmt_execute($stmt)) {
                $message = "ลงทะเบียนสำเร็จ! <a href='login.php' class='message-link'>เข้าสู่ระบบที่นี่</a>";
            } else {
                $message = "เกิดข้อผิดพลาดในการลงทะเบียน: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Meeting System</title>
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
            position: relative;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 600px;
            margin: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
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
        .register-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
            letter-spacing: 1px;
        }
        .register-form {
            display: grid;
            gap: 25px;
        }
        .form-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .form-section h2 {
            color: #4dabf7;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }
        .input-group {
            margin-bottom: 15px;
            position: relative;
        }
        .input-group label {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
            font-weight: 500;
        }
        .input-group input,
        .input-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dcdcdc;
            border-radius: 10px;
            font-size: 15px;
            color: #2c3e50;
            background: #fff;
            transition: all 0.3s ease;
        }
        .input-group select {
            appearance: none;
            background: #fff url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="%232c3e50" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 15px center;
        }
        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            border-color: #4dabf7;
            box-shadow: 0 0 10px rgba(77, 171, 247, 0.3);
        }
        .btn-register {
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
        .btn-register:hover {
            background: linear-gradient(90deg, #37b24d, #4dabf7);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(55, 178, 77, 0.4);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #747d8c;
        }
        .login-link a {
            color: #4dabf7;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .login-link a:hover {
            color: #37b24d;
        }
        .message {
            padding: 15px 20px;
            border-radius: 12px;
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
        .message.success {
            background: #e6ffe6;
            color: #37b24d;
            border: 1px solid #b3ffb3;
            box-shadow: 0 5px 15px rgba(55, 178, 77, 0.2);
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
        .message-link {
            color: #37b24d;
            font-weight: 600;
            text-decoration: none;
        }
        .message-link:hover {
            text-decoration: underline;
        }
        .register-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #a4b0be;
        }
        /* Cookie Policy */
        .cookie-policy {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            max-width: 350px;
            z-index: 1000;
            display: none;
            animation: slideUp 0.5s ease-in-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .cookie-policy p {
            font-size: 13px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .cookie-policy .buttons {
            display: flex;
            gap: 10px;
        }
        .cookie-policy button {
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .cookie-policy .accept {
            background: #37b24d;
            color: white;
        }
        .cookie-policy .accept:hover {
            background: #2d9a3e;
        }
        .cookie-policy .decline {
            background: #ff4757;
            color: white;
        }
        .cookie-policy .decline:hover {
            background: #e63946;
        }
        @media (max-width: 480px) {
            .register-container {
                padding: 25px;
                max-width: 90%;
            }
            .form-section h2 {
                font-size: 18px;
            }
            .register-header h1 {
                font-size: 24px;
            }
            .btn-register {
                padding: 12px;
                font-size: 15px;
            }
            .cookie-policy {
                left: 10px;
                right: 10px;
                bottom: 10px;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="logo">MS</div>
            <h1>Meeting System</h1>
        </div>
        <form method="POST" class="register-form">
            <?php if (!empty($message)) { ?>
                <div class="message <?php echo strpos($message, 'สำเร็จ') !== false ? 'success' : 'error'; ?>">
                    <i class="fas <?php echo strpos($message, 'สำเร็จ') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php } ?>

            <!-- ส่วนข้อมูลส่วนตัว -->
            <div class="form-section">
                <h2>ข้อมูลส่วนตัว</h2>
                <div class="input-group">
                    <label for="first_name">ชื่อ</label>
                    <input type="text" id="first_name" name="first_name" placeholder="กรอกชื่อ" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                </div>
                <div class="input-group">
                    <label for="last_name">นามสกุล</label>
                    <input type="text" id="last_name" name="last_name" placeholder="กรอกนามสกุล" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                </div>
            </div>

            <!-- ส่วนข้อมูลล็อกอิน -->
            <div class="form-section">
                <h2>ข้อมูลล็อกอิน</h2>
                <div class="input-group">
                    <label for="username">ชื่อผู้ใช้</label>
                    <input type="text" id="username" name="username" placeholder="กรอกชื่อผู้ใช้" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                </div>
                <div class="input-group">
                    <label for="email">อีเมล</label>
                    <input type="email" id="email" name="email" placeholder="กรอกอีเมล" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                <div class="input-group">
                    <label for="password">รหัสผ่าน</label>
                    <input type="password" id="password" name="password" placeholder="กรอกรหัสผ่าน" required>
                </div>
                <div class="input-group">
                    <label for="confirm_password">ยืนยันรหัสผ่าน</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>
                </div>
            </div>

            <!-- ส่วนข้อมูลตำแหน่ง -->
            <div class="form-section">
                <h2>ข้อมูลตำแหน่ง</h2>
                <div class="input-group">
                    <label for="position">ตำแหน่ง</label>
                    <input type="text" id="position" name="position" placeholder="กรอกตำแหน่ง" value="<?php echo isset($_POST['position']) ? htmlspecialchars($_POST['position']) : ''; ?>" required>
                </div>
                <div class="input-group">
                    <label for="department">แผนก</label>
                    <input type="text" id="department" name="department" placeholder="กรอกแผนก" value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>" required>
                </div>
                <div class="input-group">
                    <label for="division">ฝ่าย</label>
                    <select id="division" name="division" required>
                        <option value="" disabled <?php echo !isset($_POST['division']) ? 'selected' : ''; ?>>-- เลือกฝ่าย --</option>
                        <option value="IT" <?php echo isset($_POST['division']) && $_POST['division'] == 'IT' ? 'selected' : ''; ?>>IT</option>
                        <option value="HR" <?php echo isset($_POST['division']) && $_POST['division'] == 'HR' ? 'selected' : ''; ?>>HR</option>
                        <option value="BD" <?php echo isset($_POST['division']) && $_POST['division'] == 'BD' ? 'selected' : ''; ?>>BD</option>
                        <option value="WH" <?php echo isset($_POST['division']) && $_POST['division'] == 'WH' ? 'selected' : ''; ?>>WH</option>
                        <option value="PU" <?php echo isset($_POST['division']) && $_POST['division'] == 'PU' ? 'selected' : ''; ?>>PU</option>
                        <option value="MKT" <?php echo isset($_POST['division']) && $_POST['division'] == 'MKT' ? 'selected' : ''; ?>>MKT</option>
                        <option value="ACC" <?php echo isset($_POST['division']) && $_POST['division'] == 'ACC' ? 'selected' : ''; ?>>ACC</option>
                        <option value="SALE" <?php echo isset($_POST['division']) && $_POST['division'] == 'SALE' ? 'selected' : ''; ?>>SALE</option>
                        <option value="R&D" <?php echo isset($_POST['division']) && $_POST['division'] == 'R&D' ? 'selected' : ''; ?>>R&D</option>
                        <option value="QC" <?php echo isset($_POST['division']) && $_POST['division'] == 'QC' ? 'selected' : ''; ?>>QC</option>
                        <option value="PD" <?php echo isset($_POST['division']) && $_POST['division'] == 'PD' ? 'selected' : ''; ?>>PD</option>
                        <option value="CEO" <?php echo isset($_POST['division']) && $_POST['division'] == 'CEO' ? 'selected' : ''; ?>>CEO</option>
                        <option value="Director" <?php echo isset($_POST['division']) && $_POST['division'] == 'Director' ? 'selected' : ''; ?>>Director</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="role">สิทธิ์ผู้ใช้</label>
                    <select id="role" name="role" required>
                        <option value="" disabled <?php echo !isset($_POST['role']) ? 'selected' : ''; ?>>-- เลือกสิทธิ์ --</option>
                        <option value="user" <?php echo isset($_POST['role']) && $_POST['role'] == 'user' ? 'selected' : ''; ?>>User (ผู้ใช้ทั่วไป)</option>
                        <option value="admin" <?php echo isset($_POST['role']) && $_POST['role'] == 'admin' ? 'selected' : ''; ?>>Admin (ผู้ดูแลระบบ)</option>
                        <option value="superadmin" <?php echo isset($_POST['role']) && $_POST['role'] == 'superadmin' ? 'selected' : ''; ?>>Superadmin (ผู้ดูแลสูงสุด)</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="submit" class="btn-register">สมัครสมาชิก</button>
            <p class="login-link">มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบที่นี่</a></p>
        </form>
        <div class="register-footer">
            <p>© <?php echo date('Y'); ?> Meeting System. All rights reserved.</p>
        </div>
    </div>

    <!-- Cookie Policy -->
    <div class="cookie-policy" id="cookiePolicy">
        <p>เราใช้คุกกี้เพื่อปรับปรุงประสบการณ์การใช้งานของคุณ คุณยินยอมหรือไม่?</p>
        <div class="buttons">
            <button class="accept" onclick="acceptCookies()">ยินยอม</button>
            <button class="decline" onclick="declineCookies()">ไม่ยินยอม</button>
        </div>
    </div>

    <script>
        // ตรวจสอบว่าผู้ใช้เคยยอมรับคุกกี้หรือยัง
        if (!localStorage.getItem('cookiesAccepted')) {
            document.getElementById('cookiePolicy').style.display = 'block';
        }

        function acceptCookies() {
            localStorage.setItem('cookiesAccepted', 'true');
            document.getElementById('cookiePolicy').style.display = 'none';
        }

        function declineCookies() {
            localStorage.setItem('cookiesAccepted', 'false');
            document.getElementById('cookiePolicy').style.display = 'none';
        }
    </script>
</body>
</html>
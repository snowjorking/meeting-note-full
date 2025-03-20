<?php
session_start();
include 'config.php'; // รวมไฟล์ config.php

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$message = ""; // ตัวแปรสำหรับข้อความแจ้งเตือน

// ดึงข้อมูลฝ่ายของผู้ใช้
$user_query = "SELECT id, department FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);
$user_id = $user['id'];
$department = $user['department'];

if (isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $meeting_no = trim($_POST['meeting_no']);
    $meeting_date = $_POST['meeting_date'];
    $meeting_time = $_POST['meeting_time'];
    $location = trim($_POST['location']);
    $committee_present = array_filter(array_map('trim', $_POST['committee_present'])); // กรรมการที่เข้าประชุม
    $committee_absent = array_filter(array_map('trim', $_POST['committee_absent'])); // กรรมการที่ไม่ได้ประชุม
    $attendees = array_filter(array_map('trim', $_POST['attendees'])); // ผู้เข้าร่วมประชุม
    $details = trim($_POST['details']);
    $recorded_at = date('Y-m-d H:i:s'); // วันที่และเวลาบันทึก

    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($title) || empty($meeting_no) || empty($meeting_date) || empty($meeting_time) || empty($location) || empty($details)) {
        $message = "กรุณากรอกข้อมูลที่จำเป็นให้ครบ!";
    } else {
        // จัดรูปแบบข้อมูลเป็น JSON
        $committee_present_json = json_encode($committee_present, JSON_UNESCAPED_UNICODE);
        $committee_absent_json = json_encode($committee_absent, JSON_UNESCAPED_UNICODE);
        $attendees_json = json_encode($attendees, JSON_UNESCAPED_UNICODE);

        // อัปโหลดไฟล์แนบ
        $file_path = "";
        $target_dir = "uploads/";
        
        // ตรวจสอบและสร้างโฟลเดอร์ uploads หากยังไม่มี
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (!empty($_FILES['attachment']['name'])) {
            $file_name = uniqid() . "_" . basename($_FILES['attachment']['name']);
            $file_path = $target_dir . $file_name;

            // ตรวจสอบการอัปโหลดไฟล์
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $file_path)) {
                $message .= "อัปโหลดไฟล์สำเร็จ! ";
            } else {
                $message = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์! รหัสข้อผิดพลาด: " . $_FILES['attachment']['error'];
                $file_path = ""; // รีเซ็ต file_path หากอัปโหลดล้มเหลว
            }
        }

        // บันทึกข้อมูลลงฐานข้อมูล
        $query = "INSERT INTO meetings (user_id, title, meeting_no, department, meeting_date, meeting_time, location, committee_present, committee_absent, attendees, details, recorded_at, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            $message = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . mysqli_error($conn);
        } else {
            mysqli_stmt_bind_param($stmt, "issssssssssss", $user_id, $title, $meeting_no, $department, $meeting_date, $meeting_time, $location, $committee_present_json, $committee_absent_json, $attendees_json, $details, $recorded_at, $file_path);

            if (mysqli_stmt_execute($stmt)) {
                $message = "บันทึกข้อมูลสำเร็จ! <a href='dashboard.php' class='message-link'>กลับไปที่แดชบอร์ด</a>";
                if (!empty($file_path)) {
                    $message .= " (ไฟล์แนบ: " . htmlspecialchars($file_name) . ")";
                }
            } else {
                $message = "เกิดข้อผิดพลาดในการบันทึก: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Meeting - Meeting System</title>
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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            overflow-y: auto;
            position: relative;
        }
        .record-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 1000px;
            margin: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .record-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
        }
        .record-header .logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 10px;
            border-radius: 50%;
            background: #2a5298;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(42, 82, 152, 0.3);
        }
        .record-header h1 {
            font-size: 26px;
            font-weight: 600;
            color: #1e3c72;
        }
        .record-form {
            display: grid;
            gap: 30px;
        }
        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        .form-section h2 {
            color: #1e3c72;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: left;
            border-left: 4px solid #2a5298;
            padding-left: 10px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 8px;
            display: block;
            font-weight: 500;
        }
        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 15px;
            color: #2c3e50;
            background: #fff;
            transition: all 0.3s ease;
        }
        .input-group textarea {
            height: 120px;
            resize: vertical;
        }
        .input-group input:focus,
        .input-group textarea:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 8px rgba(42, 82, 152, 0.2);
        }
        .input-group input[disabled] {
            background: #e9ecef;
            color: #6c757d;
        }
        .inline-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .inline-group .input-group {
            flex: 1;
            min-width: 200px;
        }
        .list-group {
            display: grid;
            gap: 12px;
        }
        .add-btn {
            padding: 8px 15px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .add-btn:hover {
            background: #1e3c72;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(30, 60, 114, 0.3);
        }
        .btn-save {
            width: 100%;
            padding: 15px;
            background: linear-gradient(90deg, #2a5298, #1e3c72);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-save:hover {
            background: linear-gradient(90deg, #1e3c72, #2a5298);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.4);
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .back-link a {
            color: #2a5298;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .back-link a:hover {
            color: #1e3c72;
        }
        .message {
            padding: 15px 20px;
            border-radius: 10px;
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
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message-link {
            color: #155724;
            font-weight: 600;
            text-decoration: none;
        }
        .message-link:hover {
            text-decoration: underline;
        }
        .file-upload input[type="file"] {
            padding: 10px;
            border: 1px dashed #2a5298;
            border-radius: 8px;
            background: #fff;
            width: 100%;
        }
        @media (max-width: 768px) {
            .record-container {
                padding: 25px;
                max-width: 90%;
            }
            .inline-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="record-container">
        <div class="record-header">
            <div class="logo">MS</div>
            <h1>Meeting System</h1>
        </div>
        <form method="POST" class="record-form" enctype="multipart/form-data">
            <?php if (!empty($message)) { ?>
                <div class="message <?php echo strpos($message, 'สำเร็จ') !== false ? 'success' : 'error'; ?>">
                    <i class="fas <?php echo strpos($message, 'สำเร็จ') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php } ?>

            <!-- 1. หัวข้อการประชุม -->
            <div class="form-section">
                <h2>หัวข้อการประชุม</h2>
                <div class="inline-group">
                    <div class="input-group">
                        <label for="title">หัวข้อรายงานการประชุม</label>
                        <input type="text" id="title" name="title" placeholder="กรอกหัวข้อ" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="meeting_no">ครั้งที่</label>
                        <input type="number" id="meeting_no" name="meeting_no" placeholder="กรอกครั้งที่" value="<?php echo isset($_POST['meeting_no']) ? htmlspecialchars($_POST['meeting_no']) : ''; ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="department">ฝ่าย</label>
                        <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($department); ?>" disabled>
                    </div>
                </div>
            </div>

            <!-- 2. วัน เวลา และสถานที่ -->
            <div class="form-section">
                <h2>วัน เวลา และสถานที่</h2>
                <div class="inline-group">
                    <div class="input-group">
                        <label for="meeting_date">ประชุมเมื่อวันที่</label>
                        <input type="date" id="meeting_date" name="meeting_date" value="<?php echo isset($_POST['meeting_date']) ? $_POST['meeting_date'] : date('Y-m-d'); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="meeting_time">เวลา</label>
                        <input type="time" id="meeting_time" name="meeting_time" value="<?php echo isset($_POST['meeting_time']) ? $_POST['meeting_time'] : ''; ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="location">สถานที่</label>
                        <input type="text" id="location" name="location" placeholder="กรอกสถานที่" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <!-- 3. กรรมการที่เข้าประชุม -->
            <div class="form-section">
                <h2>กรรมการที่เข้าประชุม</h2>
                <div class="list-group" id="committee-present-list">
                    <?php for ($i = 1; $i <= 3; $i++) { ?>
                        <div class="input-group">
                            <label><?php echo $i; ?>. ชื่อกรรมการ</label>
                            <input type="text" name="committee_present[]" placeholder="กรอกชื่อกรรมการ" value="<?php echo isset($_POST['committee_present'][$i-1]) ? htmlspecialchars($_POST['committee_present'][$i-1]) : ''; ?>">
                        </div>
                    <?php } ?>
                </div>
                <button type="button" class="add-btn" onclick="addField('committee-present-list')">เพิ่มกรรมการ</button>
            </div>

            <!-- 4. กรรมการที่ไม่ได้ประชุม -->
            <div class="form-section">
                <h2>กรรมการที่ไม่ได้ประชุม</h2>
                <div class="list-group" id="committee-absent-list">
                    <?php for ($i = 1; $i <= 3; $i++) { ?>
                        <div class="input-group">
                            <label><?php echo $i; ?>. ชื่อกรรมการ</label>
                            <input type="text" name="committee_absent[]" placeholder="กรอกชื่อกรรมการ" value="<?php echo isset($_POST['committee_absent'][$i-1]) ? htmlspecialchars($_POST['committee_absent'][$i-1]) : ''; ?>">
                        </div>
                    <?php } ?>
                </div>
                <button type="button" class="add-btn" onclick="addField('committee-absent-list')">เพิ่มกรรมการ</button>
            </div>

            <!-- 5. ผู้เข้าร่วมประชุม -->
            <div class="form-section">
                <h2>ผู้เข้าร่วมประชุม</h2>
                <div class="list-group" id="attendees-list">
                    <?php for ($i = 1; $i <= 3; $i++) { ?>
                        <div class="input-group">
                            <label><?php echo $i; ?>. ชื่อผู้เข้าร่วม</label>
                            <input type="text" name="attendees[]" placeholder="กรอกชื่อผู้เข้าร่วม" value="<?php echo isset($_POST['attendees'][$i-1]) ? htmlspecialchars($_POST['attendees'][$i-1]) : ''; ?>">
                        </div>
                    <?php } ?>
                </div>
                <button type="button" class="add-btn" onclick="addField('attendees-list')">เพิ่มผู้เข้าร่วม</button>
            </div>

            <!-- 6. รายละเอียดการประชุม -->
            <div class="form-section">
                <h2>รายละเอียดการประชุม</h2>
                <div class="input-group">
                    <label for="details">รายละเอียด</label>
                    <textarea id="details" name="details" placeholder="กรอกรายละเอียดการประชุม" required><?php echo isset($_POST['details']) ? htmlspecialchars($_POST['details']) : ''; ?></textarea>
                </div>
            </div>

            <!-- 7. วันที่และเวลาบันทึก -->
            <div class="form-section">
                <h2>วันที่และเวลาบันทึก</h2>
                <div class="input-group">
                    <label>บันทึกเมื่อ</label>
                    <input type="text" value="<?php echo date('Y-m-d H:i:s'); ?>" disabled>
                </div>
            </div>

            <!-- 9. อัปโหลดไฟล์แนบ -->
            <div class="form-section">
                <h2>ไฟล์แนบ</h2>
                <div class="input-group file-upload">
                    <label for="attachment">อัปโหลดไฟล์ (ถ้ามี)</label>
                    <input type="file" id="attachment" name="attachment">
                </div>
            </div>

            <button type="submit" name="submit" class="btn-save">บันทึกการประชุม</button>
            <p class="back-link"><a href="dashboard.php">กลับไปที่แดชบอร์ด</a></p>
        </form>
    </div>

    <script>
        function addField(listId) {
            const list = document.getElementById(listId);
            const count = list.children.length + 1;
            const newField = document.createElement('div');
            newField.className = 'input-group';
            newField.innerHTML = `
                <label>${count}. ชื่อ${listId.includes('committee-present') ? 'กรรมการ' : listId.includes('committee-absent') ? 'กรรมการ' : 'ผู้เข้าร่วม'}</label>
                <input type="text" name="${listId.includes('committee-present') ? 'committee_present' : listId.includes('committee-absent') ? 'committee_absent' : 'attendees'}[]" placeholder="กรอกชื่อ${listId.includes('committee-present') ? 'กรรมการ' : listId.includes('committee-absent') ? 'กรรมการ' : 'ผู้เข้าร่วม'}">
            `;
            list.appendChild(newField);
        }
    </script>
</body>
</html>
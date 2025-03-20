<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$user_query = "SELECT department, role FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);
$user_department = $user['department'];
$user_role = $user['role'];

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$meeting_id = $_GET['id'];
$query = "SELECT * FROM meetings WHERE id = ? AND department = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "is", $meeting_id, $user_department);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$meeting = mysqli_fetch_assoc($result);

if (!$meeting && $user_role !== 'superadmin') {
    die("คุณไม่มีสิทธิ์แก้ไขข้อมูลนี้!");
}

if (isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $meeting_no = trim($_POST['meeting_no']);
    $meeting_date = $_POST['meeting_date'];
    $meeting_time = $_POST['meeting_time'];
    $location = trim($_POST['location']);
    $committee_present = array_filter(array_map('trim', $_POST['committee_present']));
    $committee_absent = array_filter(array_map('trim', $_POST['committee_absent']));
    $attendees = array_filter(array_map('trim', $_POST['attendees']));
    $details = trim($_POST['details']);
    $recorded_at = date('Y-m-d H:i:s');

    if (empty($title) || empty($meeting_no) || empty($meeting_date) || empty($meeting_time) || empty($location) || empty($details)) {
        $message = "กรุณากรอกข้อมูลที่จำเป็นให้ครบ!";
    } else {
        $committee_present_json = json_encode($committee_present);
        $committee_absent_json = json_encode($committee_absent);
        $attendees_json = json_encode($attendees);

        $file_path = $meeting['file_path'];
        if (!empty($_FILES['attachment']['name'])) {
            $file_name = uniqid() . "_" . basename($_FILES['attachment']['name']);
            $target_dir = "uploads/";
            $file_path = $target_dir . $file_name;
            if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $file_path)) {
                $message = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์!";
                $file_path = $meeting['file_path'];
            }
        }

        $query = "UPDATE meetings SET title = ?, meeting_no = ?, meeting_date = ?, meeting_time = ?, location = ?, committee_present = ?, committee_absent = ?, attendees = ?, details = ?, recorded_at = ?, file_path = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssssssssi", $title, $meeting_no, $meeting_date, $meeting_time, $location, $committee_present_json, $committee_absent_json, $attendees_json, $details, $recorded_at, $file_path, $meeting_id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: dashboard.php?status=updated");
            exit();
        } else {
            $message = "เกิดข้อผิดพลาดในการแก้ไข: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Meeting - Meeting System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ใช้ CSS เดียวกับ record.php แต่ปรับบางส่วน */
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            overflow-y: auto;
        }
        .edit-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 1000px;
            margin: 20px;
        }
        .edit-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .edit-header h1 {
            font-size: 26px;
            font-weight: 600;
            color: #1e3c72;
        }
        .edit-form {
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
        }
        .input-group textarea {
            height: 120px;
            resize: vertical;
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
        .btn-save {
            width: 100%;
            padding: 15px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-save:hover {
            background: #1e3c72;
        }
        .message {
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            font-size: 14px;
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
    <script>
        function addField(listId) {
            const list = document.getElementById(listId);
            const count = list.children.length + 1;
            const newField = document.createElement('div');
            newField.className = 'input-group';
            newField.innerHTML = `
                <label>${count}. ชื่อ${listId.includes('committee-present') ? 'กรรมการ' : listId.includes('committee-absent') ? 'กรรมการ' : 'ผู้เข้าร่วม'}</label>
                <input type="text" name="${listId.includes('committee-present') ? 'committee_present' : listId.includes('committee-absent') ? 'committee_absent' : 'attendees'}[]" placeholder="กรอกชื่อ">
            `;
            list.appendChild(newField);
        }
    </script>
</head>
<body>
    <div class="edit-container">
        <div class="edit-header">
            <h1>แก้ไขบันทึกการประชุม</h1>
        </div>
        <form method="POST" class="edit-form" enctype="multipart/form-data">
            <?php if (!empty($message)) { ?>
                <div class="message"><?php echo $message; ?></div>
            <?php } ?>

            <div class="form-section">
                <h2>หัวข้อการประชุม</h2>
                <div class="inline-group">
                    <div class="input-group">
                        <label for="title">หัวข้อรายงานการประชุม</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($meeting['title']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="meeting_no">ครั้งที่</label>
                        <input type="number" id="meeting_no" name="meeting_no" value="<?php echo htmlspecialchars($meeting['meeting_no']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="department">ฝ่าย</label>
                        <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($meeting['department']); ?>" disabled>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>วัน เวลา และสถานที่</h2>
                <div class="inline-group">
                    <div class="input-group">
                        <label for="meeting_date">วันที่ประชุม</label>
                        <input type="date" id="meeting_date" name="meeting_date" value="<?php echo htmlspecialchars($meeting['meeting_date']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="meeting_time">เวลา</label>
                        <input type="time" id="meeting_time" name="meeting_time" value="<?php echo htmlspecialchars($meeting['meeting_time']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="location">สถานที่</label>
                        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($meeting['location']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>กรรมการที่เข้าประชุม</h2>
                <div class="list-group" id="committee-present-list">
                    <?php
                    $committee_present = json_decode($meeting['committee_present'], true);
                    foreach ($committee_present as $index => $name) { ?>
                        <div class="input-group">
                            <label><?php echo $index + 1; ?>. ชื่อกรรมการ</label>
                            <input type="text" name="committee_present[]" value="<?php echo htmlspecialchars($name); ?>">
                        </div>
                    <?php } ?>
                </div>
                <button type="button" onclick="addField('committee-present-list')">เพิ่มกรรมการ</button>
            </div>

            <div class="form-section">
                <h2>กรรมการที่ไม่ได้ประชุม</h2>
                <div class="list-group" id="committee-absent-list">
                    <?php
                    $committee_absent = json_decode($meeting['committee_absent'], true);
                    foreach ($committee_absent as $index => $name) { ?>
                        <div class="input-group">
                            <label><?php echo $index + 1; ?>. ชื่อกรรมการ</label>
                            <input type="text" name="committee_absent[]" value="<?php echo htmlspecialchars($name); ?>">
                        </div>
                    <?php } ?>
                </div>
                <button type="button" onclick="addField('committee-absent-list')">เพิ่มกรรมการ</button>
            </div>

            <div class="form-section">
                <h2>ผู้เข้าร่วมประชุม</h2>
                <div class="list-group" id="attendees-list">
                    <?php
                    $attendees = json_decode($meeting['attendees'], true);
                    foreach ($attendees as $index => $name) { ?>
                        <div class="input-group">
                            <label><?php echo $index + 1; ?>. ชื่อผู้เข้าร่วม</label>
                            <input type="text" name="attendees[]" value="<?php echo htmlspecialchars($name); ?>">
                        </div>
                    <?php } ?>
                </div>
                <button type="button" onclick="addField('attendees-list')">เพิ่มผู้เข้าร่วม</button>
            </div>

            <div class="form-section">
                <h2>รายละเอียดการประชุม</h2>
                <div class="input-group">
                    <label for="details">รายละเอียด</label>
                    <textarea id="details" name="details" required><?php echo htmlspecialchars($meeting['details']); ?></textarea>
                </div>
            </div>

            <div class="form-section">
                <h2>ไฟล์แนบ</h2>
                <div class="input-group">
                    <label for="attachment">อัปโหลดไฟล์ใหม่ (ถ้ามี)</label>
                    <input type="file" id="attachment" name="attachment">
                    <?php if (!empty($meeting['file_path'])) { ?>
                        <p>ไฟล์ปัจจุบัน: <a href="<?php echo htmlspecialchars($meeting['file_path']); ?>" target="_blank">ดาวน์โหลด</a></p>
                    <?php } ?>
                </div>
            </div>

            <button type="submit" name="submit" class="btn-save">บันทึกการแก้ไข</button>
        </form>
    </div>
</body>
</html>
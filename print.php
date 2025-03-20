<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("ไม่พบ ID การประชุม!");
}

$meeting_id = $_GET['id'];
$query = "SELECT * FROM meetings WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $meeting_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$meeting = mysqli_fetch_assoc($result);

if (!$meeting) {
    die("ไม่พบข้อมูลการประชุม!");
}

// แปลง JSON กลับเป็น array
$committee_present = json_decode($meeting['committee_present'], true);
$committee_absent = json_decode($meeting['committee_absent'], true);
$attendees = json_decode($meeting['attendees'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกการประชุม - Meeting System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #e9f0f6;
            color: #1e3a5f;
            line-height: 1.6;
            font-size: 11pt;
            padding: 25px;
        }
        .print-container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
            overflow-y: auto;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 3px solid #1e3a5f;
            margin-bottom: 25px;
        }
        .header .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1e3a5f, #4a90e2);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26pt;
            font-weight: 600;
            margin: 0 auto 12px;
            box-shadow: 0 2px 10px rgba(30, 58, 95, 0.3);
        }
        .header h1 {
            font-size: 22pt;
            font-weight: 600;
            color: #1e3a5f;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 10pt;
            color: #6b7280;
        }
        .section {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            padding: 15px;
            background: #f9fafc;
            border-radius: 8px;
            border: 1px solid #dbe2ea;
        }
        .section h2 {
            font-size: 14pt;
            font-weight: 600;
            color: #1e3a5f;
            width: 100%;
            margin-bottom: 12px;
            border-bottom: 2px solid #4a90e2;
            padding-bottom: 5px;
        }
        .section .left, .section .right {
            width: 48%;
        }
        .section p {
            font-size: 11pt;
            color: #1e3a5f;
            margin-bottom: 6px;
        }
        .section ul {
            font-size: 11pt;
            list-style-type: decimal;
            margin-left: 20px;
            color: #1e3a5f;
        }
        .section ul li {
            margin-bottom: 5px;
        }
        .section a {
            color: #4a90e2;
            text-decoration: none;
            font-weight: 500;
        }
        .section a:hover {
            text-decoration: underline;
        }
        .section-full {
            width: 100%;
        }
        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 12pt;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-print {
            background: linear-gradient(90deg, #4a90e2, #357abd);
        }
        .btn-print:hover {
            background: linear-gradient(90deg, #357abd, #2a639e);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
            transform: translateY(-2px);
        }
        .btn-back {
            background: linear-gradient(90deg, #38a169, #2f855a);
        }
        .btn-back:hover {
            background: linear-gradient(90deg, #2f855a, #276749);
            box-shadow: 0 4px 12px rgba(56, 161, 105, 0.3);
            transform: translateY(-2px);
        }
        .btn-logout {
            background: linear-gradient(90deg, #e53e3e, #c53030);
        }
        .btn-logout:hover {
            background: linear-gradient(90deg, #c53030, #9b2c2c);
            box-shadow: 0 4px 12px rgba(229, 62, 62, 0.3);
            transform: translateY(-2px);
        }
        .footer {
            text-align: center;
            font-size: 9pt;
            color: #6b7280;
            margin-top: 25px;
            border-top: 1px solid #dbe2ea;
            padding-top: 10px;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .print-container {
                box-shadow: none;
                border: none;
                padding: 20px;
                margin: 0;
                width: 100%;
                max-width: none;
            }
            .header {
                border-bottom: 3px solid #1e3a5f;
                padding: 15px 0;
            }
            .header .logo {
                width: 50px;
                height: 50px;
                font-size: 22pt;
            }
            .section {
                background: #fff;
                border: 1px solid #dbe2ea;
                padding: 10px;
            }
            .section h2 {
                border-bottom: 2px solid #4a90e2;
            }
            .section .left, .section .right {
                width: 48%;
            }
            .button-group, .footer {
                display: none;
            }
            @page {
                size: A4;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="header">
            <div class="logo">MS</div>
            <h1>บันทึกการประชุม</h1>
            <div class="subtitle">ระบบจัดการประชุม</div>
        </div>

        <div class="section">
            <h2>หัวข้อการประชุม</h2>
            <div class="left">
                <p><strong>หัวข้อ:</strong> <?php echo htmlspecialchars($meeting['title']); ?></p>
                <p><strong>ครั้งที่:</strong> <?php echo htmlspecialchars($meeting['meeting_no']); ?></p>
            </div>
            <div class="right">
                <p><strong>ฝ่าย:</strong> <?php echo htmlspecialchars($meeting['department']); ?></p>
            </div>
        </div>

        <div class="section">
            <h2>วัน เวลา และสถานที่</h2>
            <div class="left">
                <p><strong>วันที่:</strong> <?php echo htmlspecialchars($meeting['meeting_date']); ?></p>
                <p><strong>เวลา:</strong> <?php echo htmlspecialchars($meeting['meeting_time']); ?></p>
            </div>
            <div class="right">
                <p><strong>สถานที่:</strong> <?php echo htmlspecialchars($meeting['location']); ?></p>
            </div>
        </div>

        <div class="section">
            <h2>รายชื่อผู้เข้าร่วม</h2>
            <div class="left">
                <strong>กรรมการที่เข้าประชุม:</strong>
                <?php if (!empty($committee_present)) { ?>
                    <ul>
                        <?php foreach ($committee_present as $index => $name) { ?>
                            <li><?php echo htmlspecialchars($name); ?></li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p>ไม่มีข้อมูล</p>
                <?php } ?>
            </div>
            <div class="right">
                <strong>กรรมการที่ไม่ได้ประชุม:</strong>
                <?php if (!empty($committee_absent)) { ?>
                    <ul>
                        <?php foreach ($committee_absent as $index => $name) { ?>
                            <li><?php echo htmlspecialchars($name); ?></li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p>ไม่มีข้อมูล</p>
                <?php } ?>
            </div>
        </div>

        <div class="section">
            <h2>ผู้เข้าร่วมประชุม</h2>
            <div class="section-full">
                <?php if (!empty($attendees)) { ?>
                    <ul>
                        <?php foreach ($attendees as $index => $name) { ?>
                            <li><?php echo htmlspecialchars($name); ?></li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p>ไม่มีข้อมูล</p>
                <?php } ?>
            </div>
        </div>

        <div class="section">
            <h2>รายละเอียดการประชุม</h2>
            <div class="section-full">
                <p><?php echo nl2br(htmlspecialchars($meeting['details'])); ?></p>
            </div>
        </div>

        <div class="section">
            <h2>ข้อมูลเพิ่มเติม</h2>
            <div class="left">
                <p><strong>วันที่และเวลาบันทึก:</strong> <?php echo htmlspecialchars($meeting['recorded_at']); ?></p>
            </div>
            <div class="right">
                <p><strong>ไฟล์แนบ:</strong> 
                    <?php if (!empty($meeting['file_path'])) { ?>
                        <a href="<?php echo htmlspecialchars($meeting['file_path']); ?>" target="_blank">ดาวน์โหลด</a>
                    <?php } else { ?>
                        ไม่มีไฟล์แนบ
                    <?php } ?>
                </p>
            </div>
        </div>

        <div class="button-group">
            <button class="btn btn-print" onclick="window.print()">พิมพ์หน้านี้</button>
            <a href="dashboard.php" class="btn btn-back">กลับไปหน้า Dashboard</a>
            <a href="logout.php" class="btn btn-logout">ออกจากระบบ</a>
        </div>

        <div class="footer">
            สร้างโดย Snowjor | พิมพ์เมื่อ <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>
</body>
</html>
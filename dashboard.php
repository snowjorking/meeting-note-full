<?php
session_start();
include 'config.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูล department และ role ของผู้ใช้
$username = $_SESSION['username'];
$user_query = "SELECT department, role FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $user_query);
if (!$stmt) {
    die("Error preparing user query: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);
$user_department = $user['department'];
$user_role = $user['role'];

// ลบข้อมูล
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete']; // แปลงเป็น integer เพื่อความปลอดภัย
    $query = "DELETE FROM meetings WHERE id = ? AND department = ?";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        die("Error preparing delete query: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "is", $id, $user_department);
    $success = mysqli_stmt_execute($stmt);
    if ($success) {
        mysqli_stmt_close($stmt);
        header("Location: dashboard.php?status=deleted");
        exit();
    } else {
        $error_message = "Error deleting record: " . mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
    }
}

// ดึงข้อมูลการประชุม (ขึ้นอยู่กับ role)
if ($user_role === 'superadmin') {
    $query = "SELECT * FROM meetings ORDER BY recorded_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        die("Error preparing select query: " . mysqli_error($conn));
    }
    mysqli_stmt_execute($stmt);
} else {
    $query = "SELECT * FROM meetings WHERE department = ? ORDER BY recorded_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        die("Error preparing select query: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "s", $user_department);
    mysqli_stmt_execute($stmt);
}
$result = mysqli_stmt_get_result($stmt);
$row_count = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Meeting System</title>
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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
        }
        .dashboard-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 30px;
            width: 100%;
            max-width: 1200px;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 15px;
        }
        .dashboard-header h2 {
            color: #1e3c72;
            font-size: 28px;
            font-weight: 600;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
        }
        .btn {
            padding: 10px 20px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .btn:hover {
            background: #1e3c72;
            transform: translateY(-2px);
        }
        .meeting-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        .meeting-table th,
        .meeting-table td {
            padding: 15px;
            text-align: left;
            font-size: 14px;
            color: #2c3e50;
        }
        .meeting-table th {
            background: #2a5298;
            color: white;
            font-weight: 600;
        }
        .meeting-table tr {
            border-bottom: 1px solid #e9ecef;
            transition: background 0.3s ease;
        }
        .meeting-table tr:hover {
            background: #f8f9fa;
        }
        .meeting-table td:last-child {
            text-align: center;
        }
        .btn-small {
            padding: 8px 15px;
            font-size: 13px;
            border-radius: 6px;
            text-decoration: none;
            margin: 0 5px;
        }
        .btn-edit {
            background: #f39c12;
        }
        .btn-edit:hover {
            background: #e67e22;
        }
        .btn-print {
            background: #3498db;
        }
        .btn-print:hover {
            background: #2980b9;
        }
        .btn-danger {
            background: #ff4757;
        }
        .btn-danger:hover {
            background: #e63946;
        }
        .no-data {
            text-align: center;
            font-size: 16px;
            color: #6c757d;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-top: 20px;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 14px;
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
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 20px;
                max-width: 100%;
            }
            .dashboard-header {
                flex-direction: column;
                gap: 15px;
            }
            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }
            .meeting-table {
                display: block;
                overflow-x: auto;
            }
        }
        @media (max-width: 480px) {
            .action-buttons {
                flex-direction: column;
                gap: 10px;
            }
            .btn {
                width: 100%;
                padding: 12px;
            }
        }
    </style>
    <script>
        function confirmDelete(id) {
            if (confirm("คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?")) {
                window.location.href = "dashboard.php?delete=" + id;
            }
        }
    </script>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h2>บันทึกการประชุม - ฝ่าย <?php echo htmlspecialchars($user_department); ?></h2>
            <div class="action-buttons">
                <a href="record.php" class="btn"><i class="fas fa-plus"></i> เพิ่มบันทึกใหม่</a>
                <a href="logout.php" class="btn"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
            </div>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'deleted') { ?>
            <div class="message success">ลบข้อมูลสำเร็จ!</div>
        <?php } elseif (isset($error_message)) { ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php } ?>

        <?php if ($row_count > 0) { ?>
            <table class="meeting-table">
                <thead>
                    <tr>
                        <th>หัวข้อ</th>
                        <th>ครั้งที่</th>
                        <th>วันที่ประชุม</th>
                        <th>ฝ่าย</th>
                        <th>วันที่บันทึก</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['meeting_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['meeting_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                        <td><?php echo htmlspecialchars($row['recorded_at']); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-small btn-edit">แก้ไข</a>
                            <a href="print.php?id=<?php echo $row['id']; ?>" class="btn-small btn-print">พิมพ์</a>
                            <a href="javascript:confirmDelete(<?php echo $row['id']; ?>)" class="btn-small btn-danger">ลบ</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p class="no-data">ไม่มีข้อมูลการประชุมในฝ่าย <?php echo htmlspecialchars($user_department); ?></p>
        <?php } ?>
    </div>
</body>
</html>
<?php
// กำหนดค่าการเชื่อมต่อฐานข้อมูล
$host = "localhost";       // โฮสต์ของฐานข้อมูล
$username = "ชื่อ DB";        // ชื่อผู้ใช้ฐานข้อมูล
$password = "รหัสผ่าน DB";            // รหัสผ่านฐานข้อมูล (ว่างไว้หากไม่มี)
$dbname = "ชื่อ DB";    // ชื่อฐานข้อมูล

// เริ่มการเชื่อมต่อ
$conn = mysqli_connect($host, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ตั้งค่าการเข้ารหัสอักขระ
mysqli_set_charset($conn, "utf8");

?>
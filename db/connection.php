<?php

// ฟังก์ชันเชื่อมต่อฐานข้อมูล
function initializeDatabaseConnection($host, $user, $password, $database,) {
    $conn = new mysqli($host, $user, $password, $database);
    
    // ตรวจสอบการเชื่อมต่อ
    if ($conn->connect_error) {
        throw new Exception("ไม่สามารถเชื่อมต่อกับฐานข้อมูล: " . $conn->connect_error);
    }

    // กำหนด charset เพื่อรองรับภาษาไทย
    $conn->set_charset("utf8mb4");

    return $conn;
}

?>

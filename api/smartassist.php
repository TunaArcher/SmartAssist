<?php
header("Content-Type: application/json");

require '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

require_once '../db/connection.php';
require_once '../functions/smartassist_functions.php';

try {
    // อ่านข้อมูล input
    $input = json_decode(file_get_contents("php://input"), true);
    $question = $input["question"] ?? "";

    // ตรวจสอบคำถาม
    if (empty($question)) {
        throw new Exception("ไม่มีคำถามที่ระบุ");
    }

    // เริ่มการเชื่อมต่อฐานข้อมูล
    $conn = initializeDatabaseConnection($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);

    // เริ่มเซสชันถ้ายังไม่ได้เริ่ม
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $session_id = session_id();

    // ประมวลผลคำตอบ
    $answer = getDynamicAnswerWithChatGPT($conn, $question, $session_id);

    // ส่งผลลัพธ์กลับ
    echo json_encode(["answer" => $answer]);
} catch (Exception $e) {
    // ส่งข้อความข้อผิดพลาดในรูปแบบ JSON
    echo json_encode(["answer" => "ขออภัยค่ะ เกิดข้อผิดพลาดในการประมวลผล", "error" => $e->getMessage()]);
}

<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$host = '127.0.0.1';
$dbname = 'leave_system';
$username = 'root';
$password = ''; // ค่าเริ่มต้นของ XAMPP คือค่าว่าง

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เชื่อมต่อฐานข้อมูล MySQL ไม่สำเร็จ: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonResponse(bool $success, array $data = [], string $message = '', int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function requireAuth(): array {
    if (empty($_SESSION['user'])) {
        jsonResponse(false, [], 'กรุณาเข้าสู่ระบบก่อนใช้งาน', 401);
    }
    return $_SESSION['user'];
}

function requireAdmin(): array {
    $user = requireAuth();
    if ($user['role'] !== 'admin') {
        jsonResponse(false, [], 'ไม่มีสิทธิ์เข้าถึงส่วนของผู้ดูแลระบบ', 403);
    }
    return $user;
}

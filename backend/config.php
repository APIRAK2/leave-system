<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('jsonResponse')) {
    function jsonResponse($success, $data = [], $message = '') {
        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        if (is_array($success)) {
            echo json_encode($success, JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (is_string($data)) {
            $tempMsg = $data;
            $data = is_array($message) ? $message : [];
            $message = $tempMsg;
        }
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$host = '127.0.0.1';
$username = 'root';
$password = '';
$dbname = 'leave_system';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    // สร้างตารางอัตโนมัติหากยังไม่มี
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            department VARCHAR(100) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS leave_types (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            max_days INT NOT NULL DEFAULT 10,
            description VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS leave_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            leave_type_id INT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            leave_period ENUM('full', 'morning', 'afternoon') DEFAULT 'full',
            total_days DECIMAL(4,1) NOT NULL DEFAULT 1.0,
            reason TEXT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            admin_remark VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // เพิ่มข้อมูลผู้ใช้ทดสอบเริ่มต้นหากตารางว่าง
    $userCount = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($userCount === 0) {
        $pdo->exec("
            INSERT INTO users (username, password, full_name, department, role) VALUES
            ('SNOW', '1234', 'SNOW', 'ฝ่ายบุคคล (HR)', 'admin'),
            ('SALMON', '1234', 'SALMON', 'พัฒนาระบบ (IT)', 'user'),
            ('suda', '1234', 'สุดา รักงาน', 'การตลาด (Marketing)', 'user')
        ");
    }

    // เพิ่มประเภทใบลาเริ่มต้นหากตารางว่าง
    $typeCount = (int)$pdo->query("SELECT COUNT(*) FROM leave_types")->fetchColumn();
    if ($typeCount === 0) {
        $pdo->exec("
            INSERT INTO leave_types (name, max_days, description, is_active) VALUES
            ('ลาป่วย', 30, 'ลาป่วยตามจริง (หากลาติดต่อกัน 3 วันขึ้นไปต้องมีใบรับรองแพทย์)', 1),
            ('ลากิจ', 6, 'ลากิจส่วนตัว แจ้งล่วงหน้าอย่างน้อย 1 วัน', 1),
            ('ลาพักร้อน', 10, 'ลาพักผ่อนประจำปี แจ้งล่วงหน้าอย่างน้อย 3 วัน', 1),
            ('ลาคลอดบุตร', 98, 'ลาคลอดบุตรตามสิทธิ์กฎหมายแรงงาน', 1)
        ");
    }
} catch (Exception $e) {
    jsonResponse(false, [], 'เชื่อมต่อฐานข้อมูล MySQL ไม่สำเร็จ: ' . $e->getMessage());
}
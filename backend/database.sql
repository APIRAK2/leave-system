CREATE DATABASE IF NOT EXISTS leave_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE leave_system;

-- ตารางผู้ใช้งาน
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL DEFAULT 'ทั่วไป',
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ตารางประเภทใบลา
CREATE TABLE IF NOT EXISTS leave_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    max_days INT NOT NULL DEFAULT 10,
    description VARCHAR(255) DEFAULT '',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ตารางรายการยื่นใบลา
CREATE TABLE IF NOT EXISTS leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    leave_type_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    leave_period ENUM('full', 'morning', 'afternoon') NOT NULL DEFAULT 'full',
    total_days DECIMAL(4,1) NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    admin_remark TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_leave_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_leave_type FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ข้อมูลผู้ใช้งานเริ่มต้น (รหัสผ่านคือ 1234 ทุกบัญชี)
INSERT INTO users (username, password, full_name, department, role) VALUES
('admin', '1234', 'วิภาดา บริหารงาน', 'ฝ่ายบุคคล (HR)', 'admin'),
('somchai', '1234', 'สมชาย ใจดี', 'พัฒนาระบบ (IT)', 'user'),
('suda', '1234', 'สุดา รักงาน', 'การตลาด (Marketing)', 'user');

-- ข้อมูลประเภทใบลาเริ่มต้น
INSERT INTO leave_types (name, max_days, description, is_active) VALUES
('ลาป่วย', 30, 'ลาเนื่องจากอาการเจ็บป่วยหรือพบแพทย์', 1),
('ลากิจ', 6, 'ลาเพื่อทำธุระส่วนตัวที่จำเป็น', 1),
('ลาพักร้อน', 10, 'ลาพักผ่อนประจำปี (ยื่นล่วงหน้าอย่างน้อย 3 วัน)', 1),
('ลาคลอด / ลาบวช', 90, 'ลาตามสิทธิ์กฎหมายแรงงาน', 1);

-- ตัวอย่างข้อมูลการยื่นใบลา
INSERT INTO leave_requests (user_id, leave_type_id, start_date, end_date, leave_period, total_days, reason, status, admin_remark) VALUES
(2, 1, CURDATE(), CURDATE(), 'full', 1.0, 'มีไข้สูงและปวดศีรษะ ไปพบแพทย์ที่คลินิก', 'pending', NULL),
(3, 3, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'full', 3.0, 'เดินทางไปพักผ่อนต่างจังหวัดกับครอบครัว', 'pending', NULL),
(2, 2, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'morning', 0.5, 'ติดต่อทำใบขับขี่ที่กรมการขนส่ง', 'approved', 'อนุมัติตามสิทธิ์เรียบร้อย'),
(3, 2, DATE_SUB(CURDATE(), INTERVAL 4 DAY), DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'full', 1.0, 'ติดธุระด่วนที่บ้าน', 'rejected', 'ช่วงวันดังกล่าวมีการประชุมสรุปแคมเปญประจำเดือน');

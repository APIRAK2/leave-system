<?php
require_once 'config.php';

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    // 1. ตรวจสอบสถานะการเข้าสู่ระบบ
    case 'check_auth':
        jsonResponse(true, ['user' => $_SESSION['user'] ?? null]);
        break;

    // 2. เข้าสู่ระบบ
    case 'login':
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if ($username === '' || $password === '') {
            jsonResponse(false, [], 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน');
        }

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || (!password_verify($password, $user['password']) && $user['password'] !== $password)) {
            jsonResponse(false, [], 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
        }

        unset($user['password']);
        $_SESSION['user'] = $user;
        jsonResponse(true, ['user' => $user], 'เข้าสู่ระบบสำเร็จ');
        break;

    // 3. สมัครสมาชิกใหม่
    case 'register':
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $fullName = trim($input['full_name'] ?? '');
        $department = trim($input['department'] ?? 'ทั่วไป');
        $role = ($input['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

        if ($username === '' || $password === '' || $fullName === '') {
            jsonResponse(false, [], 'กรุณากรอกข้อมูลให้ครบถ้วน');
        }

        $check = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $check->execute([$username]);
        if ($check->fetch()) {
            jsonResponse(false, [], 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว');
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password, full_name, department, role) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$username, $hashed, $fullName, $department, $role]);

        $newUser = [
            'id' => (int)$pdo->lastInsertId(),
            'username' => $username,
            'full_name' => $fullName,
            'department' => $department,
            'role' => $role
        ];
        $_SESSION['user'] = $newUser;
        jsonResponse(true, ['user' => $newUser], 'สมัครสมาชิกสำเร็จ');
        break;

    // 4. ออกจากระบบ
    case 'logout':
        session_destroy();
        jsonResponse(true, [], 'ออกจากระบบเรียบร้อย');
        break;

    // 5. โหลดข้อมูลทั้งหมดตามสิทธิ์ผู้ใช้
    case 'get_dashboard_data':
        $user = requireAuth();

        $typesStmt = $pdo->query('SELECT * FROM leave_types ORDER BY id ASC');
        $leaveTypes = $typesStmt->fetchAll();

        $myLeavesStmt = $pdo->prepare('
            SELECT lr.*, lt.name AS leave_type_name
            FROM leave_requests lr
            JOIN leave_types lt ON lr.leave_type_id = lt.id
            WHERE lr.user_id = ?
            ORDER BY lr.created_at DESC, lr.id DESC
        ');
        $myLeavesStmt->execute([$user['id']]);
        $myLeaves = $myLeavesStmt->fetchAll();

        $allLeaves = [];
        $users = [];

        if ($user['role'] === 'admin') {
            $allStmt = $pdo->query('
                SELECT lr.*, u.full_name AS user_name, u.department, u.username, lt.name AS leave_type_name
                FROM leave_requests lr
                JOIN users u ON lr.user_id = u.id
                JOIN leave_types lt ON lr.leave_type_id = lt.id
                ORDER BY FIELD(lr.status, "pending", "approved", "rejected"), lr.created_at DESC
            ');
            $allLeaves = $allStmt->fetchAll();

            $usersStmt = $pdo->query('
                SELECT u.id, u.username, u.full_name, u.department, u.role, u.created_at,
                       COUNT(lr.id) AS total_requests
                FROM users u
                LEFT JOIN leave_requests lr ON u.id = lr.user_id
                GROUP BY u.id
                ORDER BY u.id ASC
            ');
            $users = $usersStmt->fetchAll();
        }

        jsonResponse(true, [
            'leave_types' => $leaveTypes,
            'my_leaves' => $myLeaves,
            'all_leaves' => $allLeaves,
            'users' => $users
        ]);
        break;

    // 6. พนักงานยื่นใบลาใหม่
    case 'create_leave':
        $user = requireAuth();
        $leaveTypeId = (int)($input['leave_type_id'] ?? 0);
        $startDate = $input['start_date'] ?? '';
        $endDate = $input['end_date'] ?? '';
        $period = in_array($input['leave_period'] ?? '', ['full', 'morning', 'afternoon']) ? $input['leave_period'] : 'full';
        $totalDays = (float)($input['total_days'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        if (!$leaveTypeId || !$startDate || !$endDate || $totalDays <= 0 || $reason === '') {
            jsonResponse(false, [], 'กรุณากรอกข้อมูลการลาให้ครบถ้วนและถูกต้อง');
        }

        $stmt = $pdo->prepare('
            INSERT INTO leave_requests (user_id, leave_type_id, start_date, end_date, leave_period, total_days, reason, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, "pending")
        ');
        $stmt->execute([$user['id'], $leaveTypeId, $startDate, $endDate, $period, $totalDays, $reason]);
        jsonResponse(true, [], 'ยื่นใบลาเรียบร้อยแล้ว');
        break;

    // 7. แก้ไขใบลา (พนักงานแก้ใบลาตนเองที่รออนุมัติ / Admin แก้ได้ทุกรายการ)
    case 'update_leave':
        $user = requireAuth();
        $id = (int)($input['id'] ?? 0);
        $leaveTypeId = (int)($input['leave_type_id'] ?? 0);
        $startDate = $input['start_date'] ?? '';
        $endDate = $input['end_date'] ?? '';
        $period = in_array($input['leave_period'] ?? '', ['full', 'morning', 'afternoon']) ? $input['leave_period'] : 'full';
        $totalDays = (float)($input['total_days'] ?? 0);
        $reason = trim($input['reason'] ?? '');

        $checkStmt = $pdo->prepare('SELECT * FROM leave_requests WHERE id = ?');
        $checkStmt->execute([$id]);
        $existing = $checkStmt->fetch();

        if (!$existing) {
            jsonResponse(false, [], 'ไม่พบข้อมูลใบลา');
        }

        if ($user['role'] === 'admin') {
            $status = in_array($input['status'] ?? '', ['pending', 'approved', 'rejected']) ? $input['status'] : $existing['status'];
            $remark = trim($input['admin_remark'] ?? '');
            $stmt = $pdo->prepare('
                UPDATE leave_requests
                SET leave_type_id = ?, start_date = ?, end_date = ?, leave_period = ?, total_days = ?, reason = ?, status = ?, admin_remark = ?
                WHERE id = ?
            ');
            $stmt->execute([$leaveTypeId, $startDate, $endDate, $period, $totalDays, $reason, $status, $remark !== '' ? $remark : null, $id]);
        } else {
            if ((int)$existing['user_id'] !== (int)$user['id'] || $existing['status'] !== 'pending') {
                jsonResponse(false, [], 'สามารถแก้ไขได้เฉพาะใบลาของตนเองที่ยังรออนุมัติเท่านั้น');
            }
            $stmt = $pdo->prepare('
                UPDATE leave_requests
                SET leave_type_id = ?, start_date = ?, end_date = ?, leave_period = ?, total_days = ?, reason = ?
                WHERE id = ?
            ');
            $stmt->execute([$leaveTypeId, $startDate, $endDate, $period, $totalDays, $reason, $id]);
        }

        jsonResponse(true, [], 'บันทึกการแก้ไขใบลาเรียบร้อยแล้ว');
        break;

    // 8. Admin อนุมัติ / ไม่อนุมัติใบลา
    case 'update_leave_status':
        requireAdmin();
        $id = (int)($input['id'] ?? 0);
        $status = $input['status'] ?? '';
        $remark = trim($input['admin_remark'] ?? '');

        if (!in_array($status, ['approved', 'rejected', 'pending'])) {
            jsonResponse(false, [], 'สถานะไม่ถูกต้อง');
        }

        $stmt = $pdo->prepare('UPDATE leave_requests SET status = ?, admin_remark = ? WHERE id = ?');
        $stmt->execute([$status, $remark !== '' ? $remark : null, $id]);
        jsonResponse(true, [], 'อัปเดตสถานะใบลาเรียบร้อยแล้ว');
        break;

    // 9. ยกเลิก / ลบใบลา
    case 'delete_leave':
        $user = requireAuth();
        $id = (int)($input['id'] ?? 0);

        $checkStmt = $pdo->prepare('SELECT * FROM leave_requests WHERE id = ?');
        $checkStmt->execute([$id]);
        $existing = $checkStmt->fetch();

        if (!$existing) {
            jsonResponse(false, [], 'ไม่พบรายการใบลา');
        }

        if ($user['role'] !== 'admin' && ((int)$existing['user_id'] !== (int)$user['id'] || $existing['status'] !== 'pending')) {
            jsonResponse(false, [], 'ไม่สามารถลบรายการนี้ได้');
        }

        $stmt = $pdo->prepare('DELETE FROM leave_requests WHERE id = ?');
        $stmt->execute([$id]);
        jsonResponse(true, [], 'ลบรายการใบลาเรียบร้อยแล้ว');
        break;

    // 10. Admin เพิ่มหรือแก้ไขประเภทใบลา
    case 'save_leave_type':
        requireAdmin();
        $id = (int)($input['id'] ?? 0);
        $name = trim($input['name'] ?? '');
        $maxDays = (int)($input['max_days'] ?? 0);
        $description = trim($input['description'] ?? '');
        $isActive = isset($input['is_active']) ? (int)(bool)$input['is_active'] : 1;

        if ($name === '' || $maxDays <= 0) {
            jsonResponse(false, [], 'กรุณาระบุชื่อประเภทใบลาและจำนวนวันสูงสุดให้ถูกต้อง');
        }

        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE leave_types SET name = ?, max_days = ?, description = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$name, $maxDays, $description, $isActive, $id]);
            jsonResponse(true, [], 'แก้ไขประเภทใบลาเรียบร้อยแล้ว');
        } else {
            $stmt = $pdo->prepare('INSERT INTO leave_types (name, max_days, description, is_active) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $maxDays, $description, $isActive]);
            jsonResponse(true, [], 'เพิ่มประเภทใบลาใหม่เรียบร้อยแล้ว');
        }
        break;

    // 11. Admin เปิด/ปิดใช้งานประเภทใบลา
    case 'toggle_leave_type':
        requireAdmin();
        $id = (int)($input['id'] ?? 0);
        $stmt = $pdo->prepare('UPDATE leave_types SET is_active = NOT is_active WHERE id = ?');
        $stmt->execute([$id]);
        jsonResponse(true, [], 'เปลี่ยนสถานะประเภทใบลาเรียบร้อยแล้ว');
        break;

    // 12. Admin ลบประเภทใบลา
    case 'delete_leave_type':
        requireAdmin();
        $id = (int)($input['id'] ?? 0);
        try {
            $stmt = $pdo->prepare('DELETE FROM leave_types WHERE id = ?');
            $stmt->execute([$id]);
            jsonResponse(true, [], 'ลบประเภทใบลาเรียบร้อยแล้ว');
        } catch (PDOException $e) {
            jsonResponse(false, [], 'ไม่สามารถลบได้ เนื่องจากมีประวัติการยื่นใบลาในประเภทนี้อยู่');
        }
        break;

    // 13. Admin สลับสิทธิ์ผู้ใช้งาน (User <-> Admin)
    case 'toggle_user_role':
        $currentAdmin = requireAdmin();
        $targetId = (int)($input['id'] ?? 0);

        if ($targetId === (int)$currentAdmin['id']) {
            jsonResponse(false, [], 'ไม่สามารถเปลี่ยนสิทธิ์บัญชีที่กำลังใช้งานอยู่ได้');
        }

        $stmt = $pdo->prepare('UPDATE users SET role = IF(role = "admin", "user", "admin") WHERE id = ?');
        $stmt->execute([$targetId]);
        jsonResponse(true, [], 'เปลี่ยนสิทธิ์ผู้ใช้งานเรียบร้อยแล้ว');
        break;

    default:
        jsonResponse(false, [], 'ไม่พบคำสั่ง API ที่ระบุ', 404);
}

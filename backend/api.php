<?php
require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? '';
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
if (!is_array($input)) {
    $input = $_POST;
}

function requireAuth() {
    if (empty($_SESSION['user'])) {
        jsonResponse(false, [], 'กรุณาเข้าสู่ระบบก่อนทำรายการ');
    }
    return $_SESSION['user'];
}

function requireAdmin() {
    $user = requireAuth();
    if (($user['role'] ?? '') !== 'admin') {
        jsonResponse(false, [], 'คุณไม่มีสิทธิ์เข้าถึงส่วนของผู้ดูแลระบบ');
    }
    return $user;
}

try {
    switch ($action) {
        case 'check_auth':
            jsonResponse(true, ['user' => $_SESSION['user'] ?? null]);
            break;

        case 'login':
            $username = trim($input['username'] ?? '');
            $password = (string)($input['password'] ?? '');

            if ($username === '' || $password === '') {
                jsonResponse(false, [], 'กรุณากรอกชื่อผู้ใช้งานและรหัสผ่าน');
            }

            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if (!$user || (!password_verify($password, $user['password']) && $password !== $user['password'])) {
                jsonResponse(false, [], 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง');
            }

            unset($user['password']);
            $_SESSION['user'] = $user;
            jsonResponse(true, ['user' => $user], 'เข้าสู่ระบบสำเร็จ');
            break;

        case 'register':
            $fullName = trim($input['full_name'] ?? '');
            $department = trim($input['department'] ?? '');
            $username = trim($input['username'] ?? '');
            $password = (string)($input['password'] ?? '');
            $role = ($input['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

            if ($fullName === '' || $department === '' || $username === '' || $password === '') {
                jsonResponse(false, [], 'กรุณากรอกข้อมูลให้ครบทุกช่อง');
            }

            $check = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $check->execute([$username]);
            if ($check->fetch()) {
                jsonResponse(false, [], 'Username นี้ถูกใช้งานแล้ว');
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, department, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $hashed, $fullName, $department, $role]);

            $newId = $pdo->lastInsertId();
            $user = [
                'id' => (int)$newId,
                'username' => $username,
                'full_name' => $fullName,
                'department' => $department,
                'role' => $role
            ];
            $_SESSION['user'] = $user;
            jsonResponse(true, ['user' => $user], 'สร้างบัญชีและเข้าสู่ระบบสำเร็จ');
            break;

        case 'logout':
            $_SESSION = [];
            session_destroy();
            jsonResponse(true, [], 'ออกจากระบบเรียบร้อยแล้ว');
            break;

        case 'get_dashboard_data':
            $user = requireAuth();
            $userId = (int)$user['id'];
            $isAdmin = ($user['role'] ?? '') === 'admin';

            $leaveTypes = $pdo->query("SELECT * FROM leave_types ORDER BY id ASC")->fetchAll();

            $stmtMy = $pdo->prepare("
                SELECT l.*, COALESCE(lt.name, 'ไม่ระบุประเภท') AS leave_type_name
                FROM leave_requests l
                LEFT JOIN leave_types lt ON l.leave_type_id = lt.id
                WHERE l.user_id = ?
                ORDER BY l.id DESC
            ");
            $stmtMy->execute([$userId]);
            $myLeaves = $stmtMy->fetchAll();

            $allLeaves = [];
            $usersList = [];

            if ($isAdmin) {
                $allLeaves = $pdo->query("
                    SELECT l.*,
                           COALESCE(u.full_name, 'ไม่ทราบชื่อ') AS user_name,
                           COALESCE(u.department, '-') AS department,
                           COALESCE(lt.name, 'ไม่ระบุประเภท') AS leave_type_name
                    FROM leave_requests l
                    LEFT JOIN users u ON l.user_id = u.id
                    LEFT JOIN leave_types lt ON l.leave_type_id = lt.id
                    ORDER BY l.id DESC
                ")->fetchAll();

                $usersList = $pdo->query("
                    SELECT u.id, u.username, u.full_name, u.department, u.role, u.created_at,
                           (SELECT COUNT(*) FROM leave_requests lr WHERE lr.user_id = u.id) AS total_requests
                    FROM users u
                    ORDER BY u.id ASC
                ")->fetchAll();
            }

            jsonResponse(true, [
                'leave_types' => $leaveTypes,
                'my_leaves' => $myLeaves,
                'all_leaves' => $allLeaves,
                'users' => $usersList
            ]);
            break;

        case 'create_leave':
            $user = requireAuth();
            $leaveTypeId = (int)($input['leave_type_id'] ?? 0);
            $startDate = $input['start_date'] ?? '';
            $endDate = $input['end_date'] ?? '';
            $leavePeriod = $input['leave_period'] ?? 'full';
            $totalDays = (float)($input['total_days'] ?? 1);
            $reason = trim($input['reason'] ?? '');

            if ($leaveTypeId <= 0 || $startDate === '' || $endDate === '' || $reason === '') {
                jsonResponse(false, [], 'กรุณากรอกข้อมูลการลาให้ครบถ้วน');
            }

            $stmt = $pdo->prepare("
                INSERT INTO leave_requests (user_id, leave_type_id, start_date, end_date, leave_period, total_days, reason, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([(int)$user['id'], $leaveTypeId, $startDate, $endDate, $leavePeriod, $totalDays, $reason]);
            jsonResponse(true, [], 'ส่งคำขอใบลาเรียบร้อยแล้ว');
            break;

        case 'update_leave':
            $user = requireAuth();
            $id = (int)($input['id'] ?? 0);
            $leaveTypeId = (int)($input['leave_type_id'] ?? 0);
            $startDate = $input['start_date'] ?? '';
            $endDate = $input['end_date'] ?? '';
            $leavePeriod = $input['leave_period'] ?? 'full';
            $totalDays = (float)($input['total_days'] ?? 1);
            $reason = trim($input['reason'] ?? '');

            if (($user['role'] ?? '') === 'admin') {
                $status = $input['status'] ?? 'pending';
                $adminRemark = trim($input['admin_remark'] ?? '');
                $stmt = $pdo->prepare("
                    UPDATE leave_requests
                    SET leave_type_id = ?, start_date = ?, end_date = ?, leave_period = ?, total_days = ?, reason = ?, status = ?, admin_remark = ?
                    WHERE id = ?
                ");
                $stmt->execute([$leaveTypeId, $startDate, $endDate, $leavePeriod, $totalDays, $reason, $status, $adminRemark, $id]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE leave_requests
                    SET leave_type_id = ?, start_date = ?, end_date = ?, leave_period = ?, total_days = ?, reason = ?
                    WHERE id = ? AND user_id = ? AND status = 'pending'
                ");
                $stmt->execute([$leaveTypeId, $startDate, $endDate, $leavePeriod, $totalDays, $reason, $id, (int)$user['id']]);
            }
            jsonResponse(true, [], 'บันทึกการแก้ไขใบลาเรียบร้อยแล้ว');
            break;

        case 'delete_leave':
            $user = requireAuth();
            $id = (int)($input['id'] ?? 0);

            if (($user['role'] ?? '') === 'admin') {
                $stmt = $pdo->prepare("DELETE FROM leave_requests WHERE id = ?");
                $stmt->execute([$id]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM leave_requests WHERE id = ? AND user_id = ? AND status = 'pending'");
                $stmt->execute([$id, (int)$user['id']]);
            }
            jsonResponse(true, [], 'ยกเลิก/ลบรายการใบลาเรียบร้อยแล้ว');
            break;

        case 'update_leave_status':
            requireAdmin();
            $id = (int)($input['id'] ?? 0);
            $status = $input['status'] ?? 'pending';
            $adminRemark = trim($input['admin_remark'] ?? '');

            $stmt = $pdo->prepare("UPDATE leave_requests SET status = ?, admin_remark = ? WHERE id = ?");
            $stmt->execute([$status, $adminRemark, $id]);
            jsonResponse(true, [], 'อัปเดตสถานะใบลาเรียบร้อยแล้ว');
            break;

        case 'save_leave_type':
            requireAdmin();
            $id = (int)($input['id'] ?? 0);
            $name = trim($input['name'] ?? '');
            $maxDays = (int)($input['max_days'] ?? 1);
            $description = trim($input['description'] ?? '');

            if ($name === '' || $maxDays <= 0) {
                jsonResponse(false, [], 'กรุณากรอกชื่อประเภทใบลาและจำนวนวันให้ถูกต้อง');
            }

            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE leave_types SET name = ?, max_days = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $maxDays, $description, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO leave_types (name, max_days, description, is_active) VALUES (?, ?, ?, 1)");
                $stmt->execute([$name, $maxDays, $description]);
            }
            jsonResponse(true, [], 'บันทึกประเภทใบลาเรียบร้อยแล้ว');
            break;

        case 'toggle_leave_type':
            requireAdmin();
            $id = (int)($input['id'] ?? 0);
            $stmt = $pdo->prepare("UPDATE leave_types SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(true, [], 'เปลี่ยนสถานะประเภทใบลาเรียบร้อยแล้ว');
            break;

        case 'delete_leave_type':
            requireAdmin();
            $id = (int)($input['id'] ?? 0);
            $check = $pdo->prepare("SELECT COUNT(*) FROM leave_requests WHERE leave_type_id = ?");
            $check->execute([$id]);
            if ((int)$check->fetchColumn() > 0) {
                jsonResponse(false, [], 'ไม่สามารถลบได้เนื่องจากมีรายการใบลาใช้ประเภทนี้อยู่ (แนะนำให้กดปิดใช้งานแทน)');
            }
            $stmt = $pdo->prepare("DELETE FROM leave_types WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(true, [], 'ลบประเภทใบลาเรียบร้อยแล้ว');
            break;

        case 'toggle_user_role':
            $admin = requireAdmin();
            $id = (int)($input['id'] ?? 0);
            if ($id === (int)$admin['id']) {
                jsonResponse(false, [], 'ไม่สามารถเปลี่ยนสิทธิ์บัญชีที่กำลังใช้งานอยู่ได้');
            }
            $stmt = $pdo->prepare("UPDATE users SET role = IF(role = 'admin', 'user', 'admin') WHERE id = ?");
            $stmt->execute([$id]);
            jsonResponse(true, [], 'เปลี่ยนสิทธิ์ผู้ใช้งานเรียบร้อยแล้ว');
            break;

        default:
            jsonResponse(false, [], 'ไม่พบคำสั่ง API ที่ร้องขอ');
    }
} catch (Exception $e) {
    jsonResponse(false, [], 'ข้อผิดพลาดจากระบบ: ' . $e->getMessage());
}
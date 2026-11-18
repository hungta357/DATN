<?php

namespace App\Models;

use App\Core\BaseModel;

/**
 * NHÁNH 1: ROLES / USERS / CINEMA INFO
 * Bảng: roles, users, user_roles, cinema_info
 */
class UserModel extends BaseModel
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        // Chuẩn hóa email để tránh trường hợp đăng ký/đăng nhập khác chữ hoa-thường.
        $email = trim($email);

        // Chỉ khởi tạo tài khoản quản trị khi thực sự đăng nhập bằng admin.
        if (strtolower($email) === 'admin') {
            $email = 'admin@cinema.vn';
        }
        if (strtolower($email) === 'admin@cinema.vn') {
            $this->ensureDedicatedAdmin();
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user) {
            $user['roles'] = $this->getUserRoleNames((int) $user['id']);
        }
        return $user ?: null;
    }

    /**
     * Đảm bảo tài khoản Quản trị viên (Admin) chuyên biệt luôn tồn tại trong DB
     * Tài khoản mặc định: admin@cinema.vn (hoặc admin) | Mật khẩu riêng: admin123
     */
    public function ensureDedicatedAdmin(): void
    {
        try {
            // Đảm bảo role admin tồn tại
            $this->db->exec("INSERT IGNORE INTO roles (id, name, display_name, description) VALUES (1, 'admin', 'Quan tri vien', 'Quan ly toan bo he thong')");

            $stmt = $this->db->prepare("SELECT id, password FROM users WHERE email = 'admin@cinema.vn' LIMIT 1");
            $stmt->execute();
            $admin = $stmt->fetch();

            // Hash mặc định cũ của Laravel sample ('password')
            $oldSampleHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

            if (!$admin) {
                $hash = password_hash('admin123', PASSWORD_BCRYPT);
                $stmtInsert = $this->db->prepare("
                    INSERT INTO users (name, email, phone, password, status, created_at, updated_at)
                    VALUES ('Quản Trị Viên Hệ Thống', 'admin@cinema.vn', '0901000001', :pw, 'active', NOW(), NOW())
                ");
                $stmtInsert->execute(['pw' => $hash]);
                $adminId = (int) $this->db->lastInsertId();
            } else {
                $adminId = (int) $admin['id'];
                // Nếu đang dùng mật khẩu mẫu cũ ('password'), cập nhật sang mật khẩu riêng của Admin ('admin123')
                if ($admin['password'] === $oldSampleHash) {
                    $newHash = password_hash('admin123', PASSWORD_BCRYPT);
                    $stmtUpd = $this->db->prepare("UPDATE users SET password = :pw, updated_at = NOW() WHERE id = :id");
                    $stmtUpd->execute(['pw' => $newHash, 'id' => $adminId]);
                }
            }

            // Đảm bảo gán quyền admin
            $stmtRole = $this->db->prepare("SELECT id FROM roles WHERE name = 'admin' LIMIT 1");
            $stmtRole->execute();
            $role = $stmtRole->fetch();
            if ($role) {
                $stmtUr = $this->db->prepare("INSERT IGNORE INTO user_roles (user_id, role_id, created_at) VALUES (:uid, :rid, NOW())");
                $stmtUr->execute(['uid' => $adminId, 'rid' => $role['id']]);
            }
        } catch (\Throwable $e) {
            // Bỏ qua nếu DB chưa khởi tạo xong
        }
    }

    public function updateAdminPassword(int $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE users SET password = :pw, updated_at = NOW() WHERE id = :id");
        return $stmt->execute(['pw' => $hash, 'id' => $userId]);
    }

    public function getUserRoleNames(int $userId): array
    {
        $sql = "SELECT r.name 
                FROM roles r
                INNER JOIN user_roles ur ON ur.role_id = r.id
                WHERE ur.user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return array_column($stmt->fetchAll(), 'name');
    }

    public function registerCustomer(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $email = strtolower(trim((string) ($data['email'] ?? '')));
            $phone = trim((string) ($data['phone'] ?? ''));

            // Tạo role customer nếu database cũ chưa có dữ liệu role mặc định.
            $this->db->exec("INSERT IGNORE INTO roles (name, display_name, description, created_at, updated_at) VALUES ('customer', 'Khach hang', 'Dat ve va su dung dich vu', NOW(), NOW())");
            $stmtRole = $this->db->prepare("SELECT id FROM roles WHERE name = 'customer' LIMIT 1");
            $stmtRole->execute();
            $role = $stmtRole->fetch();
            if (!$role) {
                throw new \RuntimeException('Không tìm thấy vai trò Customer trong database.');
            }

            $passwordHash = password_hash((string) $data['password'], PASSWORD_DEFAULT);
            if ($passwordHash === false) {
                throw new \RuntimeException('Không thể mã hóa mật khẩu.');
            }

            $userId = $this->create([
                'name' => trim((string) $data['name']),
                'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'password' => $passwordHash,
                'status' => 'active',
            ]);

            // Gán quyền Customer bắt buộc; không báo đăng ký thành công nếu thiếu role.
            $stmtUr = $this->db->prepare("INSERT INTO user_roles (user_id, role_id, created_at) VALUES (:uid, :rid, NOW())");
            $stmtUr->execute(['uid' => $userId, 'rid' => (int) $role['id']]);

            // Tạo ví nội bộ mặc định cho tài khoản mới.
            $stmtWallet = $this->db->prepare("INSERT INTO wallets (user_id, balance, currency, created_at, updated_at) VALUES (:uid, 0, 'VND', NOW(), NOW())");
            $stmtWallet->execute(['uid' => $userId]);

            // Xác minh dữ liệu đã lưu trước khi commit để tránh báo thành công giả.
            $stmtVerify = $this->db->prepare("SELECT password, status FROM users WHERE id = :id LIMIT 1");
            $stmtVerify->execute(['id' => $userId]);
            $savedUser = $stmtVerify->fetch();
            if (!$savedUser || $savedUser['status'] !== 'active' || !password_verify((string) $data['password'], $savedUser['password'])) {
                throw new \RuntimeException('Không xác minh được tài khoản vừa tạo.');
            }

            $this->db->commit();
            return $userId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getAllUsersWithRoles(): array
    {
        $sql = "SELECT u.*, GROUP_CONCAT(r.display_name SEPARATOR ', ') AS role_names
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r ON r.id = ur.role_id
                GROUP BY u.id
                ORDER BY u.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getCinemaInfo(): array
    {
        $stmt = $this->db->query("SELECT * FROM cinema_info WHERE id = 1 LIMIT 1");
        return $stmt->fetch() ?: [
            'id' => 1,
            'name' => 'CineNow',
            'address' => 'Chưa cập nhật địa chỉ',
            'phone' => '1900 636807',
            'email' => 'cskh@cinenow.vn',
        ];
    }

    public function updateCinemaInfo(array $data): bool
    {
        $sql = "UPDATE cinema_info 
                SET name = :name, address = :address, phone = :phone, email = :email, description = :description, updated_at = NOW()
                WHERE id = 1";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'address' => $data['address'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }
}


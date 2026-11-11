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
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if ($user) {
            $user['roles'] = $this->getUserRoleNames((int) $user['id']);
        }
        return $user ?: null;
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
            $userId = $this->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => password_hash($data['password'], PASSWORD_BCRYPT),
                'status' => 'active',
            ]);

            // Gán role customer mặc định
            $stmtRole = $this->db->prepare("SELECT id FROM roles WHERE name = 'customer' LIMIT 1");
            $stmtRole->execute();
            $role = $stmtRole->fetch();
            if ($role) {
                $stmtUr = $this->db->prepare("INSERT INTO user_roles (user_id, role_id, created_at) VALUES (:uid, :rid, NOW())");
                $stmtUr->execute(['uid' => $userId, 'rid' => $role['id']]);
            }

            // Tạo ví nội bộ (wallets) mặc định cho user
            $stmtWallet = $this->db->prepare("INSERT INTO wallets (user_id, balance, currency, created_at, updated_at) VALUES (:uid, 0, 'VND', NOW(), NOW())");
            $stmtWallet->execute(['uid' => $userId]);

            $this->db->commit();
            return $userId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
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
            'name' => 'Cinema Booking',
            'address' => 'Chưa cập nhật địa chỉ',
            'phone' => '1900 0000',
            'email' => 'contact@cinema.vn',
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

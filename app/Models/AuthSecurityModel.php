<?php

namespace App\Models;

use App\Core\BaseModel;

class AuthSecurityModel extends BaseModel
{
    public function getLoginLockRemaining(string $attemptKey): int
    {
        $stmt = $this->db->prepare(
            'SELECT GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), locked_until))
             FROM auth_login_attempts
             WHERE attempt_key = :attempt_key AND locked_until > NOW()
             LIMIT 1'
        );
        $stmt->execute(['attempt_key' => $attemptKey]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function recordFailedLogin(string $attemptKey): int
    {
        $this->db->beginTransaction();
        try {
            $insert = $this->db->prepare(
                'INSERT IGNORE INTO auth_login_attempts (attempt_key, failed_attempts, locked_until, updated_at)
                 VALUES (:attempt_key, 0, NULL, NOW())'
            );
            $insert->execute(['attempt_key' => $attemptKey]);

            $select = $this->db->prepare(
                'SELECT failed_attempts, locked_until
                 FROM auth_login_attempts
                 WHERE attempt_key = :attempt_key
                 FOR UPDATE'
            );
            $select->execute(['attempt_key' => $attemptKey]);
            $attempt = $select->fetch();
            if (!$attempt) {
                throw new \RuntimeException('Không thể ghi nhận lần đăng nhập thất bại.');
            }

            $failedAttempts = (int) $attempt['failed_attempts'];
            if ($attempt['locked_until'] !== null && strtotime((string) $attempt['locked_until']) <= time()) {
                $failedAttempts = 0;
            }
            $failedAttempts++;
            $isLocked = $failedAttempts >= 5;

            $update = $this->db->prepare(
                'UPDATE auth_login_attempts
                 SET failed_attempts = :failed_attempts,
                     locked_until = ' . ($isLocked ? 'DATE_ADD(NOW(), INTERVAL 15 MINUTE)' : 'NULL') . ',
                     updated_at = NOW()
                 WHERE attempt_key = :attempt_key'
            );
            $update->execute([
                'failed_attempts' => $isLocked ? 5 : $failedAttempts,
                'attempt_key' => $attemptKey,
            ]);
            $this->db->commit();

            return $isLocked ? 900 : 0;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function clearLoginFailures(string $attemptKey): void
    {
        $stmt = $this->db->prepare('DELETE FROM auth_login_attempts WHERE attempt_key = :attempt_key');
        $stmt->execute(['attempt_key' => $attemptKey]);
    }
}

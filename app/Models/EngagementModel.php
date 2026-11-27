<?php

namespace App\Models;

use App\Core\BaseModel;
use Exception;

/**
 * NHÁNH 7: CONCESSIONS / COUPONS / REVIEWS / WISHLISTS / NOTIFICATIONS
 * Bảng: concessions, booking_concessions, coupons, coupon_usages, reviews, wishlists, notifications
 */
class EngagementModel extends BaseModel
{
    protected string $table = 'concessions';

    // --- 7.1 CONCESSIONS (BẮP NƯỚC) ---
    public function getActiveConcessions(): array
    {
        return $this->db->query("SELECT * FROM concessions WHERE status = 'active' AND stock > 0 ORDER BY sort_order ASC, id ASC")->fetchAll();
    }

    public function getAllConcessions(): array
    {
        return $this->db->query("SELECT * FROM concessions ORDER BY sort_order ASC, id DESC")->fetchAll();
    }

    // --- 7.2 COUPONS (MÃ GIẢM GIÁ) ---
    public function getAllCoupons(): array
    {
        return $this->db->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
    }

    public function validateCoupon(string $code, float $orderAmount): array
    {
        $stmt = $this->db->prepare("SELECT * FROM coupons WHERE code = :code AND status = 'active' LIMIT 1 FOR UPDATE");
        $stmt->execute(['code' => $code]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            throw new Exception("Mã giảm giá '{$code}' không tồn tại hoặc đã ngừng hoạt động.");
        }

        $now = time();
        if (!empty($coupon['starts_at']) && strtotime($coupon['starts_at']) > $now) {
            throw new Exception("Mã giảm giá '{$code}' chưa đến thời gian áp dụng.");
        }
        if (!empty($coupon['expires_at']) && strtotime($coupon['expires_at']) < $now) {
            throw new Exception("Mã giảm giá '{$code}' đã hết hạn.");
        }
        if ($coupon['usage_limit'] !== null && (int) $coupon['used_count'] >= (int) $coupon['usage_limit']) {
            throw new Exception("Mã giảm giá '{$code}' đã hết lượt sử dụng.");
        }
        if ($orderAmount < (float) $coupon['min_order_amount']) {
            throw new Exception('Đơn hàng tối thiểu phải từ ' . number_format((float) $coupon['min_order_amount']) . 'đ để dùng mã này.');
        }

        if ($coupon['discount_type'] === 'percent') {
            $discount = $orderAmount * ((float) $coupon['discount_value'] / 100);
            if ($coupon['max_discount_amount'] !== null) {
                $discount = min($discount, (float) $coupon['max_discount_amount']);
            }
        } else {
            $discount = (float) $coupon['discount_value'];
        }

        $discount = min($discount, $orderAmount);

        return [
            'coupon' => $coupon,
            'discount_amount' => $discount,
        ];
    }

    // --- 7.3 REVIEWS (ĐÁNH GIÁ PHIM) ---
    /** Mỗi tài khoản chỉ 1 đánh giá / phim (UNIQUE user_id + movie_id). */
    public function getUserReviewForMovie(int $userId, int $movieId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT rv.*, u.name AS user_name
            FROM reviews rv
            INNER JOIN users u ON u.id = rv.user_id
            WHERE rv.user_id = :uid AND rv.movie_id = :mid
            LIMIT 1
        ");
        $stmt->execute(['uid' => $userId, 'mid' => $movieId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getMovieReviews(int $movieId, int $page = 1, int $perPage = 5): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("
            SELECT COUNT(*) FROM reviews
            WHERE movie_id = :mid AND status = 'approved'
        ");
        $countStmt->execute(['mid' => $movieId]);
        $total = (int) $countStmt->fetchColumn();

        $sql = "SELECT rv.*, u.name AS user_name
                FROM reviews rv
                INNER JOIN users u ON u.id = rv.user_id
                WHERE rv.movie_id = :mid AND rv.status = 'approved'
                ORDER BY rv.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['mid' => $movieId]);
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Lưu đánh giá: mỗi user chỉ 1 bản ghi / phim.
     * @return string 'created' | 'updated'
     */
    public function saveReview(int $userId, int $movieId, int $rating, string $comment): string
    {
        $rating = max(1, min(10, $rating));
        $existing = $this->getUserReviewForMovie($userId, $movieId);

        if ($existing) {
            $sql = "UPDATE reviews
                    SET rating = :rating, comment = :comment, status = 'approved', updated_at = NOW()
                    WHERE user_id = :uid AND movie_id = :mid";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'rating' => $rating,
                'comment' => $comment,
                'uid' => $userId,
                'mid' => $movieId,
            ]);
            return 'updated';
        }

        $sql = "INSERT INTO reviews (user_id, movie_id, rating, comment, status, created_at, updated_at)
                VALUES (:uid, :mid, :rating, :comment, 'approved', NOW(), NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'uid' => $userId,
            'mid' => $movieId,
            'rating' => $rating,
            'comment' => $comment,
        ]);
        return 'created';
    }

    /** Chỉ chủ đánh giá mới xóa được. */
    public function deleteReview(int $userId, int $reviewId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = :id AND user_id = :uid");
        $stmt->execute(['id' => $reviewId, 'uid' => $userId]);
        return $stmt->rowCount() > 0;
    }

    // --- 7.4 WISHLISTS (PHIM YÊU THÍCH) ---
    public function toggleWishlist(int $userId, int $movieId): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM wishlists WHERE user_id = :uid AND movie_id = :mid LIMIT 1");
        $stmt->execute(['uid' => $userId, 'mid' => $movieId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $this->db->prepare("DELETE FROM wishlists WHERE id = :id")->execute(['id' => $existing['id']]);
            return false; // Đã bỏ yêu thích
        }

        $this->db->prepare("INSERT INTO wishlists (user_id, movie_id, created_at, updated_at) VALUES (:uid, :mid, NOW(), NOW())")
                 ->execute(['uid' => $userId, 'mid' => $movieId]);
        return true; // Đã thêm yêu thích
    }

    public function getUserWishlist(int $userId): array
    {
        $sql = "SELECT m.*, w.created_at AS wishlisted_at
                FROM wishlists w
                INNER JOIN movies m ON m.id = w.movie_id
                WHERE w.user_id = :uid
                ORDER BY w.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    
    public function createContactMessage(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO contact_messages (user_id, full_name, email, phone, subject, message, status, created_at, updated_at) VALUES (:user_id, :full_name, :email, :phone, :subject, :message, 'new', NOW(), NOW())");
        $stmt->execute([
            'user_id' => $data['user_id'] ?: null,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getAllContactMessages(): array
    {
        return $this->db->query("SELECT * FROM contact_messages ORDER BY FIELD(status, 'new', 'processing', 'replied', 'closed'), created_at DESC")->fetchAll();
    }

    public function updateContactMessageStatus(int $id, string $status, string $reply = ''): bool
    {
        $stmt = $this->db->prepare("UPDATE contact_messages SET status = :status, admin_reply = :reply, replied_at = CASE WHEN :status2 IN ('replied','closed') THEN NOW() ELSE replied_at END, updated_at = NOW() WHERE id = :id");
        return $stmt->execute(['status' => $status, 'status2' => $status, 'reply' => $reply ?: null, 'id' => $id]);
    }

    // --- 7.5 NOTIFICATIONS (THÔNG BÁO KHÁCH HÀNG) ---
    public function getUserNotifications(int $userId, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid");
        $countStmt->execute(['uid' => $userId]);
        $total = (int) $countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT * FROM notifications
            WHERE user_id = :uid
            ORDER BY created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute(['uid' => $userId]);
        $items = $stmt->fetchAll();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function countUnreadNotifications(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND read_at IS NULL");
        $stmt->execute(['uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markNotificationRead(int $userId, int $notificationId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET read_at = NOW(), updated_at = NOW()
            WHERE id = :id AND user_id = :uid AND read_at IS NULL
        ");
        $stmt->execute(['id' => $notificationId, 'uid' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function markAllNotificationsRead(int $userId): void
    {
        $this->db->prepare("UPDATE notifications SET read_at = NOW(), updated_at = NOW() WHERE user_id = :uid AND read_at IS NULL")
                 ->execute(['uid' => $userId]);
    }

    public function createNotification(int $userId, string $title, string $message, string $type = 'system'): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, type, created_at, updated_at)
            VALUES (:uid, :title, :message, :type, NOW(), NOW())
        ");
        $stmt->execute([
            'uid' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
        return (int) $this->db->lastInsertId();
    }

    // --- 7.6 CINEMA / FACILITY REVIEWS (ĐÁNH GIÁ RẠP & CƠ SỞ VẬT CHẤT) ---

    public function getUserCinemaReview(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT cr.*, u.name AS user_name
            FROM cinema_reviews cr
            INNER JOIN users u ON u.id = cr.user_id
            WHERE cr.user_id = :uid
            LIMIT 1
        ");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getCinemaReviews(int $page = 1, int $perPage = 5): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(50, $perPage));
        $offset = ($page - 1) * $perPage;

        $total = (int) $this->db->query("SELECT COUNT(*) FROM cinema_reviews WHERE status = 'approved'")->fetchColumn();

        $sql = "SELECT cr.*, u.name AS user_name
                FROM cinema_reviews cr
                INNER JOIN users u ON u.id = cr.user_id
                WHERE cr.status = 'approved'
                ORDER BY cr.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $items = $this->db->query($sql)->fetchAll();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /** Điểm trung bình các tiêu chí đánh giá rạp. */
    public function getCinemaReviewStats(): array
    {
        $sql = "SELECT
                    COUNT(*) AS total_reviews,
                    ROUND(AVG(rating_overall), 1) AS avg_overall,
                    ROUND(AVG(rating_facility), 1) AS avg_facility,
                    ROUND(AVG(rating_cleanliness), 1) AS avg_cleanliness,
                    ROUND(AVG(rating_sound), 1) AS avg_sound,
                    ROUND(AVG(rating_service), 1) AS avg_service
                FROM cinema_reviews
                WHERE status = 'approved'";
        $row = $this->db->query($sql)->fetch() ?: [];
        return [
            'total_reviews' => (int) ($row['total_reviews'] ?? 0),
            'avg_overall' => (float) ($row['avg_overall'] ?? 0),
            'avg_facility' => (float) ($row['avg_facility'] ?? 0),
            'avg_cleanliness' => (float) ($row['avg_cleanliness'] ?? 0),
            'avg_sound' => (float) ($row['avg_sound'] ?? 0),
            'avg_service' => (float) ($row['avg_service'] ?? 0),
        ];
    }

    /**
     * Mỗi tài khoản chỉ 1 đánh giá rạp.
     * @return string 'created'|'updated'
     */
    public function saveCinemaReview(int $userId, array $data): string
    {
        $clamp = static fn($v) => max(1, min(10, (int) $v));
        $payload = [
            'uid' => $userId,
            'rating_overall' => $clamp($data['rating_overall'] ?? 10),
            'rating_facility' => $clamp($data['rating_facility'] ?? 10),
            'rating_cleanliness' => $clamp($data['rating_cleanliness'] ?? 10),
            'rating_sound' => $clamp($data['rating_sound'] ?? 10),
            'rating_service' => $clamp($data['rating_service'] ?? 10),
            'comment' => trim((string) ($data['comment'] ?? '')),
        ];

        $existing = $this->getUserCinemaReview($userId);
        if ($existing) {
            $sql = "UPDATE cinema_reviews SET
                        rating_overall = :rating_overall,
                        rating_facility = :rating_facility,
                        rating_cleanliness = :rating_cleanliness,
                        rating_sound = :rating_sound,
                        rating_service = :rating_service,
                        comment = :comment,
                        status = 'approved',
                        updated_at = NOW()
                    WHERE user_id = :uid";
            $this->db->prepare($sql)->execute($payload);
            return 'updated';
        }

        $sql = "INSERT INTO cinema_reviews
                    (user_id, rating_overall, rating_facility, rating_cleanliness, rating_sound, rating_service, comment, status, created_at, updated_at)
                VALUES
                    (:uid, :rating_overall, :rating_facility, :rating_cleanliness, :rating_sound, :rating_service, :comment, 'approved', NOW(), NOW())";
        $this->db->prepare($sql)->execute($payload);
        return 'created';
    }

    public function deleteCinemaReview(int $userId, int $reviewId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM cinema_reviews WHERE id = :id AND user_id = :uid");
        $stmt->execute(['id' => $reviewId, 'uid' => $userId]);
        return $stmt->rowCount() > 0;
    }
}


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
        $stmt = $this->db->prepare("SELECT * FROM coupons WHERE code = :code AND status = 'active' LIMIT 1");
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
    public function getMovieReviews(int $movieId): array
    {
        $sql = "SELECT rv.*, u.name AS user_name
                FROM reviews rv
                INNER JOIN users u ON u.id = rv.user_id
                WHERE rv.movie_id = :mid AND rv.status = 'approved'
                ORDER BY rv.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['mid' => $movieId]);
        return $stmt->fetchAll();
    }

    public function saveReview(int $userId, int $movieId, int $rating, string $comment): void
    {
        $rating = max(1, min(10, $rating));
        $sql = "INSERT INTO reviews (user_id, movie_id, rating, comment, status, created_at, updated_at)
                VALUES (:uid, :mid, :rating, :comment, 'approved', NOW(), NOW())
                ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), updated_at = NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'uid' => $userId,
            'mid' => $movieId,
            'rating' => $rating,
            'comment' => $comment,
        ]);
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

    // --- 7.5 NOTIFICATIONS (THÔNG BÁO) ---
    public function getUserNotifications(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 30");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function markAllNotificationsRead(int $userId): void
    {
        $this->db->prepare("UPDATE notifications SET read_at = NOW(), updated_at = NOW() WHERE user_id = :uid AND read_at IS NULL")
                 ->execute(['uid' => $userId]);
    }
}

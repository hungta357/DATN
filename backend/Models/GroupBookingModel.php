<?php

namespace App\Models;

use App\Core\BaseModel;

class GroupBookingModel extends BaseModel
{
    protected string $table = 'group_bookings';
    private static bool $schemaEnsured = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        if (self::$schemaEnsured) {
            return;
        }
        self::$schemaEnsured = true;
        try {
            $sqlFile = dirname(__DIR__, 2) . '/database/group_booking_feature.sql';
            if (is_file($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                if ($sql !== false) {
                    $this->db->exec($sql);
                }
            }
        } catch (\Throwable $e) {
            error_log('[GroupBookingModel::ensureSchema] ' . $e->getMessage());
        }
    }

    public function getUpcomingShows(): array
    {
        $showModel = new ShowModel();
        $showModel->getSellingShowsGroupedByMovie();

        return $this->db->query("
            SELECT sh.id, sh.start_time, m.title AS movie_title, m.title_vi, r.name AS room_name,
                   (SELECT COUNT(*) FROM show_seats ss
                    WHERE ss.show_id = sh.id
                      AND (ss.status = 'available' OR (ss.status = 'held' AND ss.hold_expires_at <= NOW()))) AS available_seats
            FROM shows sh
            INNER JOIN movies m ON m.id = sh.movie_id
            INNER JOIN rooms r ON r.id = sh.room_id
            WHERE sh.status IN ('scheduled', 'selling') AND sh.start_time > NOW()
            ORDER BY sh.start_time
        ")->fetchAll();
    }

    public function createGroup(int $ownerId, int $showId, int $memberLimit, string $paymentMode): string
    {
        if ($memberLimit < 2 || $memberLimit > 8) {
            throw new \InvalidArgumentException('Một nhóm cần từ 2 đến 8 thành viên, bao gồm người tạo.');
        }
        if (!in_array($paymentMode, ['shared', 'split'], true)) {
            throw new \InvalidArgumentException('Hình thức thanh toán nhóm không hợp lệ.');
        }

        $this->db->beginTransaction();
        try {
            $stmtShow = $this->db->prepare("
                SELECT id FROM shows
                WHERE id = :id AND status IN ('scheduled', 'selling') AND start_time > NOW()
                FOR UPDATE
            ");
            $stmtShow->execute(['id' => $showId]);
            if (!$stmtShow->fetchColumn()) {
                throw new \InvalidArgumentException('Suất chiếu không còn mở để tạo nhóm.');
            }

            $inviteCode = strtoupper(bin2hex(random_bytes(6)));
            $stmt = $this->db->prepare("
                INSERT INTO group_bookings
                    (invite_code, created_by, show_id, member_limit, payment_mode, status, expires_at, created_at, updated_at)
                VALUES (:code, :owner_id, :show_id, :member_limit, :payment_mode, 'open',
                        DATE_ADD(NOW(), INTERVAL 30 MINUTE), NOW(), NOW())
            ");
            $stmt->execute([
                'code' => $inviteCode,
                'owner_id' => $ownerId,
                'show_id' => $showId,
                'member_limit' => $memberLimit,
                'payment_mode' => $paymentMode,
            ]);
            $groupId = (int) $this->db->lastInsertId();
            $this->db->prepare("
                INSERT INTO group_members (group_id, user_id, is_owner, status, joined_at, created_at, updated_at)
                VALUES (:group_id, :user_id, 1, 'joined', NOW(), NOW(), NOW())
            ")->execute(['group_id' => $groupId, 'user_id' => $ownerId]);
            $this->db->commit();
            return $inviteCode;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getUserGroups(int $userId): array
    {
        $this->expireUserGroups($userId);
        $stmt = $this->db->prepare("
            SELECT gb.*, m.title AS movie_title, r.name AS room_name, sh.start_time,
                   b.status AS booking_status, b.total_amount,
                   (SELECT COUNT(*) FROM group_members gm2 WHERE gm2.group_id = gb.id) AS member_count,
                   (SELECT COUNT(*) FROM group_seat_assignments ga WHERE ga.group_id = gb.id) AS assigned_seats,
                   (SELECT COUNT(*) FROM group_contributions gc WHERE gc.group_id = gb.id AND gc.status = 'paid') AS paid_members
            FROM group_bookings gb
            INNER JOIN group_members mine ON mine.group_id = gb.id AND mine.user_id = :user_id
            INNER JOIN shows sh ON sh.id = gb.show_id
            INNER JOIN movies m ON m.id = sh.movie_id
            INNER JOIN rooms r ON r.id = sh.room_id
            LEFT JOIN bookings b ON b.id = gb.booking_id
            ORDER BY gb.created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getGroupByCode(string $inviteCode): ?array
    {
        $stmtId = $this->db->prepare('SELECT id FROM group_bookings WHERE invite_code = :code LIMIT 1');
        $stmtId->execute(['code' => strtoupper(trim($inviteCode))]);
        $groupId = $stmtId->fetchColumn();
        if (!$groupId) {
            return null;
        }
        $this->expireGroupIfNeeded((int) $groupId);

        $stmt = $this->db->prepare("
            SELECT gb.*, sh.start_time, sh.status AS show_status, m.title AS movie_title,
                   r.name AS room_name, b.status AS booking_status, b.total_amount,
                   creator.name AS creator_name,
                   (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = gb.id) AS member_count,
                   (SELECT COUNT(*) FROM group_seat_assignments ga WHERE ga.group_id = gb.id) AS assigned_seats
            FROM group_bookings gb
            INNER JOIN shows sh ON sh.id = gb.show_id
            INNER JOIN movies m ON m.id = sh.movie_id
            INNER JOIN rooms r ON r.id = sh.room_id
            INNER JOIN users creator ON creator.id = gb.created_by
            LEFT JOIN bookings b ON b.id = gb.booking_id
            WHERE gb.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $groupId]);
        $group = $stmt->fetch();
        if (!$group) {
            return null;
        }

        $stmtMembers = $this->db->prepare("
            SELECT gm.id, gm.user_id, gm.is_owner, gm.status, u.name, ga.show_seat_id,
                   s.seat_code, gc.amount AS contribution_amount, gc.status AS contribution_status
            FROM group_members gm
            INNER JOIN users u ON u.id = gm.user_id
            LEFT JOIN group_seat_assignments ga ON ga.member_id = gm.id
            LEFT JOIN show_seats ss ON ss.id = ga.show_seat_id
            LEFT JOIN seats s ON s.id = ss.seat_id
            LEFT JOIN group_contributions gc ON gc.member_id = gm.id AND gc.group_id = gm.group_id
            WHERE gm.group_id = :group_id
            ORDER BY gm.is_owner DESC, gm.joined_at, gm.id
        ");
        $stmtMembers->execute(['group_id' => $groupId]);
        $group['members'] = $stmtMembers->fetchAll();

        if ($group['payment_mode'] === 'split' && $group['total_amount'] !== null) {
            $totalCents = (int) round((float) $group['total_amount'] * 100);
            $memberCount = max(1, (int) $group['member_limit']);
            $baseCents = intdiv($totalCents, $memberCount);
            foreach ($group['members'] as &$member) {
                $shareCents = (bool) $member['is_owner']
                    ? $totalCents - $baseCents * ($memberCount - 1)
                    : $baseCents;
                $member['share_amount'] = $shareCents / 100;
            }
            unset($member);
        }

        return $group;
    }

    public function getAvailableSeats(int $groupId): array
    {
        $stmtShow = $this->db->prepare('SELECT show_id FROM group_bookings WHERE id = :id LIMIT 1');
        $stmtShow->execute(['id' => $groupId]);
        $showId = $stmtShow->fetchColumn();
        if ($showId !== false) {
            (new ShowModel())->releaseExpiredHeldSeats((int) $showId);
        }

        $stmt = $this->db->prepare("
            SELECT ss.id, ss.price, s.seat_code, st.name AS seat_type_name
            FROM group_bookings gb
            INNER JOIN show_seats ss ON ss.show_id = gb.show_id AND ss.status = 'available'
            INNER JOIN seats s ON s.id = ss.seat_id
            INNER JOIN seat_types st ON st.id = s.seat_type_id
            LEFT JOIN group_seat_assignments ga ON ga.show_seat_id = ss.id
            LEFT JOIN group_bookings gb_other ON gb_other.id = ga.group_id
                AND gb_other.status IN ('open', 'pending_payment')
                AND gb_other.expires_at > NOW()
            WHERE gb.id = :group_id AND gb.status = 'open' AND gb.expires_at > NOW()
              AND gb_other.id IS NULL
            ORDER BY s.row_label, s.seat_number
        ");
        $stmt->execute(['group_id' => $groupId]);
        return $stmt->fetchAll();
    }

    public function joinGroup(string $inviteCode, int $userId): int
    {
        $stmt = $this->db->prepare('SELECT id FROM group_bookings WHERE invite_code = :code LIMIT 1');
        $stmt->execute(['code' => strtoupper(trim($inviteCode))]);
        $groupId = $stmt->fetchColumn();
        if (!$groupId) {
            throw new \InvalidArgumentException('Mã mời nhóm không tồn tại.');
        }
        $this->expireGroupIfNeeded((int) $groupId);

        $this->db->beginTransaction();
        try {
            $stmtGroup = $this->db->prepare('SELECT *, expires_at <= NOW() AS is_expired FROM group_bookings WHERE id = :id FOR UPDATE');
            $stmtGroup->execute(['id' => $groupId]);
            $group = $stmtGroup->fetch();
            if (!$group || $group['status'] !== 'open' || (bool) $group['is_expired']) {
                throw new \InvalidArgumentException('Nhóm đã đóng hoặc hết hạn tham gia.');
            }
            $stmtCount = $this->db->prepare('SELECT COUNT(*) FROM group_members WHERE group_id = :id');
            $stmtCount->execute(['id' => $groupId]);
            if ((int) $stmtCount->fetchColumn() >= (int) $group['member_limit']) {
                throw new \InvalidArgumentException('Nhóm đã đủ số thành viên.');
            }
            $stmtExisting = $this->db->prepare('SELECT id FROM group_members WHERE group_id = :group_id AND user_id = :user_id');
            $stmtExisting->execute(['group_id' => $groupId, 'user_id' => $userId]);
            if ($stmtExisting->fetchColumn()) {
                throw new \InvalidArgumentException('Bạn đã tham gia nhóm này.');
            }
            $this->db->prepare("
                INSERT INTO group_members (group_id, user_id, is_owner, status, joined_at, created_at, updated_at)
                VALUES (:group_id, :user_id, 0, 'joined', NOW(), NOW(), NOW())
            ")->execute(['group_id' => $groupId, 'user_id' => $userId]);
            $memberId = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $memberId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function assignSeat(int $groupId, int $userId, int $showSeatId): void
    {
        $this->expireGroupIfNeeded($groupId);
        $this->db->beginTransaction();
        try {
            $stmtGroup = $this->db->prepare('SELECT *, expires_at <= NOW() AS is_expired FROM group_bookings WHERE id = :id FOR UPDATE');
            $stmtGroup->execute(['id' => $groupId]);
            $group = $stmtGroup->fetch();
            if (!$group || $group['status'] !== 'open' || (bool) $group['is_expired']) {
                throw new \InvalidArgumentException('Nhóm đã đóng hoặc hết hạn chọn ghế.');
            }
            $stmtMember = $this->db->prepare('SELECT id FROM group_members WHERE group_id = :group_id AND user_id = :user_id FOR UPDATE');
            $stmtMember->execute(['group_id' => $groupId, 'user_id' => $userId]);
            $memberId = $stmtMember->fetchColumn();
            if (!$memberId) {
                throw new \InvalidArgumentException('Bạn cần tham gia nhóm trước khi chọn ghế.');
            }
            $stmtSeat = $this->db->prepare("
                SELECT id FROM show_seats
                WHERE id = :seat_id AND show_id = :show_id AND status = 'available'
                FOR UPDATE
            ");
            $stmtSeat->execute(['seat_id' => $showSeatId, 'show_id' => $group['show_id']]);
            if (!$stmtSeat->fetchColumn()) {
                throw new \InvalidArgumentException('Ghế không còn trống trong suất chiếu này.');
            }

            // Dọn các bản ghi gán ghế cũ của những nhóm đã hết hạn để tránh lỗi UNIQUE KEY uq_group_assigned_seat
            $this->db->prepare("
                DELETE ga FROM group_seat_assignments ga
                INNER JOIN group_bookings gb_old ON gb_old.id = ga.group_id
                WHERE ga.show_seat_id = :seat_id
                  AND (gb_old.status = 'expired' OR gb_old.expires_at <= NOW())
            ")->execute(['seat_id' => $showSeatId]);

            $stmtTakenByActiveGroup = $this->db->prepare("
                SELECT ga.member_id
                FROM group_seat_assignments ga
                INNER JOIN group_bookings gb_act ON gb_act.id = ga.group_id
                WHERE ga.show_seat_id = :seat_id
                  AND gb_act.status IN ('open', 'pending_payment')
                  AND gb_act.expires_at > NOW()
                LIMIT 1
            ");
            $stmtTakenByActiveGroup->execute(['seat_id' => $showSeatId]);
            $takenMemberId = $stmtTakenByActiveGroup->fetchColumn();
            if ($takenMemberId !== false && (int) $takenMemberId !== (int) $memberId) {
                throw new \InvalidArgumentException('Ghế này đã được một thành viên khác chọn.');
            }

            $this->db->prepare('DELETE FROM group_seat_assignments WHERE member_id = :member_id')
                ->execute(['member_id' => $memberId]);
            $this->db->prepare("
                INSERT INTO group_seat_assignments (group_id, member_id, show_seat_id, created_at)
                VALUES (:group_id, :member_id, :show_seat_id, NOW())
            ")->execute([
                'group_id' => $groupId,
                'member_id' => $memberId,
                'show_seat_id' => $showSeatId,
            ]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function finalizeGroup(int $groupId, int $userId): array
    {
        $this->expireGroupIfNeeded($groupId);
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT *, expires_at <= NOW() AS is_expired FROM group_bookings WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $groupId]);
            $group = $stmt->fetch();
            if (!$group || (int) $group['created_by'] !== $userId || $group['status'] !== 'open' || (bool) $group['is_expired']) {
                throw new \InvalidArgumentException('Chỉ người tạo mới có thể xác nhận nhóm đang mở.');
            }
            $stmtSeats = $this->db->prepare("
                SELECT ga.show_seat_id
                FROM group_seat_assignments ga
                INNER JOIN group_members gm ON gm.id = ga.member_id
                WHERE ga.group_id = :group_id
                ORDER BY gm.is_owner DESC, gm.id
            ");
            $stmtSeats->execute(['group_id' => $groupId]);
            $seatIds = array_map('intval', array_column($stmtSeats->fetchAll(), 'show_seat_id'));
            $stmtCount = $this->db->prepare('SELECT COUNT(*) FROM group_members WHERE group_id = :id');
            $stmtCount->execute(['id' => $groupId]);
            $memberCount = (int) $stmtCount->fetchColumn();
            if ($memberCount !== (int) $group['member_limit'] || count($seatIds) !== $memberCount) {
                throw new \InvalidArgumentException('Tất cả thành viên cần tham gia và chọn đúng một ghế trước khi xác nhận.');
            }
            $this->db->prepare("
                UPDATE group_bookings
                SET status = 'pending_payment', expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE), updated_at = NOW()
                WHERE id = :id AND status = 'open'
            ")->execute(['id' => $groupId]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        try {
            $booking = (new BookingModel())->createPendingBooking(
                $userId,
                (int) $group['show_id'],
                $seatIds,
                [],
                null,
                'Đặt vé nhóm ' . $group['invite_code']
            );
            $stmtAttach = $this->db->prepare("
                UPDATE group_bookings
                SET booking_id = :booking_id, updated_at = NOW()
                WHERE id = :group_id AND status = 'pending_payment' AND booking_id IS NULL
            ");
            $stmtAttach->execute(['booking_id' => $booking['id'], 'group_id' => $groupId]);
            if ($stmtAttach->rowCount() !== 1) {
                throw new \RuntimeException('Không thể liên kết đơn thanh toán với nhóm đặt vé.');
            }
            return $booking;
        } catch (\Throwable $e) {
            if (!empty($booking['id'])) {
                $this->db->prepare("UPDATE bookings SET status = 'expired', updated_at = NOW() WHERE id = :id AND status = 'pending'")
                    ->execute(['id' => $booking['id']]);
                $this->db->prepare("
                    UPDATE show_seats ss
                    INNER JOIN booking_details bd ON bd.show_seat_id = ss.id
                    SET ss.status = 'available', ss.held_by_user_id = NULL, ss.hold_expires_at = NULL, ss.updated_at = NOW()
                    WHERE bd.booking_id = :booking_id AND ss.status = 'held'
                ")->execute(['booking_id' => $booking['id']]);
            }
            $this->db->prepare("
                UPDATE group_bookings
                SET status = 'open', expires_at = DATE_ADD(NOW(), INTERVAL 20 MINUTE), updated_at = NOW()
                WHERE id = :id AND status = 'pending_payment' AND booking_id IS NULL
            ")->execute(['id' => $groupId]);
            throw $e;
        }
    }

    public function getGroupForBooking(int $bookingId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, invite_code, payment_mode, status
            FROM group_bookings WHERE booking_id = :booking_id LIMIT 1
        ");
        $stmt->execute(['booking_id' => $bookingId]);
        $group = $stmt->fetch();
        if ($group) {
            $this->expireGroupIfNeeded((int) $group['id']);
            $stmt->execute(['booking_id' => $bookingId]);
            $group = $stmt->fetch();
        }
        return $group ?: null;
    }

    public function isGroupMember(int $groupId, int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM group_members WHERE group_id = :group_id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['group_id' => $groupId, 'user_id' => $userId]);
        return (bool) $stmt->fetchColumn();
    }

    public function payShare(int $groupId, int $userId): void
    {
        $this->expireGroupIfNeeded($groupId);
        (new PaymentWalletModel())->getOrCreateWallet($userId);
        $this->db->beginTransaction();
        try {
            $stmtGroup = $this->db->prepare('SELECT *, expires_at <= NOW() AS is_expired FROM group_bookings WHERE id = :id FOR UPDATE');
            $stmtGroup->execute(['id' => $groupId]);
            $group = $stmtGroup->fetch();
            if (
                !$group || $group['status'] !== 'pending_payment' || $group['payment_mode'] !== 'split' ||
                empty($group['booking_id']) || (bool) $group['is_expired']
            ) {
                throw new \InvalidArgumentException('Nhóm không ở trạng thái nhận thanh toán chia phần.');
            }
            $stmtMember = $this->db->prepare("
                SELECT gm.* FROM group_members gm
                WHERE gm.group_id = :group_id AND gm.user_id = :user_id
                FOR UPDATE
            ");
            $stmtMember->execute(['group_id' => $groupId, 'user_id' => $userId]);
            $member = $stmtMember->fetch();
            if (!$member) {
                throw new \InvalidArgumentException('Bạn không phải thành viên của nhóm này.');
            }
            $stmtExisting = $this->db->prepare("
                SELECT id FROM group_contributions
                WHERE group_id = :group_id AND member_id = :member_id
                FOR UPDATE
            ");
            $stmtExisting->execute(['group_id' => $groupId, 'member_id' => $member['id']]);
            if ($stmtExisting->fetchColumn()) {
                throw new \InvalidArgumentException('Phần thanh toán của bạn đã được ghi nhận.');
            }

            $stmtBooking = $this->db->prepare("SELECT total_amount, status FROM bookings WHERE id = :id FOR UPDATE");
            $stmtBooking->execute(['id' => $group['booking_id']]);
            $booking = $stmtBooking->fetch();
            if (!$booking || $booking['status'] !== 'pending') {
                throw new \InvalidArgumentException('Đơn đặt vé nhóm không còn chờ thanh toán.');
            }
            $totalCents = (int) round((float) $booking['total_amount'] * 100);
            $memberCount = (int) $group['member_limit'];
            $baseCents = intdiv($totalCents, $memberCount);
            $shareCents = (bool) $member['is_owner'] ? $totalCents - $baseCents * ($memberCount - 1) : $baseCents;
            $amount = $shareCents / 100;

            $stmtWallet = $this->db->prepare('SELECT * FROM wallets WHERE user_id = :user_id FOR UPDATE');
            $stmtWallet->execute(['user_id' => $userId]);
            $wallet = $stmtWallet->fetch();
            if (!$wallet || (float) $wallet['balance'] < $amount) {
                throw new \InvalidArgumentException('Số dư ví không đủ. Hãy nạp tiền rồi thử lại.');
            }
            $balanceAfter = round((float) $wallet['balance'] - $amount, 2);
            $this->db->prepare('UPDATE wallets SET balance = :balance, updated_at = NOW() WHERE id = :id')
                ->execute(['balance' => $balanceAfter, 'id' => $wallet['id']]);
            $transactionCode = 'GP' . date('YmdHis') . strtoupper(bin2hex(random_bytes(6)));
            $this->db->prepare("
                INSERT INTO wallet_transactions
                    (wallet_id, booking_id, type, amount, balance_after, transaction_code, note, status, created_at, updated_at)
                VALUES (:wallet_id, :booking_id, 'payment', :amount, :balance_after, :code, :note, 'success', NOW(), NOW())
            ")->execute([
                'wallet_id' => $wallet['id'],
                'booking_id' => $group['booking_id'],
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'code' => $transactionCode,
                'note' => 'Thanh toán phần vé nhóm ' . $group['invite_code'],
            ]);
            $walletTransactionId = (int) $this->db->lastInsertId();
            $this->db->prepare("
                INSERT INTO group_contributions
                    (group_id, member_id, wallet_transaction_id, amount, status, created_at, updated_at)
                VALUES (:group_id, :member_id, :transaction_id, :amount, 'paid', NOW(), NOW())
            ")->execute([
                'group_id' => $groupId,
                'member_id' => $member['id'],
                'transaction_id' => $walletTransactionId,
                'amount' => $amount,
            ]);

            $stmtPaid = $this->db->prepare("
                SELECT COUNT(*) FROM group_contributions
                WHERE group_id = :group_id AND status = 'paid'
            ");
            $stmtPaid->execute(['group_id' => $groupId]);
            if ((int) $stmtPaid->fetchColumn() === $memberCount) {
                $stmtMethod = $this->db->query("SELECT id FROM payment_methods WHERE code = 'WALLET' AND status = 'active' LIMIT 1");
                $walletMethodId = (int) $stmtMethod->fetchColumn();
                if ($walletMethodId <= 0) {
                    throw new \RuntimeException('Phương thức ví nội bộ chưa được cấu hình.');
                }
                (new BookingModel())->confirmBookingAndIssueTickets((int) $group['booking_id'], $walletMethodId, true);
                $this->db->prepare("UPDATE group_bookings SET status = 'paid', updated_at = NOW() WHERE id = :id")
                    ->execute(['id' => $groupId]);
                $this->db->prepare("UPDATE group_members SET status = 'paid', updated_at = NOW() WHERE group_id = :id")
                    ->execute(['id' => $groupId]);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function markSharedGroupPaid(int $bookingId): void
    {
        $stmt = $this->db->prepare("
            SELECT id FROM group_bookings
            WHERE booking_id = :booking_id AND payment_mode = 'shared' AND status = 'pending_payment'
            LIMIT 1
        ");
        $stmt->execute(['booking_id' => $bookingId]);
        $groupId = $stmt->fetchColumn();
        if ($groupId) {
            $this->db->prepare("UPDATE group_bookings SET status = 'paid', updated_at = NOW() WHERE id = :id")
                ->execute(['id' => $groupId]);
            $this->db->prepare("UPDATE group_members SET status = 'paid', updated_at = NOW() WHERE group_id = :id")
                ->execute(['id' => $groupId]);
        }
    }

    public function getAllGroupsForAdmin(): array
    {
        $this->expireAllGroups();
        return $this->db->query("
            SELECT gb.*, creator.name AS creator_name, m.title AS movie_title, r.name AS room_name,
                   sh.start_time, b.status AS booking_status, b.total_amount,
                   COALESCE(ms.member_count, 0) AS member_count,
                   COALESCE(ms.member_names, '') AS member_names,
                   COALESCE(cs.paid_count, 0) AS paid_count
            FROM group_bookings gb
            INNER JOIN users creator ON creator.id = gb.created_by
            INNER JOIN shows sh ON sh.id = gb.show_id
            INNER JOIN movies m ON m.id = sh.movie_id
            INNER JOIN rooms r ON r.id = sh.room_id
            LEFT JOIN bookings b ON b.id = gb.booking_id
            LEFT JOIN (
                SELECT gm.group_id, COUNT(*) AS member_count,
                       GROUP_CONCAT(u.name ORDER BY gm.is_owner DESC, u.name SEPARATOR ', ') AS member_names
                FROM group_members gm
                INNER JOIN users u ON u.id = gm.user_id
                GROUP BY gm.group_id
            ) ms ON ms.group_id = gb.id
            LEFT JOIN (
                SELECT group_id, COUNT(*) AS paid_count
                FROM group_contributions WHERE status = 'paid' GROUP BY group_id
            ) cs ON cs.group_id = gb.id
            ORDER BY gb.created_at DESC
        ")->fetchAll();
    }

    private function expireUserGroups(int $userId): void
    {
        $stmt = $this->db->prepare("
            SELECT gb.id FROM group_bookings gb
            INNER JOIN group_members gm ON gm.group_id = gb.id
            WHERE gm.user_id = :user_id AND gb.status IN ('open','pending_payment') AND gb.expires_at <= NOW()
        ");
        $stmt->execute(['user_id' => $userId]);
        foreach ($stmt->fetchAll(\PDO::FETCH_COLUMN) as $groupId) {
            $this->expireGroupIfNeeded((int) $groupId);
        }
    }

    private function expireAllGroups(): void
    {
        $ids = $this->db->query("
            SELECT id FROM group_bookings
            WHERE status IN ('open','pending_payment') AND expires_at <= NOW()
        ")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($ids as $id) {
            $this->expireGroupIfNeeded((int) $id);
        }
    }

    private function expireGroupIfNeeded(int $groupId): void
    {
        $ownsTransaction = !$this->db->inTransaction();
        if ($ownsTransaction) {
            $this->db->beginTransaction();
        }
        try {
            $stmt = $this->db->prepare('SELECT *, expires_at <= NOW() AS is_expired FROM group_bookings WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $groupId]);
            $group = $stmt->fetch();
            if (!$group || !in_array($group['status'], ['open', 'pending_payment'], true) || !(bool) $group['is_expired']) {
                if ($ownsTransaction) {
                    $this->db->commit();
                }
                return;
            }

            if (!empty($group['booking_id'])) {
                $stmtBookingStatus = $this->db->prepare('SELECT status FROM bookings WHERE id = :id FOR UPDATE');
                $stmtBookingStatus->execute(['id' => $group['booking_id']]);
                if ($stmtBookingStatus->fetchColumn() === 'confirmed') {
                    $this->db->prepare("UPDATE group_bookings SET status = 'paid', updated_at = NOW() WHERE id = :id")
                        ->execute(['id' => $groupId]);
                    $this->db->prepare("UPDATE group_members SET status = 'paid', updated_at = NOW() WHERE group_id = :id")
                        ->execute(['id' => $groupId]);
                    if ($ownsTransaction) {
                        $this->db->commit();
                    }
                    return;
                }
                if ($group['payment_mode'] === 'split') {
                    $stmtContributions = $this->db->prepare("
                        SELECT gc.id, gc.amount, gc.member_id, gm.user_id
                        FROM group_contributions gc
                        INNER JOIN group_members gm ON gm.id = gc.member_id
                        WHERE gc.group_id = :group_id AND gc.status = 'paid'
                        ORDER BY gm.user_id
                        FOR UPDATE
                    ");
                    $stmtContributions->execute(['group_id' => $groupId]);
                    foreach ($stmtContributions->fetchAll() as $contribution) {
                        $stmtWallet = $this->db->prepare('SELECT * FROM wallets WHERE user_id = :user_id FOR UPDATE');
                        $stmtWallet->execute(['user_id' => $contribution['user_id']]);
                        $wallet = $stmtWallet->fetch();
                        if (!$wallet) {
                            $this->db->prepare("INSERT INTO wallets (user_id, balance, currency, created_at, updated_at) VALUES (:uid, 0, 'VND', NOW(), NOW())")
                                ->execute(['uid' => $contribution['user_id']]);
                            $stmtWallet->execute(['user_id' => $contribution['user_id']]);
                            $wallet = $stmtWallet->fetch();
                        }
                        $balanceAfter = round((float) $wallet['balance'] + (float) $contribution['amount'], 2);
                        $this->db->prepare('UPDATE wallets SET balance = :balance, updated_at = NOW() WHERE id = :id')
                            ->execute(['balance' => $balanceAfter, 'id' => $wallet['id']]);
                        $this->db->prepare("
                            INSERT INTO wallet_transactions
                                (wallet_id, booking_id, type, amount, balance_after, transaction_code, note, status, created_at, updated_at)
                            VALUES (:wallet_id, :booking_id, 'refund', :amount, :balance_after, :code, :note, 'success', NOW(), NOW())
                        ")->execute([
                            'wallet_id' => $wallet['id'],
                            'booking_id' => $group['booking_id'],
                            'amount' => $contribution['amount'],
                            'balance_after' => $balanceAfter,
                            'code' => 'GR' . date('YmdHis') . strtoupper(bin2hex(random_bytes(6))),
                            'note' => 'Hoàn tiền do nhóm ' . $group['invite_code'] . ' hết hạn.',
                        ]);
                        $this->db->prepare("UPDATE group_contributions SET status = 'refunded', updated_at = NOW() WHERE id = :id")
                            ->execute(['id' => $contribution['id']]);
                    }
                }

                $this->db->prepare("
                    UPDATE bookings SET status = 'expired', updated_at = NOW()
                    WHERE id = :id AND status = 'pending'
                ")->execute(['id' => $group['booking_id']]);
                $this->db->prepare("
                    UPDATE show_seats ss
                    INNER JOIN booking_details bd ON bd.show_seat_id = ss.id
                    SET ss.status = 'available', ss.held_by_user_id = NULL, ss.hold_expires_at = NULL, ss.updated_at = NOW()
                    WHERE bd.booking_id = :booking_id AND ss.status = 'held'
                ")->execute(['booking_id' => $group['booking_id']]);
            }
            // Giải phóng các bản ghi giữ ghế tạm của nhóm đã hết hạn để các nhóm khác có thể chọn lại ghế đó
            $this->db->prepare("DELETE FROM group_seat_assignments WHERE group_id = :id")
                ->execute(['id' => $groupId]);
            $this->db->prepare("UPDATE group_bookings SET status = 'expired', updated_at = NOW() WHERE id = :id")
                ->execute(['id' => $groupId]);
            if ($ownsTransaction) {
                $this->db->commit();
            }
        } catch (\Throwable $e) {
            if ($ownsTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}

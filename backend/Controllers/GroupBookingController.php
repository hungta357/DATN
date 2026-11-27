<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\GroupBookingModel;
use App\Models\PaymentWalletModel;

class GroupBookingController extends BaseController
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $model = new GroupBookingModel();
        $codeInput = $_GET['code'] ?? '';
        $code = is_string($codeInput) ? strtoupper(trim($codeInput)) : '';
        $group = $code !== '' ? $model->getGroupByCode($code) : null;
        $isMember = false;
        $member = null;
        $availableSeats = [];
        $wallet = null;

        if ($group) {
            foreach ($group['members'] as $groupMember) {
                if ((int) $groupMember['user_id'] === (int) $user['id']) {
                    $isMember = true;
                    $member = $groupMember;
                    break;
                }
            }
            if ($isMember && $group['status'] === 'open') {
                $availableSeats = $model->getAvailableSeats((int) $group['id']);
            }
            if ($isMember && $group['status'] === 'pending_payment' && $group['payment_mode'] === 'split') {
                $wallet = (new PaymentWalletModel())->getOrCreateWallet((int) $user['id']);
            }
        }

        $this->view('booking/group', [
            'pageTitle' => 'Đặt vé nhóm',
            'groups' => $model->getUserGroups((int) $user['id']),
            'shows' => $model->getUpcomingShows(),
            'group' => $group,
            'inviteCode' => $code,
            'isMember' => $isMember,
            'currentMember' => $member,
            'availableSeats' => $availableSeats,
            'wallet' => $wallet,
        ]);
    }

    public function create(): void
    {
        $user = $this->requireAuth();
        try {
            $showId = $this->positiveInt($_POST['show_id'] ?? null, 'Suất chiếu không hợp lệ.');
            $memberLimit = $this->positiveInt($_POST['member_limit'] ?? null, 'Số thành viên không hợp lệ.');
            $mode = $_POST['payment_mode'] ?? '';
            if (!is_string($mode)) {
                throw new \InvalidArgumentException('Hình thức thanh toán nhóm không hợp lệ.');
            }
            $code = (new GroupBookingModel())->createGroup((int) $user['id'], $showId, $memberLimit, $mode);
            $this->setFlash('success', 'Đã tạo nhóm. Chia sẻ mã mời để các thành viên tham gia.');
            $this->redirect('?route=booking/group&code=' . urlencode($code));
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể tạo nhóm: ' . $e->getMessage());
            $this->redirect('?route=booking/group');
        }
    }

    public function join(): void
    {
        $user = $this->requireAuth();
        $codeInput = $_POST['invite_code'] ?? '';
        $code = is_string($codeInput) ? strtoupper(trim($codeInput)) : '';
        try {
            if ($code === '') {
                throw new \InvalidArgumentException('Mã mời nhóm không hợp lệ.');
            }
            (new GroupBookingModel())->joinGroup($code, (int) $user['id']);
            $this->setFlash('success', 'Bạn đã tham gia nhóm đặt vé.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể tham gia nhóm: ' . $e->getMessage());
        }
        $this->redirect('?route=booking/group&code=' . urlencode($code));
    }

    public function assignSeat(): void
    {
        $user = $this->requireAuth();
        $groupId = 0;
        try {
            $groupId = $this->positiveInt($_POST['group_id'] ?? null, 'Nhóm không hợp lệ.');
            $showSeatId = $this->positiveInt($_POST['show_seat_id'] ?? null, 'Ghế không hợp lệ.');
            (new GroupBookingModel())->assignSeat($groupId, (int) $user['id'], $showSeatId);
            $this->setFlash('success', 'Đã lưu ghế của bạn trong nhóm.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể chọn ghế: ' . $e->getMessage());
        }
        $this->redirect($this->groupUrl($groupId, (int) $user['id']));
    }

    public function finalize(): void
    {
        $user = $this->requireAuth();
        $groupId = 0;
        try {
            $groupId = $this->positiveInt($_POST['group_id'] ?? null, 'Nhóm không hợp lệ.');
            $model = new GroupBookingModel();
            $booking = $model->finalizeGroup($groupId, (int) $user['id']);
            $group = $model->getGroupForBooking((int) $booking['id']);
            if ($group && $group['payment_mode'] === 'shared') {
                $this->setFlash('success', 'Đã giữ ghế nhóm trong 10 phút. Người tạo hãy hoàn tất thanh toán.');
                $this->redirect('?route=booking/checkout&id=' . (int) $booking['id']);
            }
            $this->setFlash('success', 'Đã giữ ghế nhóm trong 10 phút. Các thành viên có thể thanh toán phần của mình.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể xác nhận đặt vé nhóm: ' . $e->getMessage());
        }
        $this->redirect($this->groupUrl($groupId, (int) $user['id']));
    }

    public function payShare(): void
    {
        $user = $this->requireAuth();
        $groupId = 0;
        try {
            $groupId = $this->positiveInt($_POST['group_id'] ?? null, 'Nhóm không hợp lệ.');
            (new GroupBookingModel())->payShare($groupId, (int) $user['id']);
            $this->setFlash('success', 'Đã thanh toán phần của bạn bằng ví nội bộ.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể thanh toán: ' . $e->getMessage());
        }
        $this->redirect($this->groupUrl($groupId, (int) $user['id']));
    }

    private function positiveInt($value, string $message): int
    {
        if (!is_string($value) && !is_int($value)) {
            throw new \InvalidArgumentException($message);
        }
        $validated = filter_var($value, FILTER_VALIDATE_INT);
        if ($validated === false || $validated <= 0) {
            throw new \InvalidArgumentException($message);
        }
        return (int) $validated;
    }

    private function groupUrl(int $groupId, int $userId): string
    {
        if ($groupId <= 0) {
            return '?route=booking/group';
        }
        $stmt = (new GroupBookingModel())->getDb()->prepare("
            SELECT gb.invite_code
            FROM group_bookings gb
            INNER JOIN group_members gm ON gm.group_id = gb.id
            WHERE gb.id = :id AND gm.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute(['id' => $groupId, 'user_id' => $userId]);
        $code = $stmt->fetchColumn();
        return $code ? '?route=booking/group&code=' . urlencode($code) : '?route=booking/group';
    }
}

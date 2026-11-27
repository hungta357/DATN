<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\TicketSecurityModel;

class TicketSecurityController extends BaseController
{
    /**
     * Nhận mã quét từ quầy, gắn sự kiện với nhân viên đang đăng nhập và hiển thị
     * kết quả cùng cảnh báo nếu hệ thống phát hiện lần quét bất thường.
     */
    public function checkIn(): void
    {
        $staff = $this->requireRole(['admin', 'staff']);
        $codeInput = $_POST['ticket_code'] ?? '';
        $ticketCode = is_string($codeInput) ? trim($codeInput) : '';
        try {
            if ($ticketCode === '') {
                throw new \InvalidArgumentException('Vui lòng nhập mã vé cần soát.');
            }

            $ticket = (new TicketSecurityModel())->checkInTicketByCode($ticketCode, (int) $staff['id']);
            $message = "Soát vé thành công! Mã vé: {$ticket['ticket_code']} | Phim: {$ticket['movie_title']} | Phòng: {$ticket['room_name']} | Ghế: {$ticket['seat_code']}";
            if (!empty($ticket['scan_anomaly'])) {
                $message .= ' Cảnh báo: ' . $ticket['scan_anomaly'] . '. Vui lòng kiểm tra thông tin suất chiếu.';
            }
            $this->setFlash('success', $message);
        } catch (\Throwable $e) {
            $this->setFlash('danger', $e->getMessage());
        }
        $this->redirect('?route=admin&tab=bookings');
    }
}

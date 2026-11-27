<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\IncidentModel;

class IncidentController extends BaseController
{
    /**
     * Hiển thị vé hợp lệ của khách và trạng thái các yêu cầu họ đã gửi; bộ lọc
     * booking_id cho phép mở biểu mẫu khiếu nại ngay từ một đơn đặt vé cụ thể.
     */
    public function index(): void
    {
        $user = $this->requireAuth();
        $bookingIdInput = $_GET['booking_id'] ?? null;
        $bookingId = is_string($bookingIdInput) || is_int($bookingIdInput)
            ? filter_var($bookingIdInput, FILTER_VALIDATE_INT)
            : false;
        $bookingId = $bookingId === false || $bookingId === null ? null : (int) $bookingId;
        if ($bookingId !== null && $bookingId <= 0) {
            $bookingId = null;
        }

        $model = new IncidentModel();
        $this->view('incident/index', [
            'pageTitle' => 'Khiếu nại vé & hỗ trợ',
            'tickets' => $model->getCustomerTickets((int) $user['id'], $bookingId),
            'reports' => $model->getReports((int) $user['id'], false),
            'selectedBookingId' => $bookingId,
        ]);
    }

    /**
     * Kiểm tra dữ liệu biểu mẫu, buộc yêu cầu gắn với vé của tài khoản hiện tại
     * rồi chuyển về danh sách để khách nhìn thấy kết quả vừa gửi.
     */
    public function create(): void
    {
        $user = $this->requireAuth();
        try {
            $ticketCode = $this->requiredText($_POST['ticket_code'] ?? '', 'Vui lòng chọn vé cần hỗ trợ.');
            $categoryInput = $_POST['category'] ?? 'ticket';
            $category = is_string($categoryInput) ? $categoryInput : '';
            $title = $this->requiredText($_POST['title'] ?? '', 'Vui lòng nhập tiêu đề khiếu nại.');
            $description = $this->requiredText($_POST['description'] ?? '', 'Vui lòng mô tả nội dung cần hỗ trợ.');
            if (mb_strlen($title) > 180 || mb_strlen($description) > 5000) {
                throw new \InvalidArgumentException('Tiêu đề tối đa 180 ký tự, mô tả tối đa 5.000 ký tự.');
            }

            (new IncidentModel())->createReport(
                (int) $user['id'],
                $ticketCode,
                $category,
                $title,
                $description
            );
            $this->setFlash('success', 'Đã gửi yêu cầu hỗ trợ. Rạp sẽ phản hồi trong thời gian sớm nhất.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể gửi yêu cầu: ' . $e->getMessage());
        }
        $this->redirect('?route=incidents');
    }

    /**
     * Chỉ Admin được phân công yêu cầu khiếu nại cho nhân viên đang hoạt động.
     */
    public function assign(): void
    {
        $this->requireRole(['admin']);
        try {
            $reportId = $this->positiveId($_POST['report_id'] ?? null, 'Khiếu nại không hợp lệ.');
            $staffId = $this->positiveId($_POST['staff_id'] ?? null, 'Nhân viên không hợp lệ.');
            (new IncidentModel())->assignReport($reportId, $staffId);
            $this->setFlash('success', 'Đã phân công xử lý khiếu nại.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể phân công khiếu nại: ' . $e->getMessage());
        }
        $this->redirect('?route=admin&tab=incidents');
    }

    /**
     * Cho phép Admin cập nhật mọi hồ sơ và chỉ cho nhân viên xử lý yêu cầu đã
     * được giao cho họ; quyền sở hữu được kiểm tra thêm trong model.
     */
    public function update(): void
    {
        $user = $this->requireRole(['admin', 'staff']);
        $isAdmin = in_array('admin', $user['roles'] ?? [], true);
        try {
            $reportId = $this->positiveId($_POST['report_id'] ?? null, 'Khiếu nại không hợp lệ.');
            $statusInput = $_POST['status'] ?? '';
            $resolutionInput = $_POST['resolution'] ?? '';
            if (!is_string($statusInput) || !is_string($resolutionInput) || mb_strlen($resolutionInput) > 5000) {
                throw new \InvalidArgumentException('Thông tin phản hồi không hợp lệ.');
            }
            (new IncidentModel())->updateReport(
                $reportId,
                (int) $user['id'],
                $isAdmin,
                $statusInput,
                trim($resolutionInput)
            );
            $this->setFlash('success', 'Đã cập nhật trạng thái và phản hồi khiếu nại.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể cập nhật khiếu nại: ' . $e->getMessage());
        }
        $this->redirect('?route=admin&tab=incidents');
    }

    /**
     * Đọc trường bắt buộc dạng chuỗi và chặn payload mảng trước khi xử lý nghiệp vụ.
     */
    private function requiredText($value, string $message): string
    {
        $text = is_string($value) ? trim($value) : '';
        if ($text === '') {
            throw new \InvalidArgumentException($message);
        }
        return $text;
    }

    /**
     * Chuẩn hóa ID dương từ form; không để giá trị rỗng hay mảng lọt xuống model.
     */
    private function positiveId($value, string $message): int
    {
        $id = is_string($value) || is_int($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
        if ($id === false || $id <= 0) {
            throw new \InvalidArgumentException($message);
        }
        return (int) $id;
    }
}

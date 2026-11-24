<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\StaffShiftModel;

class StaffShiftController extends BaseController
{
    /**
     * Chuẩn hóa lối vào trang ca trực rồi chuyển sang dashboard để dữ liệu lịch
     * được tải theo đúng quyền Admin/Staff trong cùng một luồng quản trị.
     */
    public function index(): void
    {
        $this->requireRole(['admin', 'staff']);
        $this->redirect('?route=admin&tab=shifts');
    }

    /**
     * Kiểm tra dữ liệu ngày giờ và phân công nhân viên; model sẽ xác minh vai trò,
     * địa điểm và chặn ca bị trùng trước khi lưu.
     */
    public function create(): void
    {
        $admin = $this->requireRole(['admin']);
        try {
            $staffId = $this->positiveId($_POST['staff_id'] ?? null, 'Vui lòng chọn nhân viên.');
            $roomInput = $_POST['room_id'] ?? '';
            $roomId = $roomInput === '' ? null : $this->positiveId($roomInput, 'Phòng chiếu không hợp lệ.');
            $startsAt = $this->parseDateTime($_POST['starts_at'] ?? null);
            $endsAt = $this->parseDateTime($_POST['ends_at'] ?? null);
            if (strtotime($startsAt) < time()) {
                throw new \InvalidArgumentException('Không thể phân công ca bắt đầu trong quá khứ.');
            }
            $duty = $this->requiredText($_POST['duty'] ?? '', 'Vui lòng nhập nhiệm vụ trong ca.');
            $notesInput = $_POST['notes'] ?? '';
            $notes = is_string($notesInput) ? trim($notesInput) : '';
            if (mb_strlen($duty) > 80 || mb_strlen($notes) > 1000) {
                throw new \InvalidArgumentException('Nhiệm vụ tối đa 80 ký tự và ghi chú tối đa 1.000 ký tự.');
            }

            (new StaffShiftModel())->createShift(
                $staffId,
                (int) $admin['id'],
                $roomId,
                $startsAt,
                $endsAt,
                $duty,
                $notes
            );
            $this->setFlash('success', 'Đã tạo ca trực và phân công nhân viên.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể tạo ca trực: ' . $e->getMessage());
        }
        $this->redirect('?route=admin&tab=shifts');
    }

    /**
     * Cho phép nhân viên hoàn thành ca của mình; Admin có thể hoàn thành hoặc hủy
     * bất kỳ ca nào còn ở trạng thái được xếp lịch.
     */
    public function updateStatus(): void
    {
        $user = $this->requireRole(['admin', 'staff']);
        $isAdmin = in_array('admin', $user['roles'] ?? [], true);
        try {
            $shiftId = $this->positiveId($_POST['shift_id'] ?? null, 'Ca trực không hợp lệ.');
            $statusInput = $_POST['status'] ?? '';
            if (!is_string($statusInput)) {
                throw new \InvalidArgumentException('Trạng thái ca trực không hợp lệ.');
            }
            (new StaffShiftModel())->updateStatus(
                $shiftId,
                (int) $user['id'],
                $isAdmin,
                $statusInput
            );
            $this->setFlash('success', 'Đã cập nhật trạng thái ca trực.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể cập nhật ca trực: ' . $e->getMessage());
        }
        $this->redirect('?route=admin&tab=shifts');
    }

    /**
     * Kiểm tra định dạng datetime-local chính xác trước khi chuyển thành DATETIME.
     */
    private function parseDateTime($value): string
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Thời gian ca trực không hợp lệ.');
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);
        if (!$date || $date->format('Y-m-d\TH:i') !== $value) {
            throw new \InvalidArgumentException('Vui lòng nhập thời gian bắt đầu và kết thúc hợp lệ.');
        }
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Chuyển ID biểu mẫu thành số nguyên dương hoặc trả về thông báo lỗi phù hợp.
     */
    private function positiveId($value, string $message): int
    {
        $id = is_string($value) || is_int($value) ? filter_var($value, FILTER_VALIDATE_INT) : false;
        if ($id === false || $id <= 0) {
            throw new \InvalidArgumentException($message);
        }
        return (int) $id;
    }

    /**
     * Xác nhận các trường văn bản bắt buộc là chuỗi không rỗng.
     */
    private function requiredText($value, string $message): string
    {
        $text = is_string($value) ? trim($value) : '';
        if ($text === '') {
            throw new \InvalidArgumentException($message);
        }
        return $text;
    }
}

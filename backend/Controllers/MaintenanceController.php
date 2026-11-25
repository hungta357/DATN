<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\MaintenanceModel;

class MaintenanceController extends BaseController
{
    public function create(): void
    {
        $user = $this->requireRole(['admin', 'staff']);
        $attachmentPath = null;
        try {
            $attachment = $this->storeEvidence($_FILES['evidence'] ?? null);
            $attachmentPath = $attachment['file_path'] ?? null;
            (new MaintenanceModel())->createReport([
                'category_id' => filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT),
                'room_id' => filter_var($_POST['room_id'] ?? null, FILTER_VALIDATE_INT),
                'seat_id' => empty($_POST['seat_id']) ? null : filter_var($_POST['seat_id'], FILTER_VALIDATE_INT),
                'severity' => 'medium',
                'description' => $this->requiredText($_POST['description'] ?? '', 'Vui lòng mô tả sự cố.'),
            ], (int) $user['id'], $attachment);
            $this->setFlash('success', 'Đã ghi nhận báo cáo sự cố.');
        } catch (\Throwable $e) {
            $this->removeEvidence($attachmentPath);
            $this->setFlash('danger', 'Không thể tạo báo cáo: ' . $e->getMessage());
        }
        $this->redirect('?route=admin&tab=maintenance');
    }

    public function assign(): void
    {
        $admin = $this->requireRole(['admin']);
        try {
            $staffId = filter_var($_POST['staff_id'] ?? null, FILTER_VALIDATE_INT);
            $reportId = filter_var($_POST['report_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$staffId || !$reportId) {
                throw new \InvalidArgumentException('Báo cáo hoặc nhân viên không hợp lệ.');
            }
            $dueAtInput = $_POST['due_at'] ?? '';
            $dueAt = is_string($dueAtInput) ? trim($dueAtInput) : '';
            if (!is_string($dueAtInput)) {
                throw new \InvalidArgumentException('Thời hạn xử lý không hợp lệ.');
            }
            $parsedDueAt = $dueAt === '' ? null : \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $dueAt);
            if ($dueAt !== '' && (!$parsedDueAt || $parsedDueAt->format('Y-m-d\TH:i') !== $dueAt)) {
                throw new \InvalidArgumentException('Thời hạn xử lý không hợp lệ.');
            }
            (new MaintenanceModel())->assignReport(
                (int) $reportId,
                (int) $staffId,
                $this->severity($_POST['severity'] ?? 'medium'),
                $parsedDueAt ? $parsedDueAt->format('Y-m-d H:i:s') : null,
                (int) $admin['id'],
                is_string($_POST['note'] ?? null) ? trim($_POST['note']) : ''
            );
            $this->setFlash('success', 'Đã phân công nhân viên xử lý sự cố.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể phân công: ' . $e->getMessage());
        }
        $this->redirect('?route=admin&tab=maintenance');
    }

    public function updateProgress(): void
    {
        $user = $this->requireRole(['admin', 'staff']);
        $isAdmin = in_array('admin', $user['roles'] ?? [], true);
        $attachmentPath = null;
        try {
            $reportId = filter_var($_POST['report_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$reportId) {
                throw new \InvalidArgumentException('Báo cáo không hợp lệ.');
            }
            $attachment = $this->storeEvidence($_FILES['evidence'] ?? null);
            $attachmentPath = $attachment['file_path'] ?? null;
            $submit = ($_POST['submit_for_confirmation'] ?? '') === '1';
            (new MaintenanceModel())->updateProgress(
                (int) $reportId,
                (int) $user['id'],
                $submit,
                $this->requiredText($_POST['note'] ?? '', 'Vui lòng nhập nội dung cập nhật.'),
                $attachment,
                $isAdmin
            );
            $this->setFlash('success', $submit ? 'Đã gửi kết quả để Admin xác nhận.' : 'Đã cập nhật tiến độ xử lý.');
        } catch (\Throwable $e) {
            $this->removeEvidence($attachmentPath);
            $this->setFlash('danger', 'Không thể cập nhật tiến độ: ' . $e->getMessage());
        }
        $this->redirect('?route=admin&tab=maintenance');
    }

    public function confirm(): void
    {
        $admin = $this->requireRole(['admin']);
        try {
            $reportId = filter_var($_POST['report_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$reportId) {
                throw new \InvalidArgumentException('Báo cáo không hợp lệ.');
            }
            (new MaintenanceModel())->confirmReport(
                (int) $reportId,
                (int) $admin['id'],
                is_string($_POST['note'] ?? null) ? trim($_POST['note']) : ''
            );
            $this->setFlash('success', 'Đã xác nhận hoàn thành sự cố.');
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Không thể xác nhận: ' . $e->getMessage());
        }
        $this->redirect('?route=admin&tab=maintenance');
    }

    private function severity($value): string
    {
        if (!is_string($value) || !in_array($value, ['low', 'medium', 'high', 'critical'], true)) {
            throw new \InvalidArgumentException('Mức độ sự cố không hợp lệ.');
        }
        return $value;
    }

    private function requiredText($value, string $message): string
    {
        $text = is_string($value) ? trim($value) : '';
        if ($text === '') {
            throw new \InvalidArgumentException($message);
        }
        return $text;
    }

    private function storeEvidence(?array $file): ?array
    {
        if ($file === null || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (
            $file['error'] !== UPLOAD_ERR_OK ||
            !isset($file['tmp_name'], $file['name']) ||
            !is_string($file['tmp_name']) ||
            !is_string($file['name']) ||
            !is_uploaded_file($file['tmp_name'])
        ) {
            throw new \InvalidArgumentException('Tệp minh chứng tải lên không hợp lệ.');
        }
        if ((int) $file['size'] < 1 || (int) $file['size'] > 5 * 1024 * 1024) {
            throw new \InvalidArgumentException('Ảnh minh chứng phải nhỏ hơn hoặc bằng 5 MB.');
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensions[$mimeType])) {
            throw new \InvalidArgumentException('Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
        }

        $directory = dirname(__DIR__, 2) . '/public/uploads/maintenance';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Không thể tạo thư mục lưu ảnh minh chứng.');
        }
        $fileName = bin2hex(random_bytes(16)) . '.' . $extensions[$mimeType];
        if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $fileName)) {
            throw new \RuntimeException('Không thể lưu ảnh minh chứng.');
        }

        return [
            'file_path' => 'public/uploads/maintenance/' . $fileName,
            'original_name' => basename((string) $file['name']),
            'mime_type' => $mimeType,
            'size_bytes' => (int) $file['size'],
        ];
    }

    private function removeEvidence(?string $relativePath): void
    {
        if ($relativePath === null || strpos($relativePath, 'public/uploads/maintenance/') !== 0) {
            return;
        }
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (is_file($path) && !unlink($path)) {
            error_log('Unable to remove unreferenced maintenance evidence: ' . $path);
        }
    }
}

<?php

namespace App\Core;

abstract class BaseController
{
    protected function view(string $viewPath, array $data = [], ?string $layout = 'layouts/main'): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../Views/' . $viewPath . '.php';

        if (!file_exists($viewFile)) {
            die("View không tồn tại: {$viewPath}");
        }

        if ($layout === null) {
            require $viewFile;
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require __DIR__ . '/../Views/' . $layout . '.php';
    }

    protected function json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function requireAuth(): array
    {
        if (empty($_SESSION['user'])) {
            $this->setFlash('warning', 'Vui lòng đăng nhập để tiếp tục.');
            $this->redirect('?route=auth/login');
        }
        return $_SESSION['user'];
    }

    protected function requireRole(array $allowedRoles = ['admin', 'staff']): array
    {
        $user = $this->requireAuth();
        $userRoles = $user['roles'] ?? [];
        if (empty(array_intersect($allowedRoles, $userRoles))) {
            $this->setFlash('danger', 'Bạn không có quyền truy cập khu vực này.');
            $this->redirect('?route=home');
        }
        return $user;
    }
}


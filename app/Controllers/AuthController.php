<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $userModel = new UserModel();
            $user = $userModel->findByEmail($email);

            if (!$user || !password_verify($password, $user['password'])) {
                $this->setFlash('danger', 'Email hoặc mật khẩu không chính xác.');
                $this->redirect('?route=auth/login');
            }

            if ($user['status'] !== 'active') {
                $this->setFlash('warning', 'Tài khoản của bạn đang bị khóa hoặc chờ kích hoạt.');
                $this->redirect('?route=auth/login');
            }

            unset($user['password']);
            $_SESSION['user'] = $user;

            $this->setFlash('success', 'Xin chào, ' . htmlspecialchars($user['name']) . '!');
            $this->redirect('?route=home');
        }

        $this->view('auth/login', ['pageTitle' => 'Đăng nhập']);
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($name === '' || $email === '' || strlen($password) < 6) {
                $this->setFlash('danger', 'Vui lòng nhập đầy đủ thông tin và mật khẩu tối thiểu 6 ký tự.');
                $this->redirect('?route=auth/register');
            }

            $userModel = new UserModel();
            if ($userModel->findByEmail($email)) {
                $this->setFlash('danger', 'Email này đã được đăng ký.');
                $this->redirect('?route=auth/register');
            }

            try {
                $userModel->registerCustomer([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone ?: null,
                    'password' => $password,
                ]);
                $this->setFlash('success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập.');
                $this->redirect('?route=auth/login');
            } catch (\Throwable $e) {
                $this->setFlash('danger', 'Lỗi đăng ký: ' . $e->getMessage());
                $this->redirect('?route=auth/register');
            }
        }

        $this->view('auth/register', ['pageTitle' => 'Đăng ký tài khoản']);
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        $this->setFlash('info', 'Bạn đã đăng xuất.');
        $this->redirect('?route=home');
    }
}

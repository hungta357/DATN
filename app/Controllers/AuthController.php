<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\AuthSecurityModel;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $emailInput = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (!is_string($emailInput) || !is_string($password) || trim($emailInput) === '' || $password === '') {
                $this->rememberAuthInput(['email' => is_string($emailInput) ? trim($emailInput) : '']);
                $this->setFlash('danger', 'Vui lòng nhập email và mật khẩu.');
                $this->redirect('?route=auth/login');
            }

            $email = strtolower(trim($emailInput));
            if ($email === 'admin') {
                $email = 'admin@cinema.vn';
            }

            $securityModel = new AuthSecurityModel();
            $attemptKey = hash('sha256', $email . "\0" . (string) ($_SERVER['REMOTE_ADDR'] ?? ''));
            $lockRemaining = $securityModel->getLoginLockRemaining($attemptKey);
            if ($lockRemaining > 0) {
                $this->rememberAuthInput(['email' => $emailInput]);
                $this->setFlash('warning', 'Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau ' . (int) ceil($lockRemaining / 60) . ' phút.');
                $this->redirect('?route=auth/login');
            }

            $userModel = new UserModel();
            $user = $userModel->findByEmail($email);

            if (!$user || empty($user['password']) || !password_verify($password, (string) $user['password'])) {
                $lockRemaining = $securityModel->recordFailedLogin($attemptKey);
                $this->rememberAuthInput(['email' => $emailInput]);
                $message = $lockRemaining > 0
                    ? 'Bạn đã đăng nhập sai 5 lần. Tài khoản tạm khóa trong 15 phút.'
                    : 'Tài khoản hoặc mật khẩu không chính xác.';
                $this->setFlash($lockRemaining > 0 ? 'warning' : 'danger', $message);
                $this->redirect('?route=auth/login');
            }

            if ($user['status'] !== 'active') {
                $this->rememberAuthInput(['email' => $emailInput]);
                $this->setFlash('warning', 'Tài khoản của bạn đang bị khóa hoặc chờ kích hoạt.');
                $this->redirect('?route=auth/login');
            }

            $securityModel->clearLoginFailures($attemptKey);
            session_regenerate_id(true);
            unset($_SESSION['auth_old_input']);
            unset($user['password']);
            $_SESSION['user'] = $user;

            if (!empty($user['roles']) && array_intersect(['admin', 'staff'], $user['roles'])) {
                unset($_SESSION['redirect_after_login']);
                $this->setFlash('success', 'Chào mừng Quản trị viên ' . $user['name'] . ' quay trở lại hệ thống!');
                $this->redirect('?route=admin');
            }

            $redirectUrl = $_SESSION['redirect_after_login'] ?? '?route=home';
            unset($_SESSION['redirect_after_login']);

            $this->setFlash('success', 'Xin chào, ' . $user['name'] . '!');
            $this->redirect($redirectUrl);
        }

        $this->view('auth/login', [
            'pageTitle' => 'Đăng nhập',
            'oldInput' => $this->consumeAuthInput(),
        ]);
    }

    public function adminLogin(): void
    {
        $this->redirect('?route=auth/login');
    }

    public function adminLogout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
        $this->setFlash('info', 'Bạn đã đăng xuất.');
        $this->redirect('?route=auth/login');
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nameInput = $_POST['name'] ?? '';
            $emailInput = $_POST['email'] ?? '';
            $phoneInput = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';
            $passwordConfirmation = $_POST['password_confirmation'] ?? '';

            if (
                !is_string($nameInput) ||
                !is_string($emailInput) ||
                !is_string($phoneInput) ||
                !is_string($password) ||
                !is_string($passwordConfirmation)
            ) {
                $this->setFlash('danger', 'Thông tin đăng ký không hợp lệ. Vui lòng kiểm tra và thử lại.');
                $this->redirect('?route=auth/register');
            }

            $name = trim($nameInput);
            $email = strtolower(trim($emailInput));
            $phone = preg_replace('/[\s().-]/u', '', trim($phoneInput));
            $nameLength = preg_match_all('/./us', $name);
            $passwordLength = preg_match_all('/./us', $password);

            $this->rememberAuthInput([
                'name' => $name,
                'email' => $email,
                'phone' => trim($phoneInput),
            ]);

            if ($nameLength === false || $nameLength < 2 || $nameLength > 150) {
                $this->setFlash('danger', 'Họ và tên cần có từ 2 đến 150 ký tự.');
                $this->redirect('?route=auth/register');
            }

            if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('danger', 'Vui lòng nhập địa chỉ email hợp lệ (tối đa 150 ký tự).');
                $this->redirect('?route=auth/register');
            }

            if ($phone === null || ($phone !== '' && !preg_match('/^\+?[0-9]{9,15}$/', $phone))) {
                $this->setFlash('danger', 'Số điện thoại cần có từ 9 đến 15 chữ số; có thể nhập mã quốc gia +84.');
                $this->redirect('?route=auth/register');
            }

            if ($passwordLength === false || $passwordLength < 8) {
                $this->setFlash('danger', 'Mật khẩu cần có ít nhất 8 ký tự.');
                $this->redirect('?route=auth/register');
            }

            if ($password !== $passwordConfirmation) {
                $this->setFlash('danger', 'Mật khẩu xác nhận không khớp.');
                $this->redirect('?route=auth/register');
            }

            $userModel = new UserModel();
            if ($userModel->findByEmail($email)) {
                $this->setFlash('danger', 'Email này đã được đăng ký.');
                $this->redirect('?route=auth/register');
            }

            if ($phone !== '' && $userModel->findByPhone($phone)) {
                $this->setFlash('danger', 'Số điện thoại này đã được đăng ký.');
                $this->redirect('?route=auth/register');
            }

            try {
                $userModel->registerCustomer([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone ?: null,
                    'password' => $password,
                ]);

                $this->rememberAuthInput(['email' => $email]);
                $this->setFlash('success', 'Đăng ký tài khoản thành công! Bạn có thể đăng nhập bằng email và mật khẩu vừa tạo.');
                $this->redirect('?route=auth/login');
            } catch (\Throwable $e) {
                error_log('[CineNow register] ' . $e->getMessage());
                $message = $e instanceof \PDOException && $e->getCode() === '23000'
                    ? 'Email hoặc số điện thoại này đã được đăng ký.'
                    : 'Không thể tạo tài khoản lúc này. Vui lòng kiểm tra kết nối database và thử lại.';
                $this->setFlash('danger', $message);
                $this->redirect('?route=auth/register');
            }
        }

        $this->view('auth/register', [
            'pageTitle' => 'Đăng ký tài khoản',
            'oldInput' => $this->consumeAuthInput(),
        ]);
    }

    public function account(): void
    {
        $user = $this->requireAuth();
        $userModel = new UserModel();
        $editUrl = '?route=auth/account&edit=1';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nameInput = $_POST['name'] ?? '';
            $emailInput = $_POST['email'] ?? '';
            $phoneInput = $_POST['phone'] ?? '';

            if (!is_string($nameInput) || !is_string($emailInput) || !is_string($phoneInput)) {
                $this->setFlash('danger', 'Thông tin tài khoản không hợp lệ.');
                $this->redirect($editUrl);
            }

            $name = trim($nameInput);
            $email = strtolower(trim($emailInput));
            $phone = preg_replace('/[\s().-]/u', '', trim($phoneInput));
            $nameLength = preg_match_all('/./us', $name);
            $this->rememberAuthInput([
                'name' => $name,
                'email' => $email,
                'phone' => trim($phoneInput),
            ]);

            if ($nameLength === false || $nameLength < 2 || $nameLength > 150) {
                $this->setFlash('danger', 'Họ và tên cần có từ 2 đến 150 ký tự.');
                $this->redirect($editUrl);
            }

            if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('danger', 'Vui lòng nhập địa chỉ email hợp lệ (tối đa 150 ký tự).');
                $this->redirect($editUrl);
            }

            if ($phone === null || ($phone !== '' && !preg_match('/^\+?[0-9]{9,15}$/', $phone))) {
                $this->setFlash('danger', 'Số điện thoại cần có từ 9 đến 15 chữ số.');
                $this->redirect($editUrl);
            }

            try {
                $userModel->updateProfile(
                    (int) $user['id'],
                    $name,
                    $email,
                    $phone !== '' ? $phone : null
                );

                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['phone'] = $phone !== '' ? $phone : null;
                $this->setFlash('success', 'Thông tin tài khoản đã được cập nhật.');
                $this->redirect('?route=auth/account');
            } catch (\PDOException $e) {
                if ($e->getCode() === '23000') {
                    $this->setFlash('danger', 'Email hoặc số điện thoại này đã được tài khoản khác sử dụng.');
                } else {
                    error_log('[CineNow account update] ' . $e->getMessage());
                    $this->setFlash('danger', 'Không thể cập nhật tài khoản lúc này. Vui lòng thử lại.');
                }
                $this->redirect($editUrl);
            }
        }

        $this->view('auth/account', [
            'pageTitle' => 'Tài khoản của tôi',
            'account' => $user,
            'oldInput' => $this->consumeAuthInput(),
            'isEditing' => ($_GET['edit'] ?? '') === '1',
        ]);
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
        $this->setFlash('info', 'Bạn đã đăng xuất.');
        $this->redirect('?route=home');
    }

    private function rememberAuthInput(array $input): void
    {
        $_SESSION['auth_old_input'] = array_intersect_key(
            $input,
            array_flip(['name', 'email', 'phone'])
        );
    }

    private function consumeAuthInput(): array
    {
        $input = $_SESSION['auth_old_input'] ?? [];
        unset($_SESSION['auth_old_input']);
        return is_array($input) ? $input : [];
    }
}


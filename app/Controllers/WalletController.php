<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\PaymentWalletModel;

class WalletController extends BaseController
{
    public function index(): void
    {
        $user = $this->requireAuth();
        $walletModel = new PaymentWalletModel();

        $wallet = $walletModel->getOrCreateWallet((int) $user['id']);
        $transactions = $walletModel->getWalletTransactions((int) $wallet['id']);

        $this->view('wallet/index', [
            'pageTitle' => 'Ví nội bộ của tôi',
            'wallet' => $wallet,
            'transactions' => $transactions,
        ]);
    }

    public function deposit(): void
    {
        $user = $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = (float) ($_POST['amount'] ?? 0);
            $note = trim($_POST['note'] ?? 'Nạp tiền vào ví nội bộ');

            try {
                $walletModel = new PaymentWalletModel();
                $walletModel->deposit((int) $user['id'], $amount, $note);
                $this->setFlash('success', 'Nạp thành công ' . number_format($amount) . 'đ vào Ví nội bộ!');
            } catch (\Throwable $e) {
                $this->setFlash('danger', $e->getMessage());
            }
        }
        $this->redirect('?route=wallet');
    }
}

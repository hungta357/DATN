<?php

namespace App\Models;

use App\Core\BaseModel;
use Exception;

/**
 * NHÁNH 6: PAYMENTS / PAYMENT METHODS / WALLET
 * Bảng: payment_methods, payments, wallets, wallet_transactions
 */
class PaymentWalletModel extends BaseModel
{
    protected string $table = 'wallets';

    public function getActivePaymentMethods(): array
    {
        return $this->db->query("SELECT * FROM payment_methods WHERE status = 'active' ORDER BY id ASC")->fetchAll();
    }

    public function getOrCreateWallet(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM wallets WHERE user_id = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $wallet = $stmt->fetch();

        if (!$wallet) {
            $id = $this->create([
                'user_id' => $userId,
                'balance' => 0,
                'currency' => 'VND',
            ]);
            return $this->find($id);
        }

        return $wallet;
    }

    public function getWalletTransactions(int $walletId): array
    {
        $stmt = $this->db->prepare("
            SELECT wt.*, b.booking_code
            FROM wallet_transactions wt
            LEFT JOIN bookings b ON b.id = wt.booking_id
            WHERE wt.wallet_id = :wid
            ORDER BY wt.created_at DESC, wt.id DESC
        ");
        $stmt->execute(['wid' => $walletId]);
        return $stmt->fetchAll();
    }

    public function deposit(int $userId, float $amount, string $note = 'Nạp tiền vào ví'): array
    {
        if ($amount <= 0) {
            throw new Exception('Số tiền nạp phải lớn hơn 0.');
        }

        $this->db->beginTransaction();
        try {
            $wallet = $this->getOrCreateWallet($userId);

            $stmtLock = $this->db->prepare("SELECT * FROM wallets WHERE id = :id FOR UPDATE");
            $stmtLock->execute(['id' => $wallet['id']]);
            $lockedWallet = $stmtLock->fetch();

            $newBalance = (float) $lockedWallet['balance'] + $amount;

            $this->db->prepare("UPDATE wallets SET balance = :bal, updated_at = NOW() WHERE id = :id")
                     ->execute(['bal' => $newBalance, 'id' => $lockedWallet['id']]);

            $txCode = 'DEP' . date('YmdHis') . rand(100, 999);
            $this->db->prepare("
                INSERT INTO wallet_transactions (wallet_id, booking_id, type, amount, balance_after, transaction_code, note, status, created_at, updated_at)
                VALUES (:wid, NULL, 'deposit', :amount, :bal_after, :tx, :note, 'success', NOW(), NOW())
            ")->execute([
                'wid' => $lockedWallet['id'],
                'amount' => $amount,
                'bal_after' => $newBalance,
                'tx' => $txCode,
                'note' => $note,
            ]);

            $this->db->commit();
            return $this->find($lockedWallet['id']);
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}


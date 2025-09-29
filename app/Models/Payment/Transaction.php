<?php
class Transaction extends BaseModel {
    protected $table = 'transactions';
    protected $fillable = ['user_id', 'payment_id', 'transaction_type', 'amount', 'currency', 'gateway', 'gateway_transaction_id', 'status', 'metadata'];

    public function createTransaction($data){
        $data['currency'] = $data['currency'] ?? 'USD';
        $data['status'] = $data['status'] ?? 'pending';
        return $this->create($data);
    }

    public function getTransactionsByUser($userId){
        $this->db->query('SELECT t.*, p.course_id, c.title as course_title
                         FROM transactions t
                         LEFT JOIN payments p ON t.payment_id = p.id
                         LEFT JOIN courses c ON p.course_id = c.id
                         WHERE t.user_id = :user_id
                         ORDER BY t.created_at DESC');
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    public function getTransactionsByPayment($paymentId){
        $this->db->query('SELECT * FROM transactions WHERE payment_id = :payment_id ORDER BY created_at DESC');
        $this->db->bind(':payment_id', $paymentId);
        return $this->db->resultSet();
    }

    public function updateTransactionStatus($transactionId, $status, $gatewayResponse = null){
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if($gatewayResponse){
            $data['gateway_response'] = json_encode($gatewayResponse);
        }
        
        return $this->update($transactionId, $data);
    }

    public function getTransactionStats($period = 'month'){
        $dateCondition = '';
        switch($period){
            case 'week':
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                break;
            case 'month':
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                break;
            case 'year':
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                break;
        }

        $this->db->query("SELECT 
                         COUNT(*) as total_transactions,
                         SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                         SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful_transactions,
                         SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_transactions,
                         SUM(CASE WHEN status = 'refunded' THEN amount ELSE 0 END) as total_refunds,
                         AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END) as average_transaction_amount
                         FROM transactions {$dateCondition}");
        return $this->db->single();
    }

    public function getRevenueByGateway($period = 'month'){
        $dateCondition = '';
        switch($period){
            case 'week':
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                break;
            case 'month':
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                break;
            case 'year':
                $dateCondition = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                break;
        }

        $this->db->query("SELECT 
                         gateway,
                         COUNT(*) as transaction_count,
                         SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                         AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END) as average_amount
                         FROM transactions 
                         {$dateCondition}
                         GROUP BY gateway
                         ORDER BY total_revenue DESC");
        return $this->db->resultSet();
    }

    public function getFailedTransactions($limit = 50){
        $this->db->query('SELECT t.*, u.name as user_name, u.email as user_email
                         FROM transactions t
                         JOIN users u ON t.user_id = u.id
                         WHERE t.status = "failed"
                         ORDER BY t.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getRefundedTransactions($limit = 50){
        $this->db->query('SELECT t.*, u.name as user_name, u.email as user_email
                         FROM transactions t
                         JOIN users u ON t.user_id = u.id
                         WHERE t.status = "refunded"
                         ORDER BY t.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function processRefund($transactionId, $refundAmount = null, $reason = ''){
        $transaction = $this->find($transactionId);
        if(!$transaction || $transaction->status !== 'completed'){
            return false;
        }

        $refundAmount = $refundAmount ?? $transaction->amount;
        
        // Create refund transaction
        $refundData = [
            'user_id' => $transaction->user_id,
            'payment_id' => $transaction->payment_id,
            'transaction_type' => 'refund',
            'amount' => -$refundAmount, // Negative amount for refund
            'currency' => $transaction->currency,
            'gateway' => $transaction->gateway,
            'status' => 'completed',
            'metadata' => json_encode([
                'original_transaction_id' => $transactionId,
                'refund_reason' => $reason
            ])
        ];
        
        $refundTransactionId = $this->create($refundData);
        
        if($refundTransactionId){
            // Update original transaction status
            $this->update($transactionId, ['status' => 'refunded']);
            return $refundTransactionId;
        }
        
        return false;
    }

    public function getDailyRevenue($days = 30){
        $this->db->query('SELECT 
                         DATE(created_at) as date,
                         SUM(CASE WHEN status = "completed" AND amount > 0 THEN amount ELSE 0 END) as revenue,
                         COUNT(CASE WHEN status = "completed" AND amount > 0 THEN 1 ELSE NULL END) as transaction_count
                         FROM transactions
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                         GROUP BY DATE(created_at)
                         ORDER BY date DESC');
        $this->db->bind(':days', $days);
        return $this->db->resultSet();
    }
}
?>
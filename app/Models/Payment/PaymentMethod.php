<?php
class PaymentMethod extends BaseModel {
    protected $table = 'payment_methods';
    protected $fillable = ['user_id', 'type', 'provider', 'provider_id', 'last_four', 'expiry_month', 'expiry_year', 'is_default'];

    public function addPaymentMethod($data){
        // If this is the first payment method, make it default
        if($this->getUserPaymentMethodCount($data['user_id']) == 0){
            $data['is_default'] = 1;
        }
        
        return $this->create($data);
    }

    public function getUserPaymentMethods($userId){
        $this->db->query('SELECT * FROM payment_methods 
                         WHERE user_id = :user_id 
                         ORDER BY is_default DESC, created_at DESC');
        $this->db->bind(':user_id', $userId);
        return $this->db->resultSet();
    }

    public function getDefaultPaymentMethod($userId){
        $this->db->query('SELECT * FROM payment_methods 
                         WHERE user_id = :user_id AND is_default = 1 
                         LIMIT 1');
        $this->db->bind(':user_id', $userId);
        return $this->db->single();
    }

    public function setDefaultPaymentMethod($userId, $paymentMethodId){
        // Remove default from all user's payment methods
        $this->db->query('UPDATE payment_methods SET is_default = 0 WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        $this->db->execute();
        
        // Set new default
        return $this->update($paymentMethodId, ['is_default' => 1]);
    }

    public function removePaymentMethod($paymentMethodId){
        $paymentMethod = $this->find($paymentMethodId);
        if(!$paymentMethod){
            return false;
        }

        $result = $this->delete($paymentMethodId);
        
        // If this was the default method, set another as default
        if($result && $paymentMethod->is_default){
            $this->setFirstAsDefault($paymentMethod->user_id);
        }
        
        return $result;
    }

    private function setFirstAsDefault($userId){
        $this->db->query('SELECT id FROM payment_methods 
                         WHERE user_id = :user_id 
                         ORDER BY created_at ASC 
                         LIMIT 1');
        $this->db->bind(':user_id', $userId);
        $firstMethod = $this->db->single();
        
        if($firstMethod){
            $this->update($firstMethod->id, ['is_default' => 1]);
        }
    }

    private function getUserPaymentMethodCount($userId){
        $this->db->query('SELECT COUNT(*) as count FROM payment_methods WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? $result->count : 0;
    }

    public function updatePaymentMethod($paymentMethodId, $data){
        // Don't allow updating sensitive data
        unset($data['provider_id'], $data['user_id']);
        return $this->update($paymentMethodId, $data);
    }

    public function getExpiredPaymentMethods(){
        $this->db->query('SELECT pm.*, u.name as user_name, u.email as user_email
                         FROM payment_methods pm
                         JOIN users u ON pm.user_id = u.id
                         WHERE pm.expiry_year < YEAR(NOW()) 
                         OR (pm.expiry_year = YEAR(NOW()) AND pm.expiry_month < MONTH(NOW()))
                         ORDER BY pm.expiry_year, pm.expiry_month');
        return $this->db->resultSet();
    }

    public function getExpiringPaymentMethods($months = 2){
        $this->db->query('SELECT pm.*, u.name as user_name, u.email as user_email
                         FROM payment_methods pm
                         JOIN users u ON pm.user_id = u.id
                         WHERE (pm.expiry_year = YEAR(NOW()) AND pm.expiry_month <= MONTH(NOW()) + :months)
                         OR (pm.expiry_year = YEAR(NOW()) + 1 AND pm.expiry_month <= MONTH(NOW()) + :months - 12)
                         ORDER BY pm.expiry_year, pm.expiry_month');
        $this->db->bind(':months', $months);
        return $this->db->resultSet();
    }

    public function getPaymentMethodStats(){
        $this->db->query('SELECT 
                         type,
                         provider,
                         COUNT(*) as count,
                         COUNT(CASE WHEN is_default = 1 THEN 1 ELSE NULL END) as default_count
                         FROM payment_methods
                         GROUP BY type, provider
                         ORDER BY count DESC');
        return $this->db->resultSet();
    }

    public function validatePaymentMethod($paymentMethodId, $userId){
        $this->db->query('SELECT id FROM payment_methods 
                         WHERE id = :id AND user_id = :user_id');
        $this->db->bind(':id', $paymentMethodId);
        $this->db->bind(':user_id', $userId);
        return $this->db->single() !== false;
    }
}
?>
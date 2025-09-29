<?php
class Subscription extends BaseModel {
    protected $table = 'subscriptions';
    protected $fillable = ['student_id', 'plan_id', 'stripe_subscription_id', 'status', 'start_date', 'end_date', 'trial_end_date'];

    public function createSubscription($data){
        $data['start_date'] = date('Y-m-d H:i:s');
        $data['status'] = 'active';
        
        // Calculate end date based on plan
        $plan = $this->getPlanDetails($data['plan_id']);
        if($plan){
            $data['end_date'] = date('Y-m-d H:i:s', strtotime('+' . $plan['duration']));
        }
        
        return $this->create($data);
    }

    public function getActiveSubscription($studentId){
        $this->db->query('SELECT * FROM subscriptions 
                         WHERE student_id = :student_id 
                         AND status = "active" 
                         AND end_date > NOW()
                         ORDER BY created_at DESC 
                         LIMIT 1');
        $this->db->bind(':student_id', $studentId);
        return $this->db->single();
    }

    public function hasActiveSubscription($studentId){
        return $this->getActiveSubscription($studentId) !== false;
    }

    public function cancelSubscription($subscriptionId, $reason = ''){
        $data = [
            'status' => 'canceled',
            'canceled_at' => date('Y-m-d H:i:s'),
            'cancellation_reason' => $reason
        ];
        
        return $this->update($subscriptionId, $data);
    }

    public function renewSubscription($subscriptionId){
        $subscription = $this->find($subscriptionId);
        if(!$subscription){
            return false;
        }

        $plan = $this->getPlanDetails($subscription->plan_id);
        if(!$plan){
            return false;
        }

        $data = [
            'status' => 'active',
            'end_date' => date('Y-m-d H:i:s', strtotime($subscription->end_date . ' +' . $plan['duration'])),
            'renewed_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($subscriptionId, $data);
    }

    public function getSubscriptionsByStudent($studentId){
        $this->db->query('SELECT * FROM subscriptions 
                         WHERE student_id = :student_id 
                         ORDER BY created_at DESC');
        $this->db->bind(':student_id', $studentId);
        return $this->db->resultSet();
    }

    public function getAllSubscriptionsForAdmin(){
        $this->db->query('SELECT s.*, u.name as student_name, u.email as student_email
                         FROM subscriptions s
                         JOIN users u ON s.student_id = u.id
                         ORDER BY s.created_at DESC');
        return $this->db->resultSet();
    }

    public function getSubscriptionStats(){
        $this->db->query('SELECT 
                         COUNT(*) as total_subscriptions,
                         SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_subscriptions,
                         SUM(CASE WHEN status = "canceled" THEN 1 ELSE 0 END) as canceled_subscriptions,
                         SUM(CASE WHEN status = "expired" THEN 1 ELSE 0 END) as expired_subscriptions,
                         COUNT(DISTINCT student_id) as unique_subscribers
                         FROM subscriptions');
        return $this->db->single();
    }

    public function getExpiringSubscriptions($days = 7){
        $this->db->query('SELECT s.*, u.name as student_name, u.email as student_email
                         FROM subscriptions s
                         JOIN users u ON s.student_id = u.id
                         WHERE s.status = "active" 
                         AND s.end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :days DAY)
                         ORDER BY s.end_date ASC');
        $this->db->bind(':days', $days);
        return $this->db->resultSet();
    }

    public function processExpiredSubscriptions(){
        $this->db->query('UPDATE subscriptions 
                         SET status = "expired" 
                         WHERE status = "active" AND end_date < NOW()');
        return $this->db->execute();
    }

    public function getRevenueByPlan($period = 'month'){
        $dateCondition = '';
        switch($period){
            case 'week':
                $dateCondition = 'WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
                break;
            case 'month':
                $dateCondition = 'WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
                break;
            case 'year':
                $dateCondition = 'WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                break;
        }

        $this->db->query("SELECT 
                         s.plan_id,
                         COUNT(*) as subscription_count,
                         SUM(p.amount) as total_revenue
                         FROM subscriptions s
                         JOIN payments p ON s.id = p.subscription_id
                         {$dateCondition}
                         GROUP BY s.plan_id
                         ORDER BY total_revenue DESC");
        return $this->db->resultSet();
    }

    private function getPlanDetails($planId){
        $plans = [
            'basic_monthly' => ['duration' => '1 month', 'price' => 9.99],
            'basic_yearly' => ['duration' => '1 year', 'price' => 99.99],
            'premium_monthly' => ['duration' => '1 month', 'price' => 19.99],
            'premium_yearly' => ['duration' => '1 year', 'price' => 199.99],
            'enterprise_monthly' => ['duration' => '1 month', 'price' => 49.99],
            'enterprise_yearly' => ['duration' => '1 year', 'price' => 499.99]
        ];
        
        return $plans[$planId] ?? null;
    }

    public function upgradeSubscription($subscriptionId, $newPlanId){
        $subscription = $this->find($subscriptionId);
        if(!$subscription){
            return false;
        }

        $data = [
            'plan_id' => $newPlanId,
            'upgraded_at' => date('Y-m-d H:i:s'),
            'previous_plan_id' => $subscription->plan_id
        ];
        
        return $this->update($subscriptionId, $data);
    }

    public function downgradeSubscription($subscriptionId, $newPlanId){
        $subscription = $this->find($subscriptionId);
        if(!$subscription){
            return false;
        }

        $data = [
            'plan_id' => $newPlanId,
            'downgraded_at' => date('Y-m-d H:i:s'),
            'previous_plan_id' => $subscription->plan_id
        ];
        
        return $this->update($subscriptionId, $data);
    }
}
?>
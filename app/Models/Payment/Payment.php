<?php
class Payment {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function createPayment($data){
        $this->db->query('INSERT INTO payments (student_id, course_id, amount, transaction_id, payment_status) VALUES (:student_id, :course_id, :amount, :transaction_id, :payment_status)');
        $this->db->bind(':student_id', $data['student_id']);
        $this->db->bind(':course_id', $data['course_id']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':transaction_id', $data['transaction_id']);
        $this->db->bind(':payment_status', $data['payment_status']);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function createSubscription($data){
        $this->db->query('INSERT INTO subscriptions (student_id, plan_id, stripe_subscription_id, status, start_date, end_date) VALUES (:student_id, :plan_id, :stripe_subscription_id, :status, :start_date, :end_date)');
        $this->db->bind(':student_id', $data['student_id']);
        $this->db->bind(':plan_id', $data['plan_id']);
        $this->db->bind(':stripe_subscription_id', $data['stripe_subscription_id']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':start_date', $data['start_date']);
        $this->db->bind(':end_date', $data['end_date']);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }
}
?>
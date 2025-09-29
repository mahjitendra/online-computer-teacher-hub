<?php
class PaymentManagementController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->paymentModel = $this->model('Payment');
        $this->subscriptionModel = $this->model('Subscription');
    }

    public function index(){
        $payments = $this->paymentModel->getAllPaymentsForAdmin();
        $paymentStats = $this->paymentModel->getPaymentStats();
        
        $data = [
            'title' => 'Payment Management',
            'payments' => $payments,
            'paymentStats' => $paymentStats
        ];

        $this->view('pages/admin/payments', $data);
    }

    public function subscriptions(){
        $subscriptions = $this->subscriptionModel->getAllSubscriptionsForAdmin();
        $subscriptionStats = $this->subscriptionModel->getSubscriptionStats();
        
        $data = [
            'title' => 'Subscription Management',
            'subscriptions' => $subscriptions,
            'subscriptionStats' => $subscriptionStats
        ];

        $this->view('pages/admin/subscriptions', $data);
    }

    public function refund($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $reason = $_POST['reason'] ?? '';
            if($this->paymentModel->processRefund($id, $reason)){
                header('location: ' . URLROOT . '/admin/payments?success=refunded');
            } else {
                header('location: ' . URLROOT . '/admin/payments?error=refund');
            }
        }
    }
}
?>
<?php
class PaymentApiController extends ApiController {
    public function __construct(){
        $this->paymentModel = $this->model('Payment');
        $this->paymentService = new PaymentService();
    }

    // GET /api/v1/payments
    public function index(){
        $studentId = $_GET['student_id'] ?? null;
        if(!$studentId){
            $this->jsonResponse(['error' => 'Student ID required'], 400);
        }

        $payments = $this->paymentModel->getPaymentsByStudent($studentId);
        $this->jsonResponse($payments);
    }

    // POST /api/v1/payments/create
    public function create(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        $data = [
            'student_id' => $input['student_id'] ?? null,
            'course_id' => $input['course_id'] ?? null,
            'amount' => $input['amount'] ?? 0,
            'payment_method' => $input['payment_method'] ?? 'stripe'
        ];

        if(!$data['student_id'] || !$data['course_id']){
            $this->jsonResponse(['error' => 'Student ID and Course ID required'], 400);
        }

        $result = $this->paymentService->processPayment($data);
        if($result['success']){
            $this->jsonResponse(['success' => true, 'payment_id' => $result['payment_id']]);
        } else {
            $this->jsonResponse(['error' => $result['message']], 400);
        }
    }

    // GET /api/v1/payments/{id}/status
    public function status($id){
        $payment = $this->paymentModel->getPaymentById($id);
        if($payment){
            $this->jsonResponse(['status' => $payment->payment_status]);
        } else {
            $this->jsonResponse(['error' => 'Payment not found'], 404);
        }
    }
}
?>
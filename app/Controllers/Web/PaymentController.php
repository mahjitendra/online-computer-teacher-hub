<?php
class PaymentController extends Controller {
    public function __construct(){
        // Middleware to ensure user is logged in
        if(!isset($_SESSION['user_id'])){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }

        $this->paymentModel = $this->model('Payment');
        $this->courseModel = $this->model('Course');
        $this->enrollmentModel = $this->model('Enrollment');
    }

    // Show payment page for a course
    public function checkout($course_id){
        $course = $this->courseModel->getCourseById($course_id);

        // In a real app, you'd check if the user is already enrolled
        // and handle cases where the course is free.

        $data = [
            'course' => $course
        ];

        $this->view('pages/frontend/checkout', $data);
    }

    // Process the payment
    public function process($course_id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $course = $this->courseModel->getCourseById($course_id);

            // Simulate interaction with a payment gateway (e.g., Stripe)
            // In a real app, this would involve using the Stripe PHP library,
            // creating a charge, and handling webhooks.

            // 1. Collect payment info from POST (e.g., token from Stripe.js)
            $token = $_POST['stripeToken']; // This is a dummy token

            // 2. Simulate a successful charge
            $transaction_id = 'txn_' . uniqid();
            $payment_status = 'completed';

            // 3. Record the payment in our database
            $paymentData = [
                'student_id' => $_SESSION['user_id'],
                'course_id' => $course_id,
                'amount' => $course->price,
                'transaction_id' => $transaction_id,
                'payment_status' => $payment_status
            ];

            if($this->paymentModel->createPayment($paymentData)){
                // 4. If payment is successful, enroll the student
                $enrollmentData = [
                    'student_id' => $_SESSION['user_id'],
                    'course_id' => $course_id
                ];
                $this->enrollmentModel->enrollStudent($enrollmentData);

                // 5. Redirect to the course page with a success message
                header('location: ' . URLROOT . '/courses/show/' . $course_id . '?payment=success');
            } else {
                die('Something went wrong with recording the payment.');
            }

        } else {
            header('location: ' . URLROOT . '/');
        }
    }
}
?>
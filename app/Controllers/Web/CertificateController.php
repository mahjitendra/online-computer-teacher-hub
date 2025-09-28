<?php
class CertificateController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id'])){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->certificateModel = $this->model('Certificate');
        $this->courseModel = $this->model('Course');
    }

    public function index(){
        $certificates = $this->certificateModel->getCertificatesByStudent($_SESSION['user_id']);

        $data = [
            'certificates' => $certificates
        ];

        $this->view('pages/student/certificates', $data);
    }

    public function show($id){
        $certificate = $this->certificateModel->getCertificateById($id);
        
        // Check if this certificate belongs to the current user
        if($certificate->student_id != $_SESSION['user_id']){
            header('location: ' . URLROOT . '/certificates');
            return;
        }

        $course = $this->courseModel->getCourseById($certificate->course_id);

        $data = [
            'certificate' => $certificate,
            'course' => $course
        ];

        $this->view('pages/student/certificate-view', $data);
    }

    public function download($id){
        $certificate = $this->certificateModel->getCertificateById($id);
        
        // Check if this certificate belongs to the current user
        if($certificate->student_id != $_SESSION['user_id']){
            header('location: ' . URLROOT . '/certificates');
            return;
        }

        // Generate PDF certificate
        $this->generateCertificatePDF($certificate);
    }

    public function verify($code){
        $certificate = $this->certificateModel->getCertificateByCode($code);
        
        if($certificate){
            $course = $this->courseModel->getCourseById($certificate->course_id);
            $data = [
                'certificate' => $certificate,
                'course' => $course,
                'valid' => true
            ];
        } else {
            $data = [
                'valid' => false,
                'code' => $code
            ];
        }

        $this->view('pages/frontend/certificate-verify', $data);
    }

    private function generateCertificatePDF($certificate){
        // PDF generation logic would go here
        // For now, we'll just redirect to the view
        header('location: ' . URLROOT . '/certificates/show/' . $certificate->id);
    }
}
?>
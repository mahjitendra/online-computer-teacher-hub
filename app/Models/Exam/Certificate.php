<?php
class Certificate extends BaseModel {
    protected $table = 'certificates';
    protected $fillable = ['student_id', 'course_id', 'exam_id', 'certificate_code', 'issued_at', 'certificate_data'];

    public function generateCertificate($examResult){
        $certificateCode = $this->generateCertificateCode();
        
        $data = [
            'student_id' => $examResult->student_id,
            'course_id' => $examResult->course_id ?? null,
            'exam_id' => $examResult->exam_id,
            'certificate_code' => $certificateCode,
            'issued_at' => date('Y-m-d H:i:s'),
            'certificate_data' => json_encode([
                'student_name' => $examResult->student_name,
                'exam_title' => $examResult->exam_title,
                'course_title' => $examResult->course_title ?? '',
                'score' => $examResult->score,
                'percentage' => $examResult->percentage,
                'grade' => $examResult->grade,
                'completion_date' => $examResult->end_time
            ])
        ];

        $certificateId = $this->create($data);
        
        if($certificateId){
            // Generate PDF certificate
            $this->generatePDFCertificate($certificateId);
            return $certificateId;
        }
        
        return false;
    }

    public function getCertificatesByStudent($studentId){
        $this->db->query('SELECT c.*, e.title as exam_title, co.title as course_title
                         FROM certificates c
                         LEFT JOIN exams e ON c.exam_id = e.id
                         LEFT JOIN courses co ON c.course_id = co.id
                         WHERE c.student_id = :student_id
                         ORDER BY c.issued_at DESC');
        $this->db->bind(':student_id', $studentId);
        return $this->db->resultSet();
    }

    public function getCertificateByCode($code){
        $this->db->query('SELECT c.*, u.name as student_name, e.title as exam_title, co.title as course_title
                         FROM certificates c
                         JOIN users u ON c.student_id = u.id
                         LEFT JOIN exams e ON c.exam_id = e.id
                         LEFT JOIN courses co ON c.course_id = co.id
                         WHERE c.certificate_code = :code');
        $this->db->bind(':code', $code);
        return $this->db->single();
    }

    public function verifyCertificate($code){
        $certificate = $this->getCertificateByCode($code);
        return $certificate !== false;
    }

    public function getCertificateStats(){
        $this->db->query('SELECT 
                         COUNT(*) as total_certificates,
                         COUNT(DISTINCT student_id) as unique_students,
                         COUNT(DISTINCT course_id) as unique_courses,
                         DATE(issued_at) as issue_date,
                         COUNT(*) as daily_count
                         FROM certificates
                         WHERE issued_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                         GROUP BY DATE(issued_at)
                         ORDER BY issue_date DESC');
        return $this->db->resultSet();
    }

    private function generateCertificateCode(){
        do {
            $code = 'CERT-' . strtoupper(uniqid());
        } while($this->getCertificateByCode($code));
        
        return $code;
    }

    private function generatePDFCertificate($certificateId){
        $certificate = $this->find($certificateId);
        $certificateData = json_decode($certificate->certificate_data, true);
        
        // Use a PDF generation library like TCPDF or FPDF
        // For now, we'll just create a placeholder
        $pdfPath = 'uploads/certificates/' . $certificate->certificate_code . '.pdf';
        
        // Generate PDF content here
        // This would involve creating a professional certificate template
        
        return $pdfPath;
    }

    public function downloadCertificate($certificateId){
        $certificate = $this->find($certificateId);
        if(!$certificate){
            return false;
        }

        $pdfPath = 'uploads/certificates/' . $certificate->certificate_code . '.pdf';
        
        if(file_exists($pdfPath)){
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="certificate_' . $certificate->certificate_code . '.pdf"');
            header('Content-Length: ' . filesize($pdfPath));
            readfile($pdfPath);
            exit();
        }
        
        return false;
    }

    public function revokeCertificate($certificateId, $reason = ''){
        $data = [
            'status' => 'revoked',
            'revoked_at' => date('Y-m-d H:i:s'),
            'revocation_reason' => $reason
        ];
        
        return $this->update($certificateId, $data);
    }
}
?>
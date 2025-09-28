<?php
class CreateJobApplicationsTable {
    public function up() {
        $db = new Database;
        $sql = "CREATE TABLE IF NOT EXISTS job_applications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            job_id INT NOT NULL,
            application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('submitted', 'viewed', 'interviewing', 'rejected', 'hired') DEFAULT 'submitted',
            resume_path VARCHAR(255), -- Path to the uploaded resume
            cover_letter TEXT,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
        )";
        $db->query($sql);
        $db->execute();
    }

    public function down() {
        $db = new Database;
        $sql = "DROP TABLE IF EXISTS job_applications";
        $db->query($sql);
        $db->execute();
    }
}
?>
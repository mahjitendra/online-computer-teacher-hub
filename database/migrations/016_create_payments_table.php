<?php
class CreatePaymentsTable {
    public function up() {
        $db = new Database;
        $sql = "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            course_id INT NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            payment_gateway VARCHAR(50) DEFAULT 'stripe', -- e.g., stripe, paypal
            transaction_id VARCHAR(255) NOT NULL,
            payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        )";
        $db->query($sql);
        $db->execute();
    }

    public function down() {
        $db = new Database;
        $sql = "DROP TABLE IF EXISTS payments";
        $db->query($sql);
        $db->execute();
    }
}
?>
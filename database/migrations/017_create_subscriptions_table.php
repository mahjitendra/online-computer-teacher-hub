<?php
class CreateSubscriptionsTable {
    public function up() {
        $db = new Database;
        $sql = "CREATE TABLE IF NOT EXISTS subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            plan_id VARCHAR(255) NOT NULL, -- e.g., 'basic_monthly', 'premium_yearly'
            stripe_subscription_id VARCHAR(255) NOT NULL,
            status ENUM('active', 'canceled', 'past_due') NOT NULL,
            start_date TIMESTAMP NOT NULL,
            end_date TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $db->query($sql);
        $db->execute();
    }

    public function down() {
        $db = new Database;
        $sql = "DROP TABLE IF EXISTS subscriptions";
        $db->query($sql);
        $db->execute();
    }
}
?>
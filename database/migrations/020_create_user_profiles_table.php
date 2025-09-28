<?php
class CreateUserProfilesTable {
    public function up() {
        $db = new Database;
        $sql = "CREATE TABLE IF NOT EXISTS user_profiles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL UNIQUE,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            phone VARCHAR(20),
            address TEXT,
            bio TEXT,
            skills TEXT,
            experience TEXT,
            education TEXT,
            avatar VARCHAR(255),
            linkedin VARCHAR(255),
            github VARCHAR(255),
            twitter VARCHAR(255),
            website VARCHAR(255),
            email_notifications BOOLEAN DEFAULT TRUE,
            sms_notifications BOOLEAN DEFAULT FALSE,
            job_alerts BOOLEAN DEFAULT TRUE,
            course_updates BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $db->query($sql);
        $db->execute();
    }

    public function down() {
        $db = new Database;
        $sql = "DROP TABLE IF EXISTS user_profiles";
        $db->query($sql);
        $db->execute();
    }
}
?>
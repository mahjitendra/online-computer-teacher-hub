<?php
class CreateCourseMaterialsTable {
    public function up() {
        $db = new Database;
        $sql = "CREATE TABLE IF NOT EXISTS course_materials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tutorial_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (tutorial_id) REFERENCES tutorials(id) ON DELETE CASCADE
        )";
        $db->query($sql);
        $db->execute();
    }

    public function down() {
        $db = new Database;
        $sql = "DROP TABLE IF EXISTS course_materials";
        $db->query($sql);
        $db->execute();
    }
}
?>
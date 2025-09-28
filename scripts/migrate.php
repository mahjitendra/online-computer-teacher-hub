<?php
require_once dirname(__FILE__) . '/../app/bootstrap.php';

// Manually include the Database utility as it's not part of the core autoload
require_once dirname(__FILE__) . '/../app/Utils/Database.php';

// Include all migration files
require_once dirname(__FILE__) . '/../database/migrations/001_create_users_table.php';
require_once dirname(__FILE__) . '/../database/migrations/002_create_courses_table.php';
require_once dirname(__FILE__) . '/../database/migrations/003_create_course_categories_table.php';
require_once dirname(__FILE__) . '/../database/migrations/004_create_tutorials_table.php';
require_once dirname(__FILE__) . '/../database/migrations/005_create_course_materials_table.php';

// Run all migrations
try {
    $migration1 = new CreateUsersTable();
    $migration1->up();
    echo "Migration for users table created successfully.\n";

    $migration2 = new CreateCoursesTable();
    $migration2->up();
    echo "Migration for courses table created successfully.\n";

    $migration3 = new CreateCourseCategoriesTable();
    $migration3->up();
    echo "Migration for course_categories table created successfully.\n";

    $migration4 = new CreateTutorialsTable();
    $migration4->up();
    echo "Migration for tutorials table created successfully.\n";

    $migration5 = new CreateCourseMaterialsTable();
    $migration5->up();
    echo "Migration for course_materials table created successfully.\n";

} catch (Exception $e) {
    echo "Error running migration: " . $e->getMessage() . "\n";
}
?>
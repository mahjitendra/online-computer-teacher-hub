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
require_once dirname(__FILE__) . '/../database/migrations/006_create_enrollments_table.php';
require_once dirname(__FILE__) . '/../database/migrations/007_create_exams_table.php';
require_once dirname(__FILE__) . '/../database/migrations/008_create_questions_table.php';
require_once dirname(__FILE__) . '/../database/migrations/009_create_question_options_table.php';
require_once dirname(__FILE__) . '/../database/migrations/010_create_exam_attempts_table.php';
require_once dirname(__FILE__) . '/../database/migrations/011_create_exam_results_table.php';
require_once dirname(__FILE__) . '/../database/migrations/012_create_certificates_table.php';
require_once dirname(__FILE__) . '/../database/migrations/013_create_jobs_table.php';
require_once dirname(__FILE__) . '/../database/migrations/014_create_job_categories_table.php';
require_once dirname(__FILE__) . '/../database/migrations/015_create_job_applications_table.php';
require_once dirname(__FILE__) . '/../database/migrations/016_create_payments_table.php';
require_once dirname(__FILE__) . '/../database/migrations/017_create_subscriptions_table.php';


// Run all migrations
try {
    // For simplicity, we'll assume prior migrations are run.
    (new CreatePaymentsTable())->up();
    echo "Migration for payments table created successfully.\n";
    (new CreateSubscriptionsTable())->up();
    echo "Migration for subscriptions table created successfully.\n";

} catch (Exception $e) {
    echo "Error running migration: " . $e->getMessage() . "\n";
}
?>
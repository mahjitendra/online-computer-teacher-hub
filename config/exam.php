<?php
// Exam Configuration
define('EXAM_TIME_LIMIT_ENABLED', $_ENV['EXAM_TIME_LIMIT_ENABLED'] ?? true);
define('EXAM_DEFAULT_DURATION', $_ENV['EXAM_DEFAULT_DURATION'] ?? 60); // minutes
define('EXAM_MAX_DURATION', $_ENV['EXAM_MAX_DURATION'] ?? 180); // 3 hours

// Question Types
define('QUESTION_TYPES', [
    'multiple_choice' => 'Multiple Choice',
    'true_false' => 'True/False',
    'short_answer' => 'Short Answer',
    'essay' => 'Essay',
    'fill_blank' => 'Fill in the Blank',
    'matching' => 'Matching'
]);

// Exam Settings
define('EXAM_RANDOMIZE_QUESTIONS', $_ENV['EXAM_RANDOMIZE_QUESTIONS'] ?? true);
define('EXAM_RANDOMIZE_OPTIONS', $_ENV['EXAM_RANDOMIZE_OPTIONS'] ?? true);
define('EXAM_SHOW_RESULTS_IMMEDIATELY', $_ENV['EXAM_SHOW_RESULTS_IMMEDIATELY'] ?? true);
define('EXAM_ALLOW_REVIEW', $_ENV['EXAM_ALLOW_REVIEW'] ?? true);

// Grading
define('EXAM_PASSING_SCORE', $_ENV['EXAM_PASSING_SCORE'] ?? 70); // percentage
define('EXAM_AUTO_GRADE', $_ENV['EXAM_AUTO_GRADE'] ?? true);

// Attempts
define('EXAM_MAX_ATTEMPTS', $_ENV['EXAM_MAX_ATTEMPTS'] ?? 3);
define('EXAM_ATTEMPT_COOLDOWN', $_ENV['EXAM_ATTEMPT_COOLDOWN'] ?? 24); // hours

// Proctoring
define('EXAM_PROCTORING_ENABLED', $_ENV['EXAM_PROCTORING_ENABLED'] ?? false);
define('EXAM_WEBCAM_REQUIRED', $_ENV['EXAM_WEBCAM_REQUIRED'] ?? false);
define('EXAM_SCREEN_RECORDING', $_ENV['EXAM_SCREEN_RECORDING'] ?? false);
define('EXAM_BROWSER_LOCKDOWN', $_ENV['EXAM_BROWSER_LOCKDOWN'] ?? false);

// Anti-Cheating
define('EXAM_PREVENT_COPY_PASTE', $_ENV['EXAM_PREVENT_COPY_PASTE'] ?? true);
define('EXAM_PREVENT_RIGHT_CLICK', $_ENV['EXAM_PREVENT_RIGHT_CLICK'] ?? true);
define('EXAM_FULLSCREEN_REQUIRED', $_ENV['EXAM_FULLSCREEN_REQUIRED'] ?? false);
define('EXAM_TAB_SWITCH_DETECTION', $_ENV['EXAM_TAB_SWITCH_DETECTION'] ?? true);

// Certificates
define('CERTIFICATE_AUTO_GENERATE', $_ENV['CERTIFICATE_AUTO_GENERATE'] ?? true);
define('CERTIFICATE_TEMPLATE_PATH', $_ENV['CERTIFICATE_TEMPLATE_PATH'] ?? dirname(__DIR__) . '/public/assets/images/certificates/');
define('CERTIFICATE_VERIFICATION_ENABLED', $_ENV['CERTIFICATE_VERIFICATION_ENABLED'] ?? true);
?>
<?php
// Job Board Configuration
define('JOB_POSTING_ENABLED', $_ENV['JOB_POSTING_ENABLED'] ?? true);
define('JOB_APPROVAL_REQUIRED', $_ENV['JOB_APPROVAL_REQUIRED'] ?? true);
define('JOB_POSTING_FEE', $_ENV['JOB_POSTING_FEE'] ?? 0); // Free by default

// Job Categories
define('JOB_CATEGORIES', [
    'software_development' => 'Software Development',
    'web_development' => 'Web Development',
    'mobile_development' => 'Mobile Development',
    'data_science' => 'Data Science',
    'cybersecurity' => 'Cybersecurity',
    'network_administration' => 'Network Administration',
    'database_administration' => 'Database Administration',
    'it_support' => 'IT Support',
    'project_management' => 'Project Management',
    'quality_assurance' => 'Quality Assurance'
]);

// Job Types
define('JOB_TYPES', [
    'full_time' => 'Full Time',
    'part_time' => 'Part Time',
    'contract' => 'Contract',
    'freelance' => 'Freelance',
    'internship' => 'Internship',
    'remote' => 'Remote'
]);

// Experience Levels
define('EXPERIENCE_LEVELS', [
    'entry' => 'Entry Level',
    'junior' => 'Junior (1-3 years)',
    'mid' => 'Mid Level (3-5 years)',
    'senior' => 'Senior (5+ years)',
    'lead' => 'Lead/Manager',
    'executive' => 'Executive'
]);

// Application Settings
define('JOB_APPLICATION_DEADLINE_DAYS', $_ENV['JOB_APPLICATION_DEADLINE_DAYS'] ?? 30);
define('JOB_AUTO_EXPIRE_DAYS', $_ENV['JOB_AUTO_EXPIRE_DAYS'] ?? 60);
define('JOB_FEATURED_DURATION_DAYS', $_ENV['JOB_FEATURED_DURATION_DAYS'] ?? 30);

// Resume/CV Settings
define('RESUME_UPLOAD_ENABLED', $_ENV['RESUME_UPLOAD_ENABLED'] ?? true);
define('RESUME_MAX_SIZE', $_ENV['RESUME_MAX_SIZE'] ?? 5242880); // 5MB
define('RESUME_ALLOWED_FORMATS', ['pdf', 'doc', 'docx']);

// Job Alerts
define('JOB_ALERTS_ENABLED', $_ENV['JOB_ALERTS_ENABLED'] ?? true);
define('JOB_ALERT_FREQUENCY', $_ENV['JOB_ALERT_FREQUENCY'] ?? 'daily'); // daily, weekly

// Government Jobs
define('GOVERNMENT_JOBS_ENABLED', $_ENV['GOVERNMENT_JOBS_ENABLED'] ?? true);
define('GOVERNMENT_JOB_CATEGORIES', [
    'civil_services' => 'Civil Services',
    'banking' => 'Banking',
    'railway' => 'Railway',
    'defense' => 'Defense',
    'police' => 'Police',
    'teaching' => 'Teaching',
    'healthcare' => 'Healthcare',
    'engineering' => 'Engineering'
]);

// Job Search
define('JOB_SEARCH_ENABLED', $_ENV['JOB_SEARCH_ENABLED'] ?? true);
define('JOB_SEARCH_FILTERS', [
    'location',
    'category',
    'type',
    'experience_level',
    'salary_range',
    'company_size'
]);
?>
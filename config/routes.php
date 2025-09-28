<?php
// Route Configuration
define('ROUTES', [
    // Frontend Routes
    '' => 'HomeController@index',
    'home' => 'HomeController@index',
    'about' => 'HomeController@about',
    'contact' => 'HomeController@contact',
    
    // Authentication Routes
    'auth/login' => 'AuthController@login',
    'auth/register' => 'AuthController@register',
    'auth/logout' => 'AuthController@logout',
    'auth/forgot-password' => 'AuthController@forgotPassword',
    'auth/reset-password' => 'AuthController@resetPassword',
    
    // Course Routes
    'courses' => 'CourseController@index',
    'courses/show/{id}' => 'CourseController@show',
    'courses/enroll/{id}' => 'CourseController@enroll',
    'courses/search' => 'CourseController@search',
    
    // Tutorial Routes
    'tutorials/show/{id}' => 'TutorialController@show',
    'tutorials/complete/{id}' => 'TutorialController@markComplete',
    
    // Exam Routes
    'exams' => 'ExamController@index',
    'exams/show/{id}' => 'ExamController@show',
    
    // Job Routes
    'jobs' => 'JobController@index',
    'jobs/show/{id}' => 'JobController@show',
    'jobs/apply/{id}' => 'JobController@apply',
    'jobs/search' => 'JobController@search',
    
    // Student Routes
    'student/dashboard' => 'StudentDashboardController@index',
    'student/courses' => 'StudentCourseController@index',
    'student/exams/take/{id}' => 'StudentExamController@take',
    'student/exams/submit/{id}' => 'StudentExamController@submit',
    'student/progress' => 'StudentProgressController@index',
    
    // Teacher Routes
    'teacher/dashboard' => 'TeacherDashboardController@index',
    'teacher/courses' => 'TeacherCourseController@index',
    'teacher/courses/create' => 'TeacherCourseController@create',
    'teacher/courses/edit/{id}' => 'TeacherCourseController@edit',
    'teacher/courses/delete/{id}' => 'TeacherCourseController@delete',
    'teacher/exams/index/{course_id}' => 'TeacherExamController@index',
    'teacher/exams/create/{course_id}' => 'TeacherExamController@create',
    'teacher/exams/show/{id}' => 'TeacherExamController@show',
    'teacher/questions/add/{exam_id}' => 'TeacherQuestionController@add',
    
    // Admin Routes
    'admin/dashboard' => 'AdminController@index',
    'admin/users' => 'AdminController@users',
    'admin/courses' => 'AdminController@courses',
    'admin/courses/approve/{id}' => 'AdminController@approveCourse',
    'admin/courses/reject/{id}' => 'AdminController@rejectCourse',
    'admin/jobs' => 'AdminController@jobs',
    'admin/payments' => 'AdminController@payments',
    'admin/settings' => 'AdminController@settings',
    
    // Profile Routes
    'profile' => 'ProfileController@index',
    'profile/edit' => 'ProfileController@edit',
    'profile/avatar' => 'ProfileController@uploadAvatar',
    
    // Application Routes
    'applications' => 'ApplicationController@index',
    'applications/show/{id}' => 'ApplicationController@show',
    'applications/withdraw/{id}' => 'ApplicationController@withdraw',
    
    // Certificate Routes
    'certificates' => 'CertificateController@index',
    'certificates/show/{id}' => 'CertificateController@show',
    'certificates/download/{id}' => 'CertificateController@download',
    'certificates/verify/{code}' => 'CertificateController@verify',
    
    // Payment Routes
    'payment/checkout/{course_id}' => 'PaymentController@checkout',
    'payment/process/{course_id}' => 'PaymentController@process',
    
    // Forum Routes
    'forum' => 'ForumController@index',
    'forum/show/{id}' => 'ForumController@show',
    'forum/create-post/{forum_id}' => 'ForumController@createPost',
    'forum/post/{id}' => 'ForumController@post',
    'forum/reply/{post_id}' => 'ForumController@reply',
    
    // Dashboard Route
    'dashboard' => 'DashboardController@index'
]);

// API Routes
define('API_ROUTES', [
    'v1/auth/login' => 'AuthApiController@login',
    'v1/auth/register' => 'AuthApiController@register',
    'v1/auth/logout' => 'AuthApiController@logout',
    'v1/auth/refresh' => 'AuthApiController@refresh',
    
    'v1/users' => 'UserApiController@index',
    'v1/users/{id}' => 'UserApiController@show',
    'v1/users/{id}/update' => 'UserApiController@update',
    
    'v1/courses' => 'CourseApiController@index',
    'v1/courses/{id}' => 'CourseApiController@show',
    'v1/courses/search' => 'CourseApiController@search',
    
    'v1/tutorials/{id}' => 'TutorialApiController@show',
    'v1/tutorials/{id}/progress' => 'TutorialApiController@updateProgress',
    
    'v1/exams' => 'ExamApiController@index',
    'v1/exams/{id}' => 'ExamApiController@show',
    'v1/exams/{id}/attempt' => 'ExamApiController@attempt',
    'v1/exams/{id}/submit' => 'ExamApiController@submit',
    
    'v1/jobs' => 'JobApiController@index',
    'v1/jobs/{id}' => 'JobApiController@show',
    'v1/jobs/search' => 'JobApiController@search',
    
    'v1/applications' => 'ApplicationApiController@index',
    'v1/applications/{id}' => 'ApplicationApiController@show',
    'v1/applications/create' => 'ApplicationApiController@create',
    
    'v1/payments' => 'PaymentApiController@index',
    'v1/payments/create' => 'PaymentApiController@create',
    'v1/payments/{id}/status' => 'PaymentApiController@status',
    
    'v1/notifications' => 'NotificationApiController@index',
    'v1/notifications/{id}/read' => 'NotificationApiController@markAsRead',
    
    'v1/analytics/dashboard' => 'AnalyticsApiController@dashboard',
    'v1/analytics/courses' => 'AnalyticsApiController@courses',
    'v1/analytics/users' => 'AnalyticsApiController@users'
]);

// Route Middleware
define('ROUTE_MIDDLEWARE', [
    'auth' => 'AuthMiddleware',
    'student' => 'StudentMiddleware',
    'teacher' => 'TeacherMiddleware',
    'admin' => 'AdminMiddleware',
    'cors' => 'CorsMiddleware',
    'rate_limit' => 'RateLimitMiddleware'
]);

// Protected Routes (require authentication)
define('PROTECTED_ROUTES', [
    'dashboard',
    'profile',
    'student/*',
    'teacher/*',
    'admin/*',
    'applications/*',
    'certificates/*',
    'payment/*'
]);
?>
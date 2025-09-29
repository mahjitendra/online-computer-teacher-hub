<?php
class CourseManagementController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->courseModel = $this->model('Course');
        $this->categoryModel = $this->model('CourseCategory');
    }

    public function index(){
        $courses = $this->courseModel->getAllCoursesForAdmin();
        $categories = $this->categoryModel->getAllCategories();
        
        $data = [
            'title' => 'Course Management',
            'courses' => $courses,
            'categories' => $categories
        ];

        $this->view('pages/admin/courses', $data);
    }

    public function approve($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->courseModel->updateCourseStatus($id, 'approved')){
                header('location: ' . URLROOT . '/admin/courses?success=approved');
            } else {
                header('location: ' . URLROOT . '/admin/courses?error=approve');
            }
        }
    }

    public function reject($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $reason = $_POST['reason'] ?? '';
            if($this->courseModel->rejectCourse($id, $reason)){
                header('location: ' . URLROOT . '/admin/courses?success=rejected');
            } else {
                header('location: ' . URLROOT . '/admin/courses?error=reject');
            }
        }
    }

    public function categories(){
        $categories = $this->categoryModel->getCategoriesWithCourseCount();
        
        $data = [
            'title' => 'Course Categories',
            'categories' => $categories
        ];

        $this->view('pages/admin/course-categories', $data);
    }

    public function createCategory(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $data = [
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description']),
                'icon' => trim($_POST['icon']),
                'color' => trim($_POST['color'])
            ];

            if($this->categoryModel->createCategory($data)){
                header('location: ' . URLROOT . '/admin/courses/categories?success=created');
            } else {
                header('location: ' . URLROOT . '/admin/courses/categories?error=create');
            }
        }
    }
}
?>
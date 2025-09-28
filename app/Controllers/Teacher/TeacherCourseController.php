<?php
class TeacherCourseController extends Controller {
    public function __construct(){
        // Here we would add middleware to ensure user is a logged-in teacher
        // For now, we will assume this is handled.
        $this->courseModel = $this->model('Course');
    }

    public function index(){
        // Get courses for the current teacher
        $courses = $this->courseModel->getCoursesByTeacher($_SESSION['user_id']);

        $data = [
            'courses' => $courses
        ];

        $this->view('pages/teacher/courses', $data);
    }

    public function create(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'teacher_id' => $_SESSION['user_id'],
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'category_id' => trim($_POST['category_id']),
                'price' => trim($_POST['price']),
                'title_err' => '',
                'description_err' => ''
            ];

            // Validate data
            if(empty($data['title'])){
                $data['title_err'] = 'Please enter title';
            }
            if(empty($data['description'])){
                $data['description_err'] = 'Please enter description';
            }

            // Make sure no errors
            if(empty($data['title_err']) && empty($data['description_err'])){
                // Validated
                if($this->courseModel->addCourse($data)){
                    header('location: ' . URLROOT . '/teacher/courses');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('pages/teacher/create_course', $data);
            }

        } else {
            $categories = $this->courseModel->getCategories();
            $data = [
                'title' => '',
                'description' => '',
                'categories' => $categories,
                'category_id' => '',
                'price' => ''
            ];

            $this->view('pages/teacher/create_course', $data);
        }
    }

    public function edit($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'id' => $id,
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'category_id' => trim($_POST['category_id']),
                'price' => trim($_POST['price']),
                'title_err' => '',
                'description_err' => ''
            ];

            // Validate data
            if(empty($data['title'])){
                $data['title_err'] = 'Please enter title';
            }
            if(empty($data['description'])){
                $data['description_err'] = 'Please enter description';
            }

            // Make sure no errors
            if(empty($data['title_err']) && empty($data['description_err'])){
                // Validated
                if($this->courseModel->updateCourse($data)){
                    header('location: ' . URLROOT . '/teacher/courses');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('pages/teacher/edit_course', $data);
            }

        } else {
            // Get existing course from model
            $course = $this->courseModel->getCourseById($id);
            $categories = $this->courseModel->getCategories();

            // Check for owner
            if($course->teacher_id != $_SESSION['user_id']){
                header('location: ' . URLROOT . '/teacher/courses');
            }

            $data = [
                'id' => $id,
                'title' => $course->title,
                'description' => $course->description,
                'categories' => $categories,
                'category_id' => $course->category_id,
                'price' => $course->price
            ];

            $this->view('pages/teacher/edit_course', $data);
        }
    }

    public function delete($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
             // Get existing course from model
             $course = $this->courseModel->getCourseById($id);

             // Check for owner
             if($course->teacher_id != $_SESSION['user_id']){
                header('location: ' . URLROOT . '/teacher/courses');
             }

            if($this->courseModel->deleteCourse($id)){
                header('location: ' . URLROOT . '/teacher/courses');
            } else {
                die('Something went wrong');
            }
        } else {
            header('location: ' . URLROOT . '/teacher/courses');
        }
    }
}
?>
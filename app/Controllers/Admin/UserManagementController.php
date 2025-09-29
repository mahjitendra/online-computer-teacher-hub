<?php
class UserManagementController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->userModel = $this->model('User');
        $this->studentModel = $this->model('Student');
        $this->teacherModel = $this->model('Teacher');
    }

    public function index(){
        $users = $this->userModel->getAllUsersWithProfiles();
        
        $data = [
            'title' => 'User Management',
            'users' => $users
        ];

        $this->view('pages/admin/users', $data);
    }

    public function students(){
        $students = $this->studentModel->getAllStudentsWithStats();
        
        $data = [
            'title' => 'Student Management',
            'students' => $students
        ];

        $this->view('pages/admin/students', $data);
    }

    public function teachers(){
        $teachers = $this->teacherModel->getAllTeachersWithStats();
        
        $data = [
            'title' => 'Teacher Management',
            'teachers' => $teachers
        ];

        $this->view('pages/admin/teachers', $data);
    }

    public function suspend($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->userModel->suspendUser($id)){
                header('location: ' . URLROOT . '/admin/users?success=suspended');
            } else {
                header('location: ' . URLROOT . '/admin/users?error=suspend');
            }
        }
    }

    public function activate($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->userModel->activateUser($id)){
                header('location: ' . URLROOT . '/admin/users?success=activated');
            } else {
                header('location: ' . URLROOT . '/admin/users?error=activate');
            }
        }
    }
}
?>
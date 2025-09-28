<?php
class ProfileController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id'])){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->userModel = $this->model('User');
        $this->profileModel = $this->model('UserProfile');
    }

    public function index(){
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        $profile = $this->profileModel->getProfileByUserId($_SESSION['user_id']);

        $data = [
            'user' => $user,
            'profile' => $profile
        ];

        $this->view('pages/student/profile', $data);
    }

    public function edit(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'user_id' => $_SESSION['user_id'],
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'phone' => trim($_POST['phone']),
                'address' => trim($_POST['address']),
                'bio' => trim($_POST['bio']),
                'skills' => trim($_POST['skills']),
                'experience' => trim($_POST['experience']),
                'education' => trim($_POST['education'])
            ];

            if($this->profileModel->updateProfile($data)){
                header('location: ' . URLROOT . '/profile?success=updated');
            } else {
                die('Something went wrong');
            }
        } else {
            $user = $this->userModel->getUserById($_SESSION['user_id']);
            $profile = $this->profileModel->getProfileByUserId($_SESSION['user_id']);

            $data = [
                'user' => $user,
                'profile' => $profile
            ];

            $this->view('pages/student/edit-profile', $data);
        }
    }

    public function uploadAvatar(){
        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])){
            $uploadDir = 'uploads/profiles/';
            $fileName = $_SESSION['user_id'] . '_' . time() . '.jpg';
            $uploadPath = $uploadDir . $fileName;

            if(move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)){
                $this->profileModel->updateAvatar($_SESSION['user_id'], $uploadPath);
                header('location: ' . URLROOT . '/profile?success=avatar');
            } else {
                header('location: ' . URLROOT . '/profile?error=upload');
            }
        }
    }
}
?>
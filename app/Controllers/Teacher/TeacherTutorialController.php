<?php
class TeacherTutorialController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->tutorialModel = $this->model('Tutorial');
        $this->courseModel = $this->model('Course');
        $this->videoService = new VideoProcessingService();
    }

    public function index($courseId){
        // Verify teacher owns the course
        $course = $this->courseModel->getCourseById($courseId);
        if($course->teacher_id != $_SESSION['user_id']){
            header('location: ' . URLROOT . '/teacher/courses');
            return;
        }

        $tutorials = $this->tutorialModel->getTutorialsByCourse($courseId);

        $data = [
            'course' => $course,
            'tutorials' => $tutorials
        ];

        $this->view('pages/teacher/tutorials', $data);
    }

    public function create($courseId){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $data = [
                'course_id' => $courseId,
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'video_url' => $this->handleVideoUpload(),
                'order_index' => intval($_POST['order_index']),
                'is_free' => isset($_POST['is_free']) ? 1 : 0,
                'content_type' => $_POST['content_type'] ?? 'video'
            ];

            if($this->tutorialModel->createTutorial($data)){
                header('location: ' . URLROOT . '/teacher/tutorials/index/' . $courseId);
            } else {
                die('Something went wrong');
            }
        } else {
            $course = $this->courseModel->getCourseById($courseId);
            if($course->teacher_id != $_SESSION['user_id']){
                header('location: ' . URLROOT . '/teacher/courses');
                return;
            }

            $data = [
                'course' => $course,
                'title' => '',
                'description' => '',
                'order_index' => 1
            ];

            $this->view('pages/teacher/create-tutorial', $data);
        }
    }

    private function handleVideoUpload(){
        if(isset($_FILES['video']) && $_FILES['video']['error'] == 0){
            $uploadDir = 'uploads/courses/videos/';
            $fileName = uniqid() . '_' . $_FILES['video']['name'];
            $uploadPath = $uploadDir . $fileName;

            if(move_uploaded_file($_FILES['video']['tmp_name'], $uploadPath)){
                // Process video in background
                $this->videoService->processVideo($uploadPath);
                return $uploadPath;
            }
        }
        return null;
    }
}
?>
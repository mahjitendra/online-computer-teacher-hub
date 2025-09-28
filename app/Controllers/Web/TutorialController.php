<?php
class TutorialController extends Controller {
    public function __construct(){
        $this->tutorialModel = $this->model('Tutorial');
        $this->courseModel = $this->model('Course');
        $this->enrollmentModel = $this->model('Enrollment');
    }

    public function show($id){
        if(!isset($_SESSION['user_id'])){
            header('location: ' . URLROOT . '/auth/login');
            return;
        }

        $tutorial = $this->tutorialModel->getTutorialById($id);
        $course = $this->courseModel->getCourseById($tutorial->course_id);
        
        // Check if user is enrolled
        $isEnrolled = $this->enrollmentModel->isStudentEnrolled($_SESSION['user_id'], $tutorial->course_id);
        
        if(!$isEnrolled){
            header('location: ' . URLROOT . '/courses/show/' . $tutorial->course_id);
            return;
        }

        $materials = $this->tutorialModel->getMaterialsByTutorial($id);
        $courseTutorials = $this->tutorialModel->getTutorialsByCourse($tutorial->course_id);

        $data = [
            'tutorial' => $tutorial,
            'course' => $course,
            'materials' => $materials,
            'courseTutorials' => $courseTutorials
        ];

        $this->view('pages/student/video-player', $data);
    }

    public function markComplete($id){
        if(!isset($_SESSION['user_id'])){
            header('location: ' . URLROOT . '/auth/login');
            return;
        }

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $tutorial = $this->tutorialModel->getTutorialById($id);
            
            // Mark tutorial as completed for this student
            $this->tutorialModel->markTutorialComplete($_SESSION['user_id'], $id);
            
            // Update course progress
            $this->enrollmentModel->updateProgress($_SESSION['user_id'], $tutorial->course_id);
            
            header('location: ' . URLROOT . '/tutorials/show/' . $id . '?completed=true');
        }
    }
}
?>
<?php
class CourseApiController extends ApiController {
    public function __construct(){
        $this->courseModel = $this->model('Course');
    }

    // GET /api/v1/courses
    public function index(){
        $courses = $this->courseModel->getAllCourses();
        $this->jsonResponse($courses);
    }

    // GET /api/v1/courses/show/{id}
    public function show($id){
        $course = $this->courseModel->getCourseById($id);
        if($course){
            $this->jsonResponse($course);
        } else {
            $this->jsonResponse(['message' => 'Course not found'], 404);
        }
    }
}
?>
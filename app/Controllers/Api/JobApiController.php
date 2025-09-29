<?php
class JobApiController extends ApiController {
    public function __construct(){
        $this->jobModel = $this->model('Job');
    }

    // GET /api/v1/jobs
    public function index(){
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 10;
        $category = $_GET['category'] ?? null;
        $location = $_GET['location'] ?? null;

        $jobs = $this->jobModel->getJobsWithFilters($page, $limit, $category, $location);
        $this->jsonResponse($jobs);
    }

    // GET /api/v1/jobs/{id}
    public function show($id){
        $job = $this->jobModel->getJobById($id);
        if($job){
            $this->jsonResponse($job);
        } else {
            $this->jsonResponse(['error' => 'Job not found'], 404);
        }
    }

    // GET /api/v1/jobs/search
    public function search(){
        $query = $_GET['q'] ?? '';
        $location = $_GET['location'] ?? '';
        $category = $_GET['category'] ?? '';

        if(empty($query)){
            $this->jsonResponse(['error' => 'Search query required'], 400);
        }

        $jobs = $this->jobModel->searchJobs($query, $location, $category);
        $this->jsonResponse($jobs);
    }
}
?>
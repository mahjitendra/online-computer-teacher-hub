<?php
class TutorialApiController extends ApiController {
    public function __construct(){
        $this->tutorialModel = $this->model('Tutorial');
    }

    // GET /api/v1/tutorials/{id}
    public function show($id){
        $tutorial = $this->tutorialModel->getTutorialById($id);
        if($tutorial){
            $materials = $this->tutorialModel->getMaterialsByTutorial($id);
            $tutorial->materials = $materials;
            $this->jsonResponse($tutorial);
        } else {
            $this->jsonResponse(['error' => 'Tutorial not found'], 404);
        }
    }

    // POST /api/v1/tutorials/{id}/progress
    public function updateProgress($id){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $studentId = $input['student_id'] ?? null;
        $progress = $input['progress_percentage'] ?? 0;

        if(!$studentId){
            $this->jsonResponse(['error' => 'Student ID required'], 400);
        }

        if($this->tutorialModel->updateTutorialProgress($studentId, $id, $progress)){
            $this->jsonResponse(['success' => true, 'message' => 'Progress updated']);
        } else {
            $this->jsonResponse(['error' => 'Failed to update progress'], 500);
        }
    }
}
?>
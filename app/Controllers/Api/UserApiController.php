<?php
class UserApiController extends ApiController {
    public function __construct(){
        $this->userModel = $this->model('User');
        $this->profileModel = $this->model('UserProfile');
    }

    // GET /api/v1/users
    public function index(){
        $users = $this->userModel->getAllUsers();
        $this->jsonResponse($users);
    }

    // GET /api/v1/users/{id}
    public function show($id){
        $user = $this->userModel->getUserById($id);
        if($user){
            $profile = $this->profileModel->getProfileByUserId($id);
            $user->profile = $profile;
            $this->jsonResponse($user);
        } else {
            $this->jsonResponse(['error' => 'User not found'], 404);
        }
    }

    // PUT /api/v1/users/{id}/update
    public function update($id){
        if($_SERVER['REQUEST_METHOD'] !== 'PUT'){
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        $data = [
            'user_id' => $id,
            'first_name' => $input['first_name'] ?? '',
            'last_name' => $input['last_name'] ?? '',
            'phone' => $input['phone'] ?? '',
            'address' => $input['address'] ?? '',
            'bio' => $input['bio'] ?? ''
        ];

        if($this->profileModel->updateProfile($data)){
            $this->jsonResponse(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            $this->jsonResponse(['error' => 'Update failed'], 500);
        }
    }
}
?>
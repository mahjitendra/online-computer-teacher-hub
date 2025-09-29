<?php
class SystemSettingsController extends Controller {
    public function __construct(){
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin'){
            header('location: ' . URLROOT . '/auth/login');
            exit();
        }
        $this->settingModel = $this->model('Setting');
        $this->backupService = new BackupService();
    }

    public function index(){
        $settings = $this->settingModel->getAllSettings();
        
        $data = [
            'title' => 'System Settings',
            'settings' => $settings
        ];

        $this->view('pages/admin/settings', $data);
    }

    public function update(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            foreach($_POST as $key => $value){
                if($key !== 'submit'){
                    $this->settingModel->updateSetting($key, $value);
                }
            }
            
            header('location: ' . URLROOT . '/admin/settings?success=updated');
        }
    }

    public function backup(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $backupFile = $this->backupService->createBackup();
            if($backupFile){
                header('location: ' . URLROOT . '/admin/settings?success=backup&file=' . $backupFile);
            } else {
                header('location: ' . URLROOT . '/admin/settings?error=backup');
            }
        }
    }

    public function maintenance(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $mode = $_POST['mode'] ?? 'off';
            $this->settingModel->updateSetting('maintenance_mode', $mode);
            
            header('location: ' . URLROOT . '/admin/settings?success=maintenance');
        }
    }
}
?>
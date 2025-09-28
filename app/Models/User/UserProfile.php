<?php
class UserProfile {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getProfileByUserId($user_id){
        $this->db->query('SELECT * FROM user_profiles WHERE user_id = :user_id');
        $this->db->bind(':user_id', $user_id);
        return $this->db->single();
    }

    public function createProfile($data){
        $this->db->query('INSERT INTO user_profiles (
                         user_id, first_name, last_name, phone, address, 
                         bio, skills, experience, education, avatar
                         ) VALUES (
                         :user_id, :first_name, :last_name, :phone, :address,
                         :bio, :skills, :experience, :education, :avatar
                         )');
        
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':bio', $data['bio']);
        $this->db->bind(':skills', $data['skills']);
        $this->db->bind(':experience', $data['experience']);
        $this->db->bind(':education', $data['education']);
        $this->db->bind(':avatar', $data['avatar'] ?? null);

        return $this->db->execute();
    }

    public function updateProfile($data){
        $this->db->query('UPDATE user_profiles SET 
                         first_name = :first_name,
                         last_name = :last_name,
                         phone = :phone,
                         address = :address,
                         bio = :bio,
                         skills = :skills,
                         experience = :experience,
                         education = :education,
                         updated_at = NOW()
                         WHERE user_id = :user_id');
        
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':bio', $data['bio']);
        $this->db->bind(':skills', $data['skills']);
        $this->db->bind(':experience', $data['experience']);
        $this->db->bind(':education', $data['education']);

        return $this->db->execute();
    }

    public function updateAvatar($user_id, $avatar_path){
        $this->db->query('UPDATE user_profiles SET avatar = :avatar WHERE user_id = :user_id');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':avatar', $avatar_path);
        return $this->db->execute();
    }

    public function getProfileWithUser($user_id){
        $this->db->query('SELECT u.*, p.* FROM users u 
                         LEFT JOIN user_profiles p ON u.id = p.user_id 
                         WHERE u.id = :user_id');
        $this->db->bind(':user_id', $user_id);
        return $this->db->single();
    }

    public function updateSocialLinks($user_id, $social_data){
        $this->db->query('UPDATE user_profiles SET 
                         linkedin = :linkedin,
                         github = :github,
                         twitter = :twitter,
                         website = :website,
                         updated_at = NOW()
                         WHERE user_id = :user_id');
        
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':linkedin', $social_data['linkedin']);
        $this->db->bind(':github', $social_data['github']);
        $this->db->bind(':twitter', $social_data['twitter']);
        $this->db->bind(':website', $social_data['website']);

        return $this->db->execute();
    }

    public function updatePreferences($user_id, $preferences){
        $this->db->query('UPDATE user_profiles SET 
                         email_notifications = :email_notifications,
                         sms_notifications = :sms_notifications,
                         job_alerts = :job_alerts,
                         course_updates = :course_updates,
                         updated_at = NOW()
                         WHERE user_id = :user_id');
        
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':email_notifications', $preferences['email_notifications']);
        $this->db->bind(':sms_notifications', $preferences['sms_notifications']);
        $this->db->bind(':job_alerts', $preferences['job_alerts']);
        $this->db->bind(':course_updates', $preferences['course_updates']);

        return $this->db->execute();
    }
}
?>
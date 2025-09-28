<?php
class TutorialVideo {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getVideoById($id){
        $this->db->query('SELECT * FROM tutorial_videos WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getVideosByTutorial($tutorial_id){
        $this->db->query('SELECT * FROM tutorial_videos 
                         WHERE tutorial_id = :tutorial_id 
                         ORDER BY order_index ASC');
        $this->db->bind(':tutorial_id', $tutorial_id);
        return $this->db->resultSet();
    }

    public function createVideo($data){
        $this->db->query('INSERT INTO tutorial_videos (
                         tutorial_id, title, video_url, video_path, duration,
                         file_size, resolution, format, order_index
                         ) VALUES (
                         :tutorial_id, :title, :video_url, :video_path, :duration,
                         :file_size, :resolution, :format, :order_index
                         )');
        
        $this->db->bind(':tutorial_id', $data['tutorial_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':video_url', $data['video_url']);
        $this->db->bind(':video_path', $data['video_path']);
        $this->db->bind(':duration', $data['duration']);
        $this->db->bind(':file_size', $data['file_size']);
        $this->db->bind(':resolution', $data['resolution']);
        $this->db->bind(':format', $data['format']);
        $this->db->bind(':order_index', $data['order_index']);

        if($this->db->execute()){
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateVideo($data){
        $this->db->query('UPDATE tutorial_videos SET 
                         title = :title,
                         video_url = :video_url,
                         video_path = :video_path,
                         duration = :duration,
                         file_size = :file_size,
                         resolution = :resolution,
                         format = :format,
                         order_index = :order_index,
                         updated_at = NOW()
                         WHERE id = :id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':video_url', $data['video_url']);
        $this->db->bind(':video_path', $data['video_path']);
        $this->db->bind(':duration', $data['duration']);
        $this->db->bind(':file_size', $data['file_size']);
        $this->db->bind(':resolution', $data['resolution']);
        $this->db->bind(':format', $data['format']);
        $this->db->bind(':order_index', $data['order_index']);

        return $this->db->execute();
    }

    public function deleteVideo($id){
        $this->db->query('DELETE FROM tutorial_videos WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function updateVideoStatus($id, $status){
        $this->db->query('UPDATE tutorial_videos SET 
                         processing_status = :status,
                         updated_at = NOW()
                         WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        return $this->db->execute();
    }

    public function getVideoQualities($video_id){
        $this->db->query('SELECT * FROM video_qualities 
                         WHERE video_id = :video_id 
                         ORDER BY quality_level DESC');
        $this->db->bind(':video_id', $video_id);
        return $this->db->resultSet();
    }

    public function addVideoQuality($data){
        $this->db->query('INSERT INTO video_qualities (
                         video_id, quality_level, file_path, file_size, bitrate
                         ) VALUES (
                         :video_id, :quality_level, :file_path, :file_size, :bitrate
                         )');
        
        $this->db->bind(':video_id', $data['video_id']);
        $this->db->bind(':quality_level', $data['quality_level']);
        $this->db->bind(':file_path', $data['file_path']);
        $this->db->bind(':file_size', $data['file_size']);
        $this->db->bind(':bitrate', $data['bitrate']);

        return $this->db->execute();
    }

    public function getVideoAnalytics($video_id){
        $this->db->query('SELECT 
                         COUNT(DISTINCT vv.student_id) as unique_viewers,
                         COUNT(vv.id) as total_views,
                         AVG(vv.watch_duration) as avg_watch_duration,
                         SUM(vv.watch_duration) as total_watch_time
                         FROM video_views vv
                         WHERE vv.video_id = :video_id');
        $this->db->bind(':video_id', $video_id);
        return $this->db->single();
    }

    public function recordVideoView($data){
        $this->db->query('INSERT INTO video_views (
                         video_id, student_id, watch_duration, completion_percentage, device_type
                         ) VALUES (
                         :video_id, :student_id, :watch_duration, :completion_percentage, :device_type
                         )');
        
        $this->db->bind(':video_id', $data['video_id']);
        $this->db->bind(':student_id', $data['student_id']);
        $this->db->bind(':watch_duration', $data['watch_duration']);
        $this->db->bind(':completion_percentage', $data['completion_percentage']);
        $this->db->bind(':device_type', $data['device_type']);

        return $this->db->execute();
    }
}
?>
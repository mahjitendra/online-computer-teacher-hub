<?php
class Message extends BaseModel {
    protected $table = 'messages';
    protected $fillable = ['sender_id', 'receiver_id', 'subject', 'message', 'is_read', 'message_type'];

    public function sendMessage($data){
        $data['is_read'] = 0;
        $data['message_type'] = $data['message_type'] ?? 'private';
        return $this->create($data);
    }

    public function getInboxMessages($userId, $limit = 50){
        $this->db->query('SELECT m.*, u.name as sender_name, u.email as sender_email
                         FROM messages m
                         JOIN users u ON m.sender_id = u.id
                         WHERE m.receiver_id = :user_id
                         ORDER BY m.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getSentMessages($userId, $limit = 50){
        $this->db->query('SELECT m.*, u.name as receiver_name, u.email as receiver_email
                         FROM messages m
                         JOIN users u ON m.receiver_id = u.id
                         WHERE m.sender_id = :user_id
                         ORDER BY m.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getUnreadCount($userId){
        $this->db->query('SELECT COUNT(*) as count FROM messages 
                         WHERE receiver_id = :user_id AND is_read = 0');
        $this->db->bind(':user_id', $userId);
        $result = $this->db->single();
        return $result ? $result->count : 0;
    }

    public function markAsRead($messageId){
        return $this->update($messageId, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
    }

    public function getConversation($userId1, $userId2, $limit = 50){
        $this->db->query('SELECT m.*, 
                         sender.name as sender_name,
                         receiver.name as receiver_name
                         FROM messages m
                         JOIN users sender ON m.sender_id = sender.id
                         JOIN users receiver ON m.receiver_id = receiver.id
                         WHERE (m.sender_id = :user1 AND m.receiver_id = :user2)
                         OR (m.sender_id = :user2 AND m.receiver_id = :user1)
                         ORDER BY m.created_at ASC
                         LIMIT :limit');
        $this->db->bind(':user1', $userId1);
        $this->db->bind(':user2', $userId2);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function deleteMessage($messageId, $userId){
        // Soft delete - mark as deleted for the user
        $message = $this->find($messageId);
        if(!$message){
            return false;
        }

        if($message->sender_id == $userId){
            return $this->update($messageId, ['deleted_by_sender' => 1]);
        } elseif($message->receiver_id == $userId){
            return $this->update($messageId, ['deleted_by_receiver' => 1]);
        }

        return false;
    }

    public function getMessageStats($userId = null){
        $whereClause = $userId ? 'WHERE sender_id = :user_id OR receiver_id = :user_id' : '';
        
        $this->db->query("SELECT 
                         COUNT(*) as total_messages,
                         SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread_messages,
                         COUNT(DISTINCT sender_id) as unique_senders,
                         COUNT(DISTINCT receiver_id) as unique_receivers
                         FROM messages {$whereClause}");
        
        if($userId){
            $this->db->bind(':user_id', $userId);
        }
        
        return $this->db->single();
    }

    public function searchMessages($userId, $searchTerm, $limit = 50){
        $this->db->query('SELECT m.*, 
                         sender.name as sender_name,
                         receiver.name as receiver_name
                         FROM messages m
                         JOIN users sender ON m.sender_id = sender.id
                         JOIN users receiver ON m.receiver_id = receiver.id
                         WHERE (m.sender_id = :user_id OR m.receiver_id = :user_id)
                         AND (m.subject LIKE :search OR m.message LIKE :search)
                         ORDER BY m.created_at DESC
                         LIMIT :limit');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':search', '%' . $searchTerm . '%');
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getRecentContacts($userId, $limit = 10){
        $this->db->query('SELECT DISTINCT
                         CASE 
                             WHEN m.sender_id = :user_id THEN m.receiver_id
                             ELSE m.sender_id
                         END as contact_id,
                         CASE 
                             WHEN m.sender_id = :user_id THEN receiver.name
                             ELSE sender.name
                         END as contact_name,
                         MAX(m.created_at) as last_message_date
                         FROM messages m
                         JOIN users sender ON m.sender_id = sender.id
                         JOIN users receiver ON m.receiver_id = receiver.id
                         WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
                         GROUP BY contact_id, contact_name
                         ORDER BY last_message_date DESC
                         LIMIT :limit');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function sendBulkMessage($senderIds, $receiverIds, $subject, $message){
        $sent = 0;
        
        foreach($senderIds as $senderId){
            foreach($receiverIds as $receiverId){
                if($senderId != $receiverId){
                    $data = [
                        'sender_id' => $senderId,
                        'receiver_id' => $receiverId,
                        'subject' => $subject,
                        'message' => $message,
                        'message_type' => 'bulk'
                    ];
                    
                    if($this->sendMessage($data)){
                        $sent++;
                    }
                }
            }
        }
        
        return $sent;
    }
}
?>
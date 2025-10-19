<?php
class Message {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO messages (sender_id, receiver_id, property_id, subject, message) 
                VALUES (?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [
            $data['sender_id'],
            $data['receiver_id'],
            $data['property_id'] ?? null,
            $data['subject'] ?? '',
            $data['message']
        ]) ? $this->db->lastInsertId() : false;
    }

    public function getConversation($user1, $user2, $propertyId = null) {
        $sql = "SELECT m.*, 
                sender.first_name as sender_first_name, 
                sender.last_name as sender_last_name
                FROM messages m
                LEFT JOIN users sender ON m.sender_id = sender.id
                WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR 
                      (m.sender_id = ? AND m.receiver_id = ?))";
        
        $params = [$user1, $user2, $user2, $user1];

        if ($propertyId) {
            $sql .= " AND m.property_id = ?";
            $params[] = $propertyId;
        }

        $sql .= " ORDER BY m.created_at ASC";

        $stmt = $this->db->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function getRecentConversations($userId) {
        $sql = "SELECT DISTINCT 
                CASE 
                    WHEN m.sender_id = ? THEN m.receiver_id 
                    ELSE m.sender_id 
                END as other_user_id,
                u.first_name, u.last_name, u.user_type,
                MAX(m.created_at) as last_message_time,
                (SELECT message FROM messages m2 
                 WHERE ((m2.sender_id = ? AND m2.receiver_id = other_user_id) OR 
                       (m2.sender_id = other_user_id AND m2.receiver_id = ?))
                 ORDER BY m2.created_at DESC LIMIT 1) as last_message
                FROM messages m
                LEFT JOIN users u ON u.id = CASE 
                    WHEN m.sender_id = ? THEN m.receiver_id 
                    ELSE m.sender_id 
                END
                WHERE m.sender_id = ? OR m.receiver_id = ?
                GROUP BY other_user_id
                ORDER BY last_message_time DESC";

        $stmt = $this->db->query($sql, [$userId, $userId, $userId, $userId, $userId, $userId]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function markAsRead($messageId) {
        $sql = "UPDATE messages SET is_read = 1 WHERE id = ?";
        return $this->db->query($sql, [$messageId]);
    }

    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0";
        $stmt = $this->db->query($sql, [$userId]);
        $result = $stmt ? $stmt->fetch() : ['count' => 0];
        return $result['count'];
    }
}
?>
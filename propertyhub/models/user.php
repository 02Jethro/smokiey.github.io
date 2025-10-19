<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt ? $stmt->fetch() : false;
    }

    public function getByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->query($sql, [$email]);
        return $stmt ? $stmt->fetch() : false;
    }

    public function getAll($type = null) {
        if ($type) {
            $sql = "SELECT * FROM users WHERE user_type = ? ORDER BY created_at DESC";
            $stmt = $this->db->query($sql, [$type]);
        } else {
            $sql = "SELECT * FROM users ORDER BY created_at DESC";
            $stmt = $this->db->query($sql);
        }
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function update($id, $data) {
        $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?";
        return $this->db->query($sql, [
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function countByType($type) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE user_type = ?";
        $stmt = $this->db->query($sql, [$type]);
        $result = $stmt ? $stmt->fetch() : ['count' => 0];
        return $result['count'];
    }
}
?>
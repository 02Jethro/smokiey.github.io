<?php
class Maintenance {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO maintenance_requests (property_id, tenant_id, title, description, priority) 
                VALUES (?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [
            $data['property_id'],
            $data['tenant_id'],
            $data['title'],
            $data['description'],
            $data['priority']
        ]) ? $this->db->lastInsertId() : false;
    }

    public function getByTenant($tenantId) {
        $sql = "SELECT mr.*, p.title as property_title, p.address as property_address
                FROM maintenance_requests mr
                LEFT JOIN properties p ON mr.property_id = p.id
                WHERE mr.tenant_id = ?
                ORDER BY mr.created_at DESC";
        
        $stmt = $this->db->query($sql, [$tenantId]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function getByLandlord($landlordId) {
        $sql = "SELECT mr.*, p.title as property_title, p.address as property_address,
                t.first_name as tenant_first_name, t.last_name as tenant_last_name
                FROM maintenance_requests mr
                LEFT JOIN properties p ON mr.property_id = p.id
                LEFT JOIN users t ON mr.tenant_id = t.id
                WHERE p.owner_id = ?
                ORDER BY mr.created_at DESC";
        
        $stmt = $this->db->query($sql, [$landlordId]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function updateStatus($id, $status, $assignedTo = null) {
        $sql = "UPDATE maintenance_requests SET status = ?, assigned_to = ? WHERE id = ?";
        return $this->db->query($sql, [$status, $assignedTo, $id]);
    }

    public function getById($id) {
        $sql = "SELECT mr.*, p.title as property_title, p.address as property_address,
                t.first_name as tenant_first_name, t.last_name as tenant_last_name
                FROM maintenance_requests mr
                LEFT JOIN properties p ON mr.property_id = p.id
                LEFT JOIN users t ON mr.tenant_id = t.id
                WHERE mr.id = ?";
        
        $stmt = $this->db->query($sql, [$id]);
        return $stmt ? $stmt->fetch() : false;
    }

    public function getStats($landlordId) {
        $sql = "SELECT 
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_requests,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_requests,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_requests
                FROM maintenance_requests mr
                LEFT JOIN properties p ON mr.property_id = p.id
                WHERE p.owner_id = ?";
        
        $stmt = $this->db->query($sql, [$landlordId]);
        return $stmt ? $stmt->fetch() : [
            'total_requests' => 0,
            'pending_requests' => 0,
            'in_progress_requests' => 0,
            'completed_requests' => 0
        ];
    }
}
?>
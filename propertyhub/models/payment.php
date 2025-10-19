<?php
class Payment {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO payments (tenant_id, landlord_id, property_id, amount, payment_type, 
                payment_method, payment_gateway, gateway_reference, status, due_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [
            $data['tenant_id'],
            $data['landlord_id'],
            $data['property_id'],
            $data['amount'],
            $data['payment_type'],
            $data['payment_method'],
            $data['payment_gateway'] ?? null,
            $data['gateway_reference'] ?? null,
            $data['status'],
            $data['due_date']
        ]) ? $this->db->lastInsertId() : false;
    }

    public function getAllPayments($filters = []) {
    $sql = "SELECT p.*, 
            tenant.first_name as tenant_first_name, tenant.last_name as tenant_last_name,
            landlord.first_name as landlord_first_name, landlord.last_name as landlord_last_name,
            prop.title as property_title
            FROM payments p
            LEFT JOIN users tenant ON p.tenant_id = tenant.id
            LEFT JOIN users landlord ON p.landlord_id = landlord.id
            LEFT JOIN properties prop ON p.property_id = prop.id
            WHERE 1=1";
    
    $params = [];

    if (!empty($filters['status'])) {
        $sql .= " AND p.status = ?";
        $params[] = $filters['status'];
    }

    if (!empty($filters['payment_type'])) {
        $sql .= " AND p.payment_type = ?";
        $params[] = $filters['payment_type'];
    }

    if (!empty($filters['date_from'])) {
        $sql .= " AND DATE(p.created_at) >= ?";
        $params[] = $filters['date_from'];
    }

    if (!empty($filters['date_to'])) {
        $sql .= " AND DATE(p.created_at) <= ?";
        $params[] = $filters['date_to'];
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $this->db->query($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

     public function getTenantProperties($tenantId) {
        $sql = "SELECT p.*, t.rent_amount, t.start_date, t.end_date 
                FROM properties p
                INNER JOIN tenancies t ON p.id = t.property_id
                WHERE t.tenant_id = ? AND t.status = 'active'
                ORDER BY p.created_at DESC";
        $stmt = $this->db->query($sql, [$tenantId]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function updateStatus($id, $status, $gatewayReference = null, $transactionId = null) {
        $sql = "UPDATE payments SET status = ?, gateway_reference = ?, transaction_id = ?, 
                payment_date = NOW() WHERE id = ?";
        
        return $this->db->query($sql, [
            $status,
            $gatewayReference,
            $transactionId,
            $id
        ]);
    }

    public function getByGatewayReference($reference) {
        $sql = "SELECT * FROM payments WHERE gateway_reference = ?";
        $stmt = $this->db->query($sql, [$reference]);
        return $stmt ? $stmt->fetch() : false;
    }

    public function getByUser($userId, $userType) {
        if ($userType == 'tenant') {
            $sql = "SELECT p.*, prop.title as property_title 
                    FROM payments p
                    LEFT JOIN properties prop ON p.property_id = prop.id
                    WHERE p.tenant_id = ?
                    ORDER BY p.created_at DESC";
        } else {
            $sql = "SELECT p.*, prop.title as property_title 
                    FROM payments p
                    LEFT JOIN properties prop ON p.property_id = prop.id
                    WHERE p.landlord_id = ?
                    ORDER BY p.created_at DESC";
        }

        $stmt = $this->db->query($sql, [$userId]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function getPending($userId, $userType) {
        if ($userType == 'tenant') {
            $sql = "SELECT * FROM payments WHERE tenant_id = ? AND status = 'pending'";
        } else {
            $sql = "SELECT * FROM payments WHERE landlord_id = ? AND status = 'pending'";
        }

        $stmt = $this->db->query($sql, [$userId]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function getById($id) {
        $sql = "SELECT * FROM payments WHERE id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt ? $stmt->fetch() : false;
    }
}
?>
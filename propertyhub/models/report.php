<?php
class Report {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getPropertyPerformance($ownerId = null) {
        $sql = "SELECT 
                p.title,
                p.price,
                p.status,
                COUNT(DISTINCT t.id) as total_tenants,
                COUNT(DISTINCT mr.id) as maintenance_requests,
                SUM(CASE WHEN pm.status = 'completed' THEN pm.amount ELSE 0 END) as total_income
                FROM properties p
                LEFT JOIN tenancies t ON p.id = t.property_id AND t.status = 'active'
                LEFT JOIN maintenance_requests mr ON p.id = mr.property_id
                LEFT JOIN payments pm ON p.id = pm.property_id";
        
        $params = [];
        if ($ownerId) {
            $sql .= " WHERE p.owner_id = ?";
            $params[] = $ownerId;
        }

        $sql .= " GROUP BY p.id";

        $stmt = $this->db->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function getFinancialReport($userId, $userType, $startDate, $endDate) {
        if ($userType == 'landlord') {
            $sql = "SELECT 
                    p.title as property_title,
                    pm.amount,
                    pm.payment_type,
                    pm.payment_date,
                    pm.status,
                    t.first_name as tenant_first_name,
                    t.last_name as tenant_last_name
                    FROM payments pm
                    LEFT JOIN properties p ON pm.property_id = p.id
                    LEFT JOIN users t ON pm.tenant_id = t.id
                    WHERE pm.landlord_id = ? AND pm.payment_date BETWEEN ? AND ?
                    ORDER BY pm.payment_date DESC";
        } else {
            $sql = "SELECT 
                    p.title as property_title,
                    pm.amount,
                    pm.payment_type,
                    pm.payment_date,
                    pm.status,
                    l.first_name as landlord_first_name,
                    l.last_name as landlord_last_name
                    FROM payments pm
                    LEFT JOIN properties p ON pm.property_id = p.id
                    LEFT JOIN users l ON pm.landlord_id = l.id
                    WHERE pm.tenant_id = ? AND pm.payment_date BETWEEN ? AND ?
                    ORDER BY pm.payment_date DESC";
        }

        $stmt = $this->db->query($sql, [$userId, $startDate, $endDate]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function getUserStats() {
        $sql = "SELECT 
                user_type,
                COUNT(*) as count,
                DATE(created_at) as date
                FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY user_type, DATE(created_at)
                ORDER BY date DESC";

        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function getRevenueStats($ownerId = null) {
        $sql = "SELECT 
                DATE(payment_date) as date,
                SUM(amount) as daily_revenue,
                COUNT(*) as transaction_count
                FROM payments 
                WHERE status = 'completed' AND payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $params = [];
        if ($ownerId) {
            $sql .= " AND landlord_id = ?";
            $params[] = $ownerId;
        }

        $sql .= " GROUP BY DATE(payment_date) ORDER BY date DESC";

        $stmt = $this->db->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }
}
?>
<?php
class Property {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll($filters = []) {
        $sql = "SELECT p.*, u.first_name, u.last_name 
                FROM properties p 
                LEFT JOIN users u ON p.owner_id = u.id 
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND p.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['province'])) {
            $sql .= " AND p.state = ?";
            $params[] = $filters['province'];
        }

        if (!empty($filters['city'])) {
            $sql .= " AND p.city LIKE ?";
            $params[] = "%{$filters['city']}%";
        }

        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }

        if (!empty($filters['bedrooms'])) {
            $sql .= " AND p.bedrooms >= ?";
            $params[] = $filters['bedrooms'];
        }

        $sql .= " ORDER BY p.created_at DESC";

        
        $stmt = $this->db->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function getById($id) {
        $sql = "SELECT p.*, u.first_name as owner_first_name, u.last_name as owner_last_name, 
                u.phone as owner_phone, u.email as owner_email
                FROM properties p 
                LEFT JOIN users u ON p.owner_id = u.id 
                WHERE p.id = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt ? $stmt->fetch() : false;
    }

    public function create($data) {
        $sql = "INSERT INTO properties (title, description, type, price, address, city, state, zip_code, 
                bedrooms, bathrooms, area_sqft, owner_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [
            $data['title'],
            $data['description'],
            $data['type'],
            $data['price'],
            $data['address'],
            $data['city'],
            $data['state'],
            $data['zip_code'],
            $data['bedrooms'] ?? 0,
            $data['bathrooms'] ?? 0,
            $data['area_sqft'] ?? 0,
            $data['owner_id'],
            $data['status'] ?? 'available'
        ]) ? $this->db->lastInsertId() : false;
    }

    public function update($id, $data) {
        $sql = "UPDATE properties SET title = ?, description = ?, type = ?, price = ?, address = ?, 
                city = ?, state = ?, zip_code = ?, bedrooms = ?, bathrooms = ?, area_sqft = ?, 
                status = ? WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['title'],
            $data['description'],
            $data['type'],
            $data['price'],
            $data['address'],
            $data['city'],
            $data['state'],
            $data['zip_code'],
            $data['bedrooms'] ?? 0,
            $data['bathrooms'] ?? 0,
            $data['area_sqft'] ?? 0,
            $data['status'],
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM properties WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getByOwner($ownerId) {
        $sql = "SELECT * FROM properties WHERE owner_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, [$ownerId]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function search($term) {
        $sql = "SELECT p.*, u.first_name, u.last_name 
                FROM properties p 
                LEFT JOIN users u ON p.owner_id = u.id 
                WHERE p.title LIKE ? OR p.description LIKE ? OR p.city LIKE ? OR p.address LIKE ? OR p.state LIKE ?
                ORDER BY p.created_at DESC";
        
        $searchTerm = "%{$term}%";
        $stmt = $this->db->query($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function getByProvince($province) {
        $sql = "SELECT p.*, u.first_name, u.last_name 
                FROM properties p 
                LEFT JOIN users u ON p.owner_id = u.id 
                WHERE p.state = ?
                ORDER BY p.created_at DESC";
        $stmt = $this->db->query($sql, [$province]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function getPropertyCountByProvince() {
        $sql = "SELECT state, COUNT(*) as count FROM properties GROUP BY state ORDER BY count DESC";
        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function getPropertyImages($propertyId) {
        $sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, created_at ASC";
        $stmt = $this->db->query($sql, [$propertyId]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function addPropertyImage($propertyId, $imageUrl, $isPrimary = false) {
        // If setting as primary, first unset any existing primary
        if ($isPrimary) {
            $this->db->query("UPDATE property_images SET is_primary = 0 WHERE property_id = ?", [$propertyId]);
        }
        
        $sql = "INSERT INTO property_images (property_id, image_url, is_primary) VALUES (?, ?, ?)";
        return $this->db->query($sql, [$propertyId, $imageUrl, $isPrimary ? 1 : 0]);
    }

    public function getByTenant($tenantId) {
        $sql = "SELECT p.*, t.rent_amount, t.start_date, t.end_date 
                FROM properties p 
                JOIN tenancies t ON p.id = t.property_id 
                WHERE t.tenant_id = ? AND t.status = 'active' 
                ORDER BY p.created_at DESC";
        $stmt = $this->db->query($sql, [$tenantId]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function getTenantInfo($propertyId) {
        $sql = "SELECT u.*, t.rent_amount, t.start_date, t.end_date 
                FROM tenancies t 
                JOIN users u ON t.tenant_id = u.id 
                WHERE t.property_id = ? AND t.status = 'active' 
                LIMIT 1";
        $stmt = $this->db->query($sql, [$propertyId]);
        return $stmt ? $stmt->fetch() : null;
    }

    public function getBuyerInfo($propertyId) {
        $sql = "SELECT u.*, ps.sale_price, ps.sale_date 
                FROM property_sales ps 
                JOIN users u ON ps.buyer_id = u.id 
                WHERE ps.property_id = ? 
                LIMIT 1";
        $stmt = $this->db->query($sql, [$propertyId]);
        return $stmt ? $stmt->fetch() : null;
    }

        public function getImages($propertyId) {
        $sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, created_at ASC";
        $stmt = $this->db->query($sql, [$propertyId]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    

    public function getActiveTenantsCount($propertyId) {
        $sql = "SELECT COUNT(*) as count FROM tenancies WHERE property_id = ? AND status = 'active'";
        $stmt = $this->db->query($sql, [$propertyId]);
        $result = $stmt ? $stmt->fetch() : ['count' => 0];
        return $result['count'];
    }

    public function getPropertyOwner($propertyId) {
        $sql = "SELECT u.* FROM users u 
                INNER JOIN properties p ON u.id = p.owner_id 
                WHERE p.id = ?";
        $stmt = $this->db->query($sql, [$propertyId]);
        return $stmt ? $stmt->fetch() : false;
    }

    public function getPropertyTenant($propertyId) {
        $sql = "SELECT u.*, t.start_date, t.end_date, t.rent_amount 
                FROM users u 
                INNER JOIN tenancies t ON u.id = t.tenant_id 
                WHERE t.property_id = ? AND t.status = 'active' 
                LIMIT 1";
        $stmt = $this->db->query($sql, [$propertyId]);
        return $stmt ? $stmt->fetch() : false;
    }
}
?>

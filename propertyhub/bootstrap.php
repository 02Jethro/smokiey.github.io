<?php
// Simple bootstrap file
require_once 'config.php';

// Test database connection
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
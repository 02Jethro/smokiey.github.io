<?php
require_once 'bootstrap.php';

echo "Testing Property model...<br>";

try {
    $propertyModel = new Property();
    echo "Property model created successfully!<br>";
    
    $properties = $propertyModel->getAll([], 3);
    echo "Found " . count($properties) . " properties<br>";
    
    foreach ($properties as $property) {
        echo "Property: " . $property['title'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
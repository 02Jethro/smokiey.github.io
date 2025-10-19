<?php
require_once '../config.php';
require_once '../core/Validator.php';
require_once '../models/Property.php';

class PropertyImageController {
    private $validator;
    private $propertyModel;

    public function __construct() {
        $this->validator = new Validator();
        $this->propertyModel = new Property();
    }

    public function uploadImages() {
        require_auth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['property_images'])) {
            $propertyId = $_POST['property_id'];
            
            // Verify property ownership
            $property = $this->propertyModel->getById($propertyId);
            if (!$property || $property['owner_id'] != $_SESSION['user_id']) {
                $_SESSION['error'] = 'You do not have permission to upload images for this property.';
                redirect('views/properties/manage.php');
            }
            
            $uploadedFiles = $_FILES['property_images'];
            $uploadCount = 0;
            
            // Check current image count
            $currentImages = $this->propertyModel->getImages($propertyId);
            if (count($currentImages) + count($uploadedFiles['name']) > 4) {
                $_SESSION['error'] = 'Maximum 4 images allowed per property.';
                redirect('views/properties/edit.php?id=' . $propertyId);
            }
            
            foreach ($uploadedFiles['name'] as $key => $name) {
                if ($uploadedFiles['error'][$key] === UPLOAD_ERR_OK) {
                    $fileTmpName = $uploadedFiles['tmp_name'][$key];
                    $fileSize = $uploadedFiles['size'][$key];
                    $fileType = $uploadedFiles['type'][$key];
                    
                    // Validate file
                    if ($this->validateImage($fileTmpName, $fileSize, $fileType)) {
                        $imageUrl = $this->saveImage($fileTmpName, $propertyId, $name);
                        
                        if ($imageUrl) {
                            $isPrimary = ($uploadCount === 0 && empty($currentImages));
                            $this->propertyModel->addImage($propertyId, $imageUrl, $isPrimary);
                            $uploadCount++;
                        }
                    }
                }
            }
            
            if ($uploadCount > 0) {
                $_SESSION['success'] = "Successfully uploaded {$uploadCount} images.";
            } else {
                $_SESSION['error'] = 'No images were uploaded. Please check file formats and sizes.';
            }
            
            redirect('views/properties/edit.php?id=' . $propertyId);
        }
    }

    private function validateImage($filePath, $fileSize, $fileType) {
        // Check file size (max 5MB)
        if ($fileSize > 5242880) {
            return false;
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($fileType, $allowedTypes)) {
            return false;
        }
        
        // Check if it's actually an image
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }
        
        return true;
    }

    private function saveImage($tmpFilePath, $propertyId, $originalName) {
        $uploadDir = UPLOAD_PATH . "properties/{$propertyId}/";
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($tmpFilePath, $filePath)) {
            // Return web-accessible URL
            return str_replace(ROOT_DIR, BASE_URL, $filePath);
        }
        
        return false;
    }

    public function setPrimaryImage() {
        require_auth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $propertyId = $_POST['property_id'];
            $imageId = $_POST['image_id'];
            
            // Verify ownership
            $property = $this->propertyModel->getById($propertyId);
            if (!$property || $property['owner_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permission denied']);
                exit;
            }
            
            if ($this->propertyModel->updatePrimaryImage($propertyId, $imageId)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to update primary image']);
            }
        }
    }

    public function deleteImage() {
        require_auth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $propertyId = $_POST['property_id'];
            $imageId = $_POST['image_id'];
            
            // Verify ownership
            $property = $this->propertyModel->getById($propertyId);
            if (!$property || $property['owner_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Permission denied']);
                exit;
            }
            
            // Get image info before deletion
            $images = $this->propertyModel->getImages($propertyId);
            $imageToDelete = null;
            $isPrimary = false;
            
            foreach ($images as $image) {
                if ($image['id'] == $imageId) {
                    $imageToDelete = $image;
                    $isPrimary = $image['is_primary'];
                    break;
                }
            }
            
            if ($imageToDelete && $this->propertyModel->deleteImage($imageId)) {
                // Delete physical file
                $filePath = str_replace(BASE_URL, ROOT_DIR, $imageToDelete['image_url']);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // If deleted image was primary, set a new primary
                if ($isPrimary) {
                    $remainingImages = $this->propertyModel->getImages($propertyId);
                    if (!empty($remainingImages)) {
                        $this->propertyModel->updatePrimaryImage($propertyId, $remainingImages[0]['id']);
                    }
                }
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to delete image']);
            }
        }
    }
}

// Handle requests
if (isset($_POST['action'])) {
    $controller = new PropertyImageController();
    
    switch ($_POST['action']) {
        case 'upload_images':
            $controller->uploadImages();
            break;
        case 'set_primary_image':
            $controller->setPrimaryImage();
            break;
        case 'delete_image':
            $controller->deleteImage();
            break;
    }
}
?>
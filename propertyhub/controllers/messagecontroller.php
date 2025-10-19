<?php
require_once '../config.php';
require_once '../models/Message.php';
require_once '../models/User.php';

class MessageController {
    private $messageModel;
    private $userModel;

    public function __construct() {
        $this->messageModel = new Message();
        $this->userModel = new User();
    }

    public function send() {
        require_auth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'sender_id' => $_SESSION['user_id'],
                'receiver_id' => $_POST['receiver_id'],
                'property_id' => $_POST['property_id'] ?? null,
                'subject' => $_POST['subject'] ?? '',
                'message' => trim($_POST['message'])
            ];

            if (empty($data['message'])) {
                $_SESSION['error'] = 'Message cannot be empty.';
                redirect('views/messages/inbox.php');
            }

            if ($this->messageModel->create($data)) {
                $_SESSION['success'] = 'Message sent successfully!';
            } else {
                $_SESSION['error'] = 'Failed to send message.';
            }

            redirect('views/messages/inbox.php');
        }
    }

    public function getConversation() {
        require_auth();
        
        if (isset($_GET['receiver_id'])) {
            $receiverId = $_GET['receiver_id'];
            $propertyId = $_GET['property_id'] ?? null;
            
            $conversation = $this->messageModel->getConversation(
                $_SESSION['user_id'], 
                $receiverId, 
                $propertyId
            );

            // Mark messages as read
            foreach ($conversation as $message) {
                if ($message['receiver_id'] == $_SESSION['user_id'] && !$message['is_read']) {
                    $this->messageModel->markAsRead($message['id']);
                }
            }

            header('Content-Type: application/json');
            echo json_encode($conversation);
            exit;
        }
    }
}

// Handle requests
if (isset($_POST['action'])) {
    $controller = new MessageController();
    
    switch ($_POST['action']) {
        case 'send_message':
            $controller->send();
            break;
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    $controller = new MessageController();
    
    switch ($_GET['action']) {
        case 'get_conversation':
            $controller->getConversation();
            break;
    }
}
?>
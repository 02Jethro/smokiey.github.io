<?php
require_once '../../config.php';
require_auth();

require_once '../../models/User.php';
require_once '../../models/Property.php';

$userModel = new User();
$propertyModel = new Property();

$users = $userModel->getAll();
$properties = $propertyModel->getAll();

// Pre-fill form if parameters are provided
$receiverId = $_GET['receiver_id'] ?? '';
$propertyId = $_GET['property_id'] ?? '';

$page_title = "Compose Message";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>Compose Message</h1>
            <p>Send a message to other users</p>
        </div>

        <div class="compose-message">
            <form action="<?php echo BASE_URL; ?>controllers/MessageController.php" method="POST">
                <input type="hidden" name="action" value="send_message">
                
                <div class="form-group">
                    <label>To:</label>
                    <select name="receiver_id" required>
                        <option value="">Select Recipient</option>
                        <?php foreach ($users as $user): ?>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <option value="<?php echo $user['id']; ?>" <?php echo $user['id'] == $receiverId ? 'selected' : ''; ?>>
                                    <?php echo $user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['user_type'] . ')'; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Regarding Property (Optional):</label>
                    <select name="property_id">
                        <option value="">No specific property</option>
                        <?php foreach ($properties as $property): ?>
                            <option value="<?php echo $property['id']; ?>" <?php echo $property['id'] == $propertyId ? 'selected' : ''; ?>>
                                <?php echo $property['title']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Subject (Optional):</label>
                    <input type="text" name="subject" placeholder="Message subject">
                </div>

                <div class="form-group">
                    <label>Message:</label>
                    <textarea name="message" rows="10" required placeholder="Type your message here..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Send Message</button>
                    <a href="<?php echo view_url('messages/inbox.php'); ?>" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <style>
    .compose-message {
        background: #fff;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-width: 800px;
        margin: 0 auto;
    }

    .compose-message .form-group {
        margin-bottom: 1.5rem;
    }

    .compose-message label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #2c3e50;
    }

    .compose-message select,
    .compose-message input,
    .compose-message textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1rem;
    }

    .compose-message textarea {
        resize: vertical;
        min-height: 200px;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .compose-message {
            padding: 1rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
    }
    </style>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
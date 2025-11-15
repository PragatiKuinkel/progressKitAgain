<?php
global $dbh;
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/dbconnection.php';

// Fetch existing messages
try {
    $stmt = $dbh->prepare("
        SELECT m.*, u.full_name, u.role as sender_role
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        ORDER BY m.created_at DESC
    ");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching messages: " . $e->getMessage());
    $messages = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Progress Kit</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Main Layout */
        body {
            height: 100vh;
            overflow: hidden;
            margin: 0;
            display: flex;
            background-color: var(--light-bg);
        }

        .sidebar {
            height: 100vh;
            overflow-y: auto;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* Chat Container */
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--white);
            margin: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Messages Area */
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column-reverse;
        }

        .message {
            max-width: 80%;
            margin-bottom: 16px;
            padding: 12px 16px;
            border-radius: 12px;
            background: var(--light-bg);
            position: relative;
        }

        .message.admin {
            background: var(--primary-color);
            color: white;
            margin-left: auto;
        }

        .message.user {
            background: var(--white);
            border: 1px solid var(--border-color);
            margin-right: auto;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            font-size: 0.85em;
        }

        .sender-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sender-name {
            font-weight: 600;
        }

        .sender-role {
            font-size: 0.8em;
            padding: 2px 6px;
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.1);
        }

        .message-time {
            color: var(--text-muted);
            font-size: 0.8em;
        }

        .message-content {
            word-break: break-word;
            line-height: 1.5;
        }

        /* Input Area */
        .input-area {
            padding: 16px;
            border-top: 1px solid var(--border-color);
            background: var(--white);
        }

        .message-form {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .message-input {
            flex: 1;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1em;
            resize: none;
            min-height: 24px;
            max-height: 120px;
            transition: border-color 0.3s;
        }

        .message-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .send-button {
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .send-button:hover {
            background: var(--primary-hover);
        }

        /* Scrollbar Styling */
        .messages-area::-webkit-scrollbar {
            width: 6px;
        }

        .messages-area::-webkit-scrollbar-track {
            background: transparent;
        }

        .messages-area::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 3px;
        }

        .messages-area::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .message {
                max-width: 90%;
            }

            .message-form {
                flex-direction: column;
            }

            .send-button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="light-mode">
<!-- Sidebar -->
<div class="sidebar">
    <?php include 'includes/sidebar.php'; ?>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Messages Content -->
    <div class="content-wrapper">
        <div class="content-header">
            <h1>Messages</h1>
        </div>

        <div class="chat-container">
            <div class="messages-area" id="messagesContainer">
                <?php foreach ($messages as $message): ?>
                    <div class="message <?php echo $message['sender_role'] === 'admin' ? 'admin' : 'user'; ?>">
                        <div class="message-header">
                            <div class="sender-info">
                                <span class="sender-name"><?php echo htmlspecialchars($message['full_name']); ?></span>
                                <span class="sender-role"><?php echo ucfirst($message['sender_role']); ?></span>
                            </div>
                            <span class="message-time"><?php echo date('g:i A, M j', strtotime($message['created_at'])); ?></span>
                        </div>
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="input-area">
                <form class="message-form" id="messageForm">
                        <textarea
                                id="messageInput"
                                class="message-input"
                                placeholder="Type your message..."
                                rows="1"
                                name="message"
                                required
                        ></textarea>
                    <button type="submit" class="send-button">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const messageForm = document.getElementById('messageForm');
        const messageInput = document.getElementById('messageInput');
        const messagesContainer = document.getElementById('messagesContainer');

        messageForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const messageText = messageInput.value.trim();
            if (!messageText) return;

            const formData = new FormData();
            formData.append('message', messageText);

            fetch('send_message.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Create a new message element
                        const newMessage = document.createElement('div');
                        newMessage.classList.add('message', 'admin'); // Assuming the sender is an admin
                        newMessage.innerHTML = `
                        <div class="message-header">
                            <div class="sender-info">
                                <span class="sender-name"><?=$_SESSION['full_name']?></span>
                                <span class="sender-role"><?=$_SESSION['role']?></span>
                            </div>
                            <span class="message-time">${new Date().toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        })}</span>
                        </div>
                        <div class="message-content">${messageText.replace(/\n/g, '<br>')}</div>
                    `;

                        // Append the new message to the container
                        messagesContainer.prepend(newMessage);

                        // Clear the input field
                        messageInput.value = '';
                    } else {
                        alert('Failed to send the message. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while sending the message.');
                });
        });

        function fetchMessages() {
            fetch('fetch_messages.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messagesContainer.innerHTML = ''; // Clear existing messages
                        data.messages.forEach(message => {
                            const messageElement = document.createElement('div');
                            messageElement.classList.add('message', message.sender_role === 'admin' ? 'admin' : 'user');
                            messageElement.innerHTML = `
                            <div class="message-header">
                                <div class="sender-info">
                                    <span class="sender-name">${message.full_name}</span>
                                    <span class="sender-role">${message.sender_role.charAt(0).toUpperCase() + message.sender_role.slice(1)}</span>
                                </div>
                                <span class="message-time">${new Date(message.created_at).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            })}</span>
                            </div>
                            <div class="message-content">${message.message.replace(/\n/g, '<br>')}</div>
                        `;
                            messagesContainer.appendChild(messageElement);
                        });
                    } else {
                        console.error('Failed to fetch messages:', data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Fetch messages every 5 seconds
        setInterval(fetchMessages, 5000);

        // Initial fetch
        fetchMessages();

    });
</script>
</body>
</html>
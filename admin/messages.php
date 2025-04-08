<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/dbconnection.php';

// Get recent messages
try {
    $stmt = $dbh->prepare("
        SELECT m.*, u.full_name, u.role 
        FROM messages m 
        JOIN users u ON m.user_id = u.id 
        ORDER BY m.created_at DESC 
        LIMIT 50
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
    <title>Messages - EventPro Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            height: 100vh;
            overflow: hidden;
            margin: 0;
            display: flex;
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

        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .content-header {
            padding: 20px;
            background: var(--white);
            border-bottom: 1px solid var(--border-color);
        }

        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--white);
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column-reverse;
        }

        .message {
            display: flex;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            background: var(--light-bg);
        }

        .message.admin {
            background: var(--primary-color);
            color: white;
        }

        .message-content {
            flex: 1;
            margin-left: 15px;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.9em;
        }

        .username {
            font-weight: bold;
        }

        .timestamp {
            color: var(--text-muted);
        }

        .message-text {
            word-break: break-word;
        }

        .input-area {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            background: var(--white);
        }

        .message-form {
            display: flex;
            gap: 10px;
        }

        .message-input {
            flex: 1;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1em;
        }

        .send-button {
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .send-button:hover {
            background: var(--primary-hover);
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Custom scrollbar */
        .messages-area::-webkit-scrollbar {
            width: 8px;
        }

        .messages-area::-webkit-scrollbar-track {
            background: var(--light-bg);
        }

        .messages-area::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        .messages-area::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
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
        <div class="content-wrapper">
            <div class="content-header">
                <h1>Messages</h1>
            </div>

            <div class="chat-container">
                <div class="messages-area" id="messagesArea">
                    <?php foreach ($messages as $message): ?>
                    <div class="message <?php echo $message['role'] === 'admin' ? 'admin' : ''; ?>">
                        <div class="message-avatar">
                            <?php echo strtoupper(substr($message['full_name'], 0, 1)); ?>
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="username"><?php echo htmlspecialchars($message['full_name']); ?></span>
                                <span class="timestamp"><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></span>
                            </div>
                            <div class="message-text"><?php echo htmlspecialchars($message['message']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="input-area">
                    <form class="message-form" id="messageForm">
                        <input type="text" class="message-input" id="messageInput" placeholder="Type your message..." required>
                        <button type="submit" class="send-button">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const userId = <?php echo $_SESSION['user_id']; ?>;
            const username = "<?php echo $_SESSION['full_name']; ?>";
            const role = "<?php echo $_SESSION['role']; ?>";
            
            // WebSocket connection
            const ws = new WebSocket('ws://localhost:8080');
            
            ws.onopen = function() {
                console.log('Connected to WebSocket server');
            };
            
            ws.onmessage = function(event) {
                const message = JSON.parse(event.data);
                appendMessage(message);
            };
            
            ws.onerror = function(error) {
                console.error('WebSocket error:', error);
            };
            
            ws.onclose = function() {
                console.log('Disconnected from WebSocket server');
            };
            
            // Handle message submission
            $('#messageForm').on('submit', function(e) {
                e.preventDefault();
                const messageInput = $('#messageInput');
                const message = messageInput.val().trim();
                
                if (message) {
                    const data = {
                        type: 'message',
                        user_id: userId,
                        message: message
                    };
                    
                    ws.send(JSON.stringify(data));
                    messageInput.val('');
                }
            });
            
            // Handle Enter key
            $('#messageInput').on('keypress', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    $('#messageForm').submit();
                }
            });
            
            function appendMessage(message) {
                const isAdmin = message.role === 'admin';
                const messageHtml = `
                    <div class="message ${isAdmin ? 'admin' : ''}">
                        <div class="message-avatar">
                            ${message.username.charAt(0).toUpperCase()}
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="username">${message.username}</span>
                                <span class="timestamp">${formatTimestamp(message.timestamp)}</span>
                            </div>
                            <div class="message-text">${message.message}</div>
                        </div>
                    </div>
                `;
                
                $('#messagesArea').prepend(messageHtml);
                $('#messagesArea').scrollTop(0);
            }
            
            function formatTimestamp(timestamp) {
                const date = new Date(timestamp);
                return date.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit'
                });
            }
        });
    </script>
</body>
</html> 
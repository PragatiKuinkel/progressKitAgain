<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->db = new PDO(
            "mysql:host=localhost;dbname=event_management",
            "root",
            "",
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
        );
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if ($data['type'] === 'message') {
            // Store message in database
            $stmt = $this->db->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
            $stmt->execute([$data['user_id'], htmlspecialchars($data['message'])]);
            
            // Get user info
            $stmt = $this->db->prepare("SELECT full_name, role FROM users WHERE id = ?");
            $stmt->execute([$data['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Prepare message for broadcast
            $message = [
                'type' => 'message',
                'user_id' => $data['user_id'],
                'username' => $user['full_name'],
                'role' => $user['role'],
                'message' => htmlspecialchars($data['message']),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // Broadcast to all clients
            foreach ($this->clients as $client) {
                $client->send(json_encode($message));
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080
);

$server->run(); 
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/dbconnection.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected $dbh;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->dbh = new PDO("mysql:host=localhost;dbname=event_management;charset=utf8", "root", "");
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg, true);
            
            if (isset($data['message']) && isset($data['sender_role']) && isset($data['sender_id'])) {
                // Store message in database
                $stmt = $this->dbh->prepare("INSERT INTO messages (sender_role, sender_id, message) VALUES (?, ?, ?)");
                $stmt->execute([$data['sender_role'], $data['sender_id'], $data['message']]);
                
                // Broadcast to all clients
                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        'message' => $data['message'],
                        'sender_role' => $data['sender_role'],
                        'sender_id' => $data['sender_id'],
                        'created_at' => date('Y-m-d H:i:s')
                    ]));
                }
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
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
            new ChatServer()
        )
    ),
    8080
);

$server->run(); 
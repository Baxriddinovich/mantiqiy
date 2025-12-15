<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Auth required']);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
if ($action === 'send') {
    $msg = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (!empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $current_user_id, $msg);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Database error']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Message empty']);
    }
} elseif ($action === 'fetch') {
    $sql = "SELECT m.id, m.user_id, m.message, m.created_at, u.username 
            FROM chat_messages m 
            JOIN users u ON m.user_id = u.id 
            ORDER BY m.id DESC LIMIT 50";

    $result = $conn->query($sql);

    $messages = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = [
                'id' => $row['id'],
                'message' => htmlspecialchars($row['message']),
                'username' => htmlspecialchars($row['username']),
                'time' => date('H:i', strtotime($row['created_at'])), // Soat:minut
                'is_me' => ($row['user_id'] == $current_user_id)
            ];
        }
        echo json_encode(['status' => 'success', 'data' => array_reverse($messages)]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Fetch error']);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid action']);
}
?>
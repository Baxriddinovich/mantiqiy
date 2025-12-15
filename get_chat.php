<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    exit('[]');
}

$sql = "SELECT cm.message, cm.created_at, cm.user_id, u.username 
        FROM chat_messages cm 
        JOIN users u ON cm.user_id = u.id 
        ORDER BY cm.created_at ASC LIMIT 500"; 

$result = $conn->query($sql);

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'message' => htmlspecialchars($row['message']),
        'username' => htmlspecialchars($row['username']),
        'user_id' => $row['user_id'],
        'time' => date('H:i', strtotime($row['created_at']))
    ];
}

header('Content-Type: application/json');
echo json_encode($messages);
?>
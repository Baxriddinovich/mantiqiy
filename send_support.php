<?php
session_start();
require_once 'db.php';
if(isset($_POST['message']) && isset($_SESSION['user_id'])) {
    $msg = $_POST['message'];
    $uid = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO support_messages (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $uid, $msg);
    $stmt->execute();
    header("Location: index.php?msg=sent");
}
?>
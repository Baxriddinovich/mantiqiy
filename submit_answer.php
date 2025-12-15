<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Ruxsat yo\'q']);
    exit;
}
$user_id = $_SESSION['user_id'];
$question_id = intval($_POST['question_id']);
$arena_id = intval($_POST['arena_id']); 
$user_answer = trim($_POST['answer']);
if (empty($user_answer)) {
    echo json_encode(['success' => false, 'message' => 'Javob yozilmadi']);
    exit;
}
$check = $conn->prepare("SELECT id FROM user_answers WHERE user_id = ? AND question_id = ? AND arena_id = ?");
$check->bind_param("iii", $user_id, $question_id, $arena_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Siz bu savolga javob bergansiz']);
    exit;
}
$q_stmt = $conn->prepare("SELECT answer FROM questions WHERE id = ?");
$q_stmt->bind_param("i", $question_id);
$q_stmt->execute();
$q_result = $q_stmt->get_result()->fetch_assoc();
if (!$q_result) {
    echo json_encode(['success' => false, 'message' => 'Savol topilmadi']);
    exit;
}
$correct_answer_db = mb_strtolower(trim($q_result['answer']));
$user_answer_lower = mb_strtolower($user_answer);
$is_correct = ($correct_answer_db == $user_answer_lower) ? 1 : 0;
$insert = $conn->prepare("INSERT INTO user_answers (user_id, arena_id, question_id, user_answer, is_correct) VALUES (?, ?, ?, ?, ?)");
$insert->bind_param("iiisi", $user_id, $arena_id, $question_id, $user_answer, $is_correct);
if ($insert->execute()) {
    echo json_encode([
        'success' => true,
        'is_correct' => (bool)$is_correct
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $conn->error]);
}
?>
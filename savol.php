<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$firstname = $_SESSION['firstname']; 
$sql = "SELECT * FROM questions ORDER BY id ASC";
$result = $conn->query($sql);
$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}
$total_questions = count($questions);
$current_index = isset($_GET['q']) ? (int)$_GET['q'] : 0;
if ($current_index < 0) $current_index = 0;
if ($current_index >= $total_questions) $current_index = $total_questions - 1;

$current_question = $questions[$current_index];
$q_id = $current_question['id'];

$stmt_check = $conn->prepare("SELECT * FROM user_answers WHERE user_id = ? AND question_id = ?");
$stmt_check->bind_param("ii", $user_id, $q_id);
$stmt_check->execute();
$existing_answer = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

$is_solved = ($existing_answer && $existing_answer['is_correct']);
$user_input_val = $existing_answer ? htmlspecialchars($existing_answer['user_answer']) : '';

$feedback_class = "";
$feedback_msg = "";
$shake_animation = "";
$show_confetti = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['answer'])) {
    $user_answer = trim(strtolower($_POST['answer']));
    $real_answer = trim(strtolower($current_question['answer']));
    
    $is_correct = ($user_answer === $real_answer) ? 1 : 0;

    if ($is_correct && !$is_solved) {
        $conn->query("UPDATE users SET coins = coins + 5 WHERE id = $user_id");
        $show_confetti = true;
    }

    $stmt = $conn->prepare("INSERT INTO user_answers (user_id, question_id, user_answer, is_correct) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE user_answer=VALUES(user_answer), is_correct=VALUES(is_correct)");
    $stmt->bind_param("iisi", $user_id, $q_id, $user_answer, $is_correct);
    $stmt->execute();
    
    if ($is_correct) {
        $is_solved = true;
        $feedback_class = "success-glow";
        $feedback_msg = "To'g'ri Javob! (+5 Tanga)";
        $user_input_val = $user_answer;
    } else {
        $feedback_class = "error-glow";
        $feedback_msg = "Noto'g'ri! Qayta urinib ko'ring.";
        $shake_animation = "shake";
        $user_input_val = $user_answer;
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bosqich <?php echo $current_index + 1; ?> - LogicMaster</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        :root {
            --bg-gradient: linear-gradient(301deg, #0f0c29, #302b63, #24243e);
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --neon-blue: #00f2ff;
            --neon-purple: #bc13fe;
            --neon-green: #0aff00;
            --neon-red: #ff0055;
            --text-white: #ffffff;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient);
            background-size: 200% 200%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            color: var(--text-white);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            overflow-x: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            text-decoration: none;
            color: rgba(255,255,255,0.7);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            border-radius: 20px;
            background: var(--glass);
            border: 1px solid transparent;
            transition: 0.3s;
            z-index: 10;
        }
        .back-btn:hover {
            border-color: var(--neon-blue);
            color: var(--neon-blue);
            background: rgba(0, 242, 255, 0.1);
        }

        .game-card {
            width: 90%;
            max-width: 550px;
            background: rgba(15, 12, 41, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            border: 1px solid var(--glass-border);
            padding: 40px 30px;
            box-shadow: 0 0 40px rgba(0,0,0,0.6);
            text-align: center;
            position: relative;
            transition: 0.3s;
        }

        .level-info {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #aaa;
            margin-bottom: 8px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .progress-track {
            width: 100%;
            height: 6px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin-bottom: 25px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--neon-purple), var(--neon-blue));
            width: <?php echo (($current_index + 1) / $total_questions) * 100; ?>%;
            box-shadow: 0 0 10px var(--neon-purple);
            transition: width 0.5s ease;
        }

        .question-image-box {
            width: 100%;
            max-height: 250px;
            overflow: hidden;
            border-radius: 15px;
            margin-bottom: 20px;
            border: 1px solid var(--glass-border);
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(0,0,0,0.3);
        }
        .question-image-box img {
            max-width: 100%;
            max-height: 250px;
            object-fit: contain;
        }

        h3 {
            font-family: 'Orbitron', sans-serif;
            font-size: 24px;
            margin: 0 0 15px 0;
            color: var(--neon-blue);
        }

        p.question-text {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
            color: #e0e0e0;
        }

        .hint-box {
            margin-bottom: 20px;
        }
        .hint-btn {
            background: transparent;
            border: 1px dashed var(--neon-purple);
            color: var(--neon-purple);
            padding: 5px 15px;
            border-radius: 15px;
            cursor: pointer;
            font-size: 12px;
            transition: 0.3s;
        }
        .hint-btn:hover {
            background: rgba(188, 19, 254, 0.1);
        }
        .hint-text {
            display: none;
            margin-top: 10px;
            font-size: 13px;
            color: #ff9f43;
            background: rgba(255, 159, 67, 0.1);
            padding: 8px;
            border-radius: 8px;
        }

        .input-group { position: relative; margin-bottom: 20px; }
        input[type="text"] {
            width: 70%;
            padding: 12px 20px;
            font-size: 18px;
            background: rgba(255,255,255,0.05);
            border: 2px solid var(--glass-border);
            border-radius: 50px;
            color: white;
            outline: none;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            transition: 0.3s;
        }
        input[type="text"]:focus {
            border-color: var(--neon-blue);
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.2);
        }

        .btn-submit {
            padding: 12px 35px;
            border-radius: 50px;
            border: none;
            background: linear-gradient(90deg, var(--neon-purple), var(--neon-blue));
            color: white;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 0 15px rgba(188, 19, 254, 0.4);
        }
        .btn-submit:hover { transform: scale(1.05); }

        .nav-buttons {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .btn-nav {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.6);
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            transition: 0.3s;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-nav:hover {
            border-color: white;
            color: white;
            background: rgba(255,255,255,0.05);
        }

        .btn-next-highlight {
            border-color: var(--neon-green);
            color: var(--neon-green);
            box-shadow: 0 0 15px rgba(10, 255, 0, 0.1);
        }
        .btn-next-highlight:hover {
            background: var(--neon-green);
            color: black;
            box-shadow: 0 0 20px rgba(10, 255, 0, 0.4);
        }

        .success-glow { border-color: var(--neon-green); box-shadow: 0 0 30px rgba(10,255,0,0.2); }
        .error-glow { border-color: var(--neon-red); box-shadow: 0 0 30px rgba(255,0,85,0.2); }
        .msg-success { color: var(--neon-green); margin-top: 10px; font-weight: bold; }
        .msg-error { color: var(--neon-red); margin-top: 10px; font-weight: bold; }

        .shake { animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }
        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        @media (max-width: 600px) {
            .game-card { padding: 30px 20px; }
            input[type="text"] { width: 90%; }
        }
    </style>
</head>
<body>

    <a href="dashboard.php" class="back-btn"><i class="fas fa-home"></i> Bosh sahifa</a>

    <div class="game-card <?php echo $feedback_class . ' ' . $shake_animation; ?>">
        <div class="level-info">
            <span>LEVEL <?php echo $current_index + 1; ?></span>
            <span><?php echo $total_questions; ?> TA DAN</span>
        </div>
        <div class="progress-track">
            <div class="progress-fill"></div>
        </div>

        <?php if (!empty($current_question['image_path']) && file_exists($current_question['image_path'])): ?>
            <div class="question-image-box">
                <img src="<?php echo htmlspecialchars($current_question['image_path']); ?>" alt="Savol rasmi">
            </div>
        <?php endif; ?>

        <h3><i class="fas fa-brain"></i> Mantiqiy Savol</h3>
        <p class="question-text">
            <?php echo nl2br(htmlspecialchars($current_question['question'])); ?>
        </p>

        <?php if (!empty($current_question['hint'])): ?>
            <div class="hint-box">
                <button type="button" class="hint-btn" onclick="toggleHint()">
                    <i class="far fa-lightbulb"></i> Yordam olish
                </button>
                <div class="hint-text" id="hintText">
                    <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($current_question['hint']); ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="input-group">
                <input type="text" name="answer" 
                       value="<?php echo $user_input_val; ?>" 
                       placeholder="Javobni yozing..." 
                       autocomplete="off" required
                       <?php echo $is_solved ? 'readonly style="border-color:var(--neon-green); color:var(--neon-green);"' : ''; ?>>
            </div>

            <?php if (!$is_solved): ?>
                <button type="submit" class="btn-submit">TEKSHIRISH</button>
            <?php else: ?>
                <div class="msg-success"><i class="fas fa-check-circle"></i> Javob To'g'ri!</div>
            <?php endif; ?>

            <?php if(!empty($feedback_msg) && !$is_solved && $_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                <div class="msg-error"><i class="fas fa-times-circle"></i> <?php echo $feedback_msg; ?></div>
            <?php endif; ?>
        </form>
        <div class="nav-buttons">
            <?php if($current_index > 0): ?>
                <a href="savol.php?q=<?php echo $current_index - 1; ?>" class="btn-nav">
                    <i class="fas fa-arrow-left"></i> Oldingi
                </a>
            <?php endif; ?>

            <?php 
            if($current_index < $total_questions - 1): 
                $next_class = $is_solved ? "btn-nav btn-next-highlight" : "btn-nav";
            ?>
                <a href="savol.php?q=<?php echo $current_index + 1; ?>" class="<?php echo $next_class; ?>">
                    Keyingi <i class="fas fa-arrow-right"></i>
                </a>
            <?php elseif ($is_solved): ?>
                <a href="index.php" class="btn-nav btn-next-highlight">
                    Tugatish <i class="fas fa-flag"></i>
                </a>
            <?php endif; ?>
        </div>

    </div>

    <script>
        function toggleHint() {
            var x = document.getElementById("hintText");
            if (x.style.display === "block") {
                x.style.display = "none";
            } else {
                x.style.display = "block";
            }
        }
        <?php if($show_confetti): ?>
            confetti({
                particleCount: 150,
                spread: 70,
                origin: { y: 0.6 },
                colors: ['#00f2ff', '#bc13fe', '#0aff00']
            });
        <?php endif; ?>
    </script>

</body>
</html>
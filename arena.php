<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$arena_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM arenas WHERE id = ? AND status = 'active'");
$stmt->bind_param("i", $arena_id);
$stmt->execute();
$arena = $stmt->get_result()->fetch_assoc();

if (!$arena) {
    echo "<div style='color:white; text-align:center; margin-top:50px; font-family:sans-serif;'>
            <h1>🚫 Arena topilmadi yoki faol emas!</h1>
            <a href='dashboard.php' style='color:#00f2ff'>Bosh sahifaga qaytish</a>
          </div>";
    exit;
}
$q_sql = "SELECT 
            q.id, 
            q.question,    
            aq.points,      
            (SELECT ua.user_answer 
             FROM user_answers ua 
             WHERE ua.question_id = q.id 
               AND ua.user_id = ? 
               AND ua.arena_id = ?) as my_answer,
            
            -- Javob to'g'rimi?
            (SELECT ua.is_correct 
             FROM user_answers ua 
             WHERE ua.question_id = q.id 
               AND ua.user_id = ? 
               AND ua.arena_id = ?) as is_correct

          FROM questions q
          JOIN arena_questions aq ON q.id = aq.question_id
          WHERE aq.arena_id = ?
          ORDER BY aq.id ASC";
$q_stmt = $conn->prepare($q_sql);

if (!$q_stmt) {
    die("SQL Xatolik: " . $conn->error);
}
$q_stmt->bind_param("iiiii", $user_id, $arena_id, $user_id, $arena_id, $arena_id);
$q_stmt->execute();
$questions = $q_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($arena['title']); ?> - O'yin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Poppins:wght@300;400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            --neon-blue: #00f2ff;
            --neon-green: #0aff00;
            --glass: rgba(255, 255, 255, 0.05);
        }

        body {
            background: var(--bg-gradient);
            color: white;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        .header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .arena-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 20px;
            color: var(--neon-blue);
        }

        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .q-card {
            background: var(--glass);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            position: relative;
            transition: 0.3s;
        }

        .q-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
            color: #aaa;
        }

        .q-points {
            color: var(--neon-green);
            font-weight: bold;
        }

        .q-text {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .input-group {
            position: relative;
            display: flex;
            gap: 10px;
        }

        .answer-input {
            flex: 1;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #444;
            background: rgba(0, 0, 0, 0.3);
            color: white;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: 0.3s;
        }

        .answer-input:focus {
            border-color: var(--neon-blue);
            box-shadow: 0 0 10px rgba(0, 242, 255, 0.2);
        }

        .submit-btn {
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(90deg, #00c6ff, #0072ff);
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .submit-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(0, 114, 255, 0.5);
        }

        .answered-correct {
            border-color: var(--neon-green);
            background: rgba(10, 255, 0, 0.05);
        }

        .answered-wrong {
            border-color: #ff0055;
            background: rgba(255, 0, 85, 0.05);
        }

        .status-msg {
            margin-top: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        .text-success {
            color: var(--neon-green);
        }

        .text-danger {
            color: #ff0055;
        }
    </style>
</head>

<body>

    <div class="header">
        <a href="zakovat.php" style="color:white; text-decoration:none;"><i class="fas fa-arrow-left"></i> Chiqish</a>
        <div class="arena-title"><?php echo htmlspecialchars($arena['title']); ?></div>
        <div><i class="fas fa-user"></i></div>
    </div>

    <div class="container">
        <?php
        if ($questions && $questions->num_rows > 0):
            $count = 1;
            while ($q = $questions->fetch_assoc()):
                // Statusni aniqlash
                $class = '';
                $status_html = '';

                if ($q['my_answer'] !== null) {
                    if ($q['is_correct']) {
                        $class = 'answered-correct';
                        $status_html = '<div class="status-msg text-success"><i class="fas fa-check-circle"></i> Javob to\'g\'ri! (+' . $q['points'] . ' ball)</div>';
                    } else {
                        $class = 'answered-wrong';
                        $status_html = '<div class="status-msg text-danger"><i class="fas fa-times-circle"></i> Javob xato. Sizning javob: ' . htmlspecialchars($q['my_answer']) . '</div>';
                    }
                }
                ?>
                <div class="q-card <?php echo $class; ?>" id="card-<?php echo $q['id']; ?>">
                    <div class="q-header">
                        <span>Savol #<?php echo $count++; ?></span>
                        <span class="q-points"><i class="fas fa-star"></i> <?php echo $q['points']; ?> Ball</span>
                    </div>

                    <div class="q-text">
                        <?php echo nl2br(htmlspecialchars($q['question'])); ?>
                    </div>

                    <?php if ($q['my_answer'] === null): ?>
                        <form class="answer-form" data-id="<?php echo $q['id']; ?>">
                            <div class="input-group">
                                <input type="text" class="answer-input" name="answer" placeholder="Javobingizni yozing..." required
                                    autocomplete="off">
                                <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </form>
                    <?php else: ?>
                        <?php echo $status_html; ?>
                    <?php endif; ?>
                </div>
            <?php endwhile;
        else: ?>
            <div style="text-align:center; color:#888; margin-top:50px;">
                <i class="fas fa-box-open" style="font-size:50px; margin-bottom:20px;"></i>
                <h3>Bu arenada hali savollar yo'q.</h3>
                <p>Admin tomonidan savollar biriktirilishini kuting.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.answer-form').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const questionId = this.getAttribute('data-id');
                const input = this.querySelector('input[name="answer"]');
                const btn = this.querySelector('button');
                const answer = input.value.trim();
                const card = document.getElementById('card-' + questionId);

                if (!answer) return;
                input.disabled = true;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                fetch('submit_answer.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `question_id=${questionId}&answer=${encodeURIComponent(answer)}&arena_id=<?php echo $arena_id; ?>`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            btn.remove();

                            let msg = document.createElement('div');
                            msg.className = 'status-msg';

                            if (data.is_correct) {
                                card.classList.add('answered-correct');
                                msg.classList.add('text-success');
                                msg.innerHTML = '<i class="fas fa-check-circle"></i> Javob to\'g\'ri! Ball berildi.';
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Barakalla!',
                                    text: 'Javobingiz to\'g\'ri',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    background: '#1a1a2e',
                                    color: '#fff'
                                });
                            } else {
                                card.classList.add('answered-wrong');
                                msg.classList.add('text-danger');
                                msg.innerHTML = '<i class="fas fa-times-circle"></i> Javob xato.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Afsuski...',
                                    text: 'Javobingiz noto\'g\'ri',
                                    timer: 1500,
                                    showConfirmButton: false,
                                    background: '#1a1a2e',
                                    color: '#fff'
                                });
                            }
                            card.appendChild(msg);
                        } else {
                            Swal.fire('Xatolik', data.message, 'error');
                            input.disabled = false;
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire('Xatolik', 'Server bilan aloqa yo\'q', 'error');
                        input.disabled = false;
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                    });
            });
        });
    </script>

</body>

</html>
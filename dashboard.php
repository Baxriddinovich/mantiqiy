<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : "Foydalanuvchi";
$sql = "SELECT * FROM questions ORDER BY id ASC";
$result = $conn->query($sql);
$questions = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}
$stmt = $conn->prepare("SELECT * FROM user_answers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$answers_result = $stmt->get_result();

$user_answers = [];
$total_score = 0;
$solved_count = 0;

while ($row = $answers_result->fetch_assoc()) {
    $user_answers[$row['question_id']] = $row;
    if ($row['is_correct']) {
        $total_score += 10;
        $solved_count++;
    }
}
$rank_title = "Boshlovchi";
$rank_color = "#ff0000";
if ($total_score >= 50) {
    $rank_title = "Havaskor";
    $rank_color = "#00f2ff";
}
if ($total_score >= 100) {
    $rank_title = "Mantiq Ustasi";
    $rank_color = "#bc13fe";
}
if ($total_score >= 200) {
    $rank_title = "Kiber Daho";
    $rank_color = "#0aff00";
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $msg = trim($_POST['message']);
    if (!empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $msg);
        $stmt->execute();
        exit('success'); 
    }
}
?>

<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LogicMaster - Arena</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Poppins:wght@300;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-gradient: linear-gradient(301deg, #0f0c29, #302b63, #24243e);
            --glass: rgba(255, 255, 255, 0.1);
            --glass-dark: rgba(15, 12, 41, 0.9);
            --glass-border: rgba(255, 255, 255, 0.2);
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
            overflow-x: hidden;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }
        .navbar {
            background: rgba(15, 12, 41, 0.8);
            backdrop-filter: blur(10px);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--glass-border);
        }

        .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 22px;
            background: linear-gradient(to right, var(--neon-blue), var(--neon-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
        }
        .user-menu-container {
            position: relative;
        }

        .user-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--glass);
            padding: 5px 12px;
            border-radius: 50px;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .avatar-circle {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--neon-purple), var(--neon-blue));
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            color: white;
        }

        .dropdown-menu {
            position: absolute;
            top: 130%;
            right: 0;
            width: 280px;
            background: var(--glass-dark);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 1001;
        }

        .user-menu-container.active .dropdown-menu {
            display: block;
            animation: slideDown 0.3s;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            color: #ddd;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.2s;
            font-size: 15px;
        }

        .menu-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding-left: 15px;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .section-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            margin-bottom: 20px;
            border-left: 5px solid var(--neon-blue);
            padding-left: 15px;
        }

        .arena-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .arena-card {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            transition: 0.3s;
            cursor: pointer;
        }

        .arena-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.2);
            border-color: var(--neon-blue);
        }

        .arena-img-box {
            height: 150px;
            background: #222;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: #444;
            position: relative;
        }

        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-active {
            background: var(--neon-green);
            color: #000;
            box-shadow: 0 0 10px var(--neon-green);
        }

        .status-upcoming {
            background: #ff9f43;
            color: #000;
        }

        .arena-content {
            padding: 20px;
        }

        .arena-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 10px;
            color: var(--neon-blue);
        }

        .arena-desc {
            font-size: 13px;
            color: #aaa;
            margin-bottom: 15px;
        }

        .arena-btn {
            display: block;
            width: 100%;
            padding: 10px;
            text-align: center;
            background: linear-gradient(90deg, var(--neon-blue), var(--neon-purple));
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }
        .levels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .level-card {
            aspect-ratio: 1;
            border-radius: 15px;
            background: var(--glass);
            border: 1px solid var(--glass-border);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-decoration: none;
            transition: 0.3s;
        }

        .level-card.solved {
            border-color: var(--neon-green);
            color: var(--neon-green);
            box-shadow: inset 0 0 15px rgba(10, 255, 0, 0.2);
        }

        .level-card.locked {
            opacity: 0.5;
            cursor: not-allowed;
            background: rgba(0, 0, 0, 0.3);
        }
        .chat-fab {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--neon-blue), var(--neon-purple));
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            box-shadow: 0 0 20px rgba(188, 19, 254, 0.5);
            cursor: pointer;
            z-index: 2000;
            transition: 0.3s;
        }

        .chat-fab:hover {
            transform: scale(1.1) rotate(10deg);
        }

        .chat-container {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 350px;
            height: 500px;
            background: #161231;
            border: 1px solid var(--neon-blue);
            border-radius: 20px;
            display: none;
            flex-direction: column;
            z-index: 2001;
            overflow: hidden;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.8);
        }

        .chat-header {
            padding: 15px;
            background: rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .chat-input-area {
            padding: 10px;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            gap: 10px;
        }

        .chat-input {
            flex: 1;
            padding: 10px;
            border-radius: 20px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            outline: none;
        }

        .chat-send {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: var(--neon-blue);
            cursor: pointer;
            color: black;
        }

        /* Chat bubbles */
        .msg {
            max-width: 80%;
            padding: 8px 12px;
            border-radius: 15px;
            font-size: 13px;
            line-height: 1.4;
        }

        .msg.me {
            align-self: flex-end;
            background: linear-gradient(90deg, var(--neon-purple), var(--neon-blue));
            border-bottom-right-radius: 2px;
        }

        .msg.them {
            align-self: flex-start;
            background: rgba(255, 255, 255, 0.1);
            border-bottom-left-radius: 2px;
        }

        .msg-user {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.7);
            display: block;
            margin-bottom: 2px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 3000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: #1a1a2e;
            margin: 15% auto;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            border-radius: 15px;
            border: 1px solid var(--neon-purple);
            text-align: center;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }

        textarea.creator-input {
            width: 100%;
            height: 100px;
            margin: 15px 0;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid #444;
            color: white;
            padding: 10px;
            border-radius: 8px;
            box-sizing: border-box;
        }
        @media (max-width: 768px) {
            .navbar {
                padding: 10px 15px;
            }

            .logo {
                font-size: 18px;
            }

            .chat-container {
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                bottom: 0;
                right: 0;
                border-radius: 0;
                z-index: 3000;
                border: none;
            }

            .chat-fab {
                bottom: 15px;
                right: 15px;
                width: 50px;
                height: 50px;
            }

            .close-chat-mobile {
                display: block;
                font-size: 24px;
                cursor: pointer;
            }

            .arena-img-box {
                height: 120px;
            }
        }
    </style>
</head>

<body>
    <div class="navbar">
        <div class="logo"><i class="fas fa-brain"></i> LOGICMASTER</div>
        <div class="user-menu-container" onclick="toggleMenu()">
            <div class="user-toggle">
                <div class="avatar-circle"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="dropdown-menu" id="userDropdown">
                <div style="text-align: center; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <h3><?php echo htmlspecialchars($user_name); ?></h3>
                    <span
                        style="color: <?php echo $rank_color; ?>; font-weight: bold;"><?php echo $rank_title; ?></span>
                </div>

                <a href="zakovat.php" class="menu-link">
                    <i class="fas fa-chess-king" style="color: var(--neon-red);"></i> Zakovat
                </a>

                <a href="reyting.php" class="menu-link">
                    <i class="fas fa-trophy" style="color: gold;"></i> Reyting
                </a>

                <a href="#" onclick="openChat(event)" class="menu-link">
                    <i class="fas fa-comments" style="color: var(--neon-green);"></i> Umumiy Chat
                </a>

                <a href="settings.php" class="menu-link">
                    <i class="fas fa-cog" style="color: var(--neon-blue);"></i> Sozlamalar
                </a>

                <div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 5px 0;"></div>
                <a href="logout.php" class="menu-link" style="color: var(--neon-red);">
                    <i class="fas fa-sign-out-alt"></i> Chiqish
                </a>
            </div>
        </div>
    </div>

   
    <div class="container">
        <div class="section-title">MANTIQIY <span style="color: var(--neon-purple);">SAVOLLAR</span></div>
        <div class="levels-grid">
            <?php
            $previous_solved = true;
            foreach ($questions as $index => $q):
                $is_solved = isset($user_answers[$q['id']]) && $user_answers[$q['id']]['is_correct'];
                $class = "level-card";
                if ($is_solved)
                    $class .= " solved";
                elseif (!$previous_solved)
                    $class .= " locked";
                $href = (strpos($class, 'locked') !== false) ? "#" : "savol.php?q=" . $index;
                ?>
                <a href="<?php echo $href; ?>" class="<?php echo $class; ?>">
                    <?php echo $is_solved ? '<i class="fas fa-check"></i>' : ($index + 1); ?>
                </a>
                <?php $previous_solved = $is_solved; endforeach; ?>
        </div>
    </div>
    <div class="chat-fab" onclick="toggleChat()"><i class="fas fa-comment-dots"></i></div>
    <div class="chat-container" id="chatBox">
        <div class="chat-header">
            <span><i class="fas fa-users"></i> Umumiy Chat</span>
            <i class="fas fa-times" onclick="toggleChat()" style="cursor:pointer;"></i>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="msg them">
                <span class="msg-user">Admin</span>
                Xush kelibsiz! Bu yerda savollarni muhokama qilishingiz mumkin.
            </div>
        </div>
        <div class="chat-input-area">
            <input type="text" id="chatInput" class="chat-input" placeholder="Xabar yozing...">
            <button class="chat-send" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

<script>
    function toggleMenu() {
        document.querySelector('.user-menu-container').classList.toggle('active');
    }
    const chatBox = document.getElementById('chatBox');
    const chatMessagesDiv = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    let isChatOpen = false;

    function toggleChat() {
        if (chatBox.style.display === 'flex') {
            chatBox.style.display = 'none';
            isChatOpen = false;
        } else {
            chatBox.style.display = 'flex';
            isChatOpen = true;
            loadMessages(); 
            setTimeout(scrollToBottom, 200);
        }
    }
    function openChat(e) {
        e.preventDefault();
        chatBox.style.display = 'flex';
        isChatOpen = true;
        document.querySelector('.user-menu-container').classList.remove('active');
        loadMessages();
        setTimeout(scrollToBottom, 200);
    }
    function scrollToBottom() {
        chatMessagesDiv.scrollTop = chatMessagesDiv.scrollHeight;
    }
    function sendMessage() {
        const msg = chatInput.value.trim();
        if (!msg) return;
        const formData = new FormData();
        formData.append('action', 'send'); 
        formData.append('message', msg);
        chatInput.value = '';

        fetch('chat_api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                console.log("Xabar ketdi!");
                loadMessages(); 
            } else {
                alert("Xatolik: " + data.msg);
            }
        })
        .catch(err => console.error(err));
    }
    function loadMessages() {
        if (!isChatOpen) return;

        const formData = new FormData();
        formData.append('action', 'fetch'); 

        fetch('chat_api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.status === 'success') {
                const messages = resp.data;
                chatMessagesDiv.innerHTML = ''; 
                chatMessagesDiv.innerHTML += `
                    <div class="msg them">
                        <span class="msg-user">Admin</span>
                        Xush kelibsiz! Omad tilaymiz.
                    </div>`;

                messages.forEach(msg => {
                    const msgClass = msg.is_me ? 'me' : 'them';
                    const userName = msg.is_me ? 'Siz' : msg.username;
                    
                    const html = `
                        <div class="msg ${msgClass}">
                            <span class="msg-user">
                                ${userName} 
                                <span style="font-size:9px; opacity:0.6; float:right; margin-left:5px;">${msg.time}</span>
                            </span>
                            ${msg.message}
                        </div>
                    `;
                    chatMessagesDiv.innerHTML += html;
                });
            }
        })
        .catch(err => console.error(err));
    }

    chatInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') sendMessage();
    });
    setInterval(loadMessages, 2000);
    window.onclick = function(event) {
        if (!event.target.closest('.user-menu-container')) {
            document.querySelector('.user-menu-container').classList.remove('active');
        }
    }
</script>
</body>

</html>
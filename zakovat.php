<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$sql = "SELECT * FROM arenas ORDER BY 
        CASE status 
            WHEN 'active' THEN 1 
            WHEN 'upcoming' THEN 2 
            ELSE 3 
        END, start_time ASC";
$result = $conn->query($sql);
$arenas = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $arenas[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>LogicMaster - Zakovat Arena</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-gradient: linear-gradient(301deg, #0f0c29, #302b63, #24243e);
            --neon-blue: #00f2ff;
            --neon-purple: #bc13fe;
            --neon-green: #0aff00;
            --neon-orange: #ff9f43;
            --neon-red: #ff0055;
            --card-bg: rgba(255, 255, 255, 0.05);
            --border: rgba(255, 255, 255, 0.1);
        }

        body {
            margin: 0; padding: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient);
            background-attachment: fixed;
            color: white;
            min-height: 100vh;
        }
        .nav-header {
            padding: 20px 30px; display: flex; align-items: center; justify-content: space-between;
            background: rgba(0,0,0,0.2); backdrop-filter: blur(10px);
            position: sticky; top: 0; z-index: 100; border-bottom: 1px solid var(--border);
        }
        .back-btn {
            color: white; text-decoration: none; font-size: 18px;
            display: flex; align-items: center; gap: 10px; transition: 0.3s;
        }
        .back-btn:hover { color: var(--neon-blue); text-shadow: 0 0 10px var(--neon-blue); }
        .page-title {
            font-family: 'Orbitron', sans-serif; font-size: 24px; letter-spacing: 2px;
            background: linear-gradient(to right, var(--neon-blue), var(--neon-purple));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: bold;
        }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        .arena-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }
        .card {
            background: var(--card-bg); border-radius: 20px;
            border: 1px solid var(--border); overflow: hidden;
            position: relative; transition: all 0.4s ease;
            backdrop-filter: blur(10px);
            display: flex; flex-direction: column;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .card-banner {
            height: 160px; background: #1a1a2e;
            display: flex; justify-content: center; align-items: center;
            font-size: 60px; color: rgba(255,255,255,0.1);
            position: relative; overflow: hidden;
        }
        .card-banner::after {
            content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 50%;
            background: linear-gradient(to top, var(--card-bg), transparent);
        }
        .status-badge {
            position: absolute; top: 15px; right: 15px;
            padding: 6px 14px; border-radius: 30px; font-size: 12px; font-weight: bold;
            text-transform: uppercase; letter-spacing: 1px;
            display: flex; align-items: center; gap: 6px; box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }
        .status-active { background: rgba(10, 255, 0, 0.2); border: 1px solid var(--neon-green); color: var(--neon-green); }
        .pulse-dot { width: 8px; height: 8px; background: var(--neon-green); border-radius: 50%; animation: pulse 1.5s infinite; }
        .status-upcoming { background: rgba(255, 159, 67, 0.2); border: 1px solid var(--neon-orange); color: var(--neon-orange); }
        .status-finished { background: rgba(255, 255, 255, 0.1); border: 1px solid #666; color: #aaa; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(10, 255, 0, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(10, 255, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(10, 255, 0, 0); }
        }
        .card-body { padding: 25px; flex: 1; display: flex; flex-direction: column; }
        
        .card-title {
            font-size: 20px; font-weight: bold; margin-bottom: 10px; color: white;
            font-family: 'Orbitron', sans-serif;
        }
        
        .card-desc {
            font-size: 14px; color: #ccc; line-height: 1.5; margin-bottom: 20px; flex-grow: 1;
        }

        .card-meta {
            display: flex; align-items: center; gap: 15px; font-size: 13px; color: #888; margin-bottom: 20px;
        }
        .meta-item { display: flex; align-items: center; gap: 5px; }
        .card-btn {
            display: block; width: 100%; padding: 12px; text-align: center;
            border-radius: 10px; font-weight: bold; text-decoration: none;
            text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
            border: none; cursor: pointer; font-size: 14px;
        }
        .btn-enter {
            background: linear-gradient(90deg, var(--neon-blue), var(--neon-purple)); color: white;
            box-shadow: 0 0 20px rgba(188, 19, 254, 0.3);
        }
        .btn-enter:hover { box-shadow: 0 0 30px rgba(0, 242, 255, 0.6); transform: scale(1.02); }

        .btn-wait {
            background: rgba(255, 159, 67, 0.1); border: 1px solid var(--neon-orange); color: var(--neon-orange);
            cursor: default;
        }

        .btn-closed {
            background: rgba(255,255,255,0.05); color: #666; border: 1px solid #444; cursor: not-allowed;
        }
        .card.active-card:hover { border-color: var(--neon-green); }
        .card.upcoming-card:hover { border-color: var(--neon-orange); }

        @media (max-width: 768px) {
            .container { padding: 0 15px; }
            .card-banner { height: 140px; }
        }
    </style>
</head>
<body>

    <div class="nav-header">
        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Bosh sahifa</a>
        <div class="page-title">ZAKOVAT ARENA</div>
        <div style="width: 80px;"></div> 
    </div>

    <div class="container">
        <?php if (empty($arenas)): ?>
            <div style="text-align: center; margin-top: 100px; color: #888;">
                <i class="fas fa-chess-board" style="font-size: 60px; margin-bottom: 20px; color: #444;"></i>
                <h2>Hozircha turnirlar yo'q</h2>
                <p>Admin tomonidan yangi arena qo'shilishini kuting.</p>
            </div>
        <?php else: ?>
            <div class="arena-grid">
                <?php foreach ($arenas as $arena): 
                    $start_date = date('d.m.Y', strtotime($arena['start_time']));
                    $start_time = date('H:i', strtotime($arena['start_time']));
                                        $card_class = '';
                    $icon = 'fa-trophy';
                    
                    if($arena['status'] == 'active') {
                        $card_class = 'active-card';
                        $icon = 'fa-bolt'; 
                    } elseif($arena['status'] == 'upcoming') {
                        $card_class = 'upcoming-card';
                        $icon = 'fa-hourglass-start';
                    } else {
                        $card_class = 'finished-card';
                        $icon = 'fa-flag-checkered';
                    }
                ?>
                    <div class="card <?php echo $card_class; ?>">
                        <div class="card-banner">
                            <i class="fas <?php echo $icon; ?>"></i>

                            <?php if ($arena['status'] == 'active'): ?>
                                <div class="status-badge status-active">
                                    <div class="pulse-dot"></div> JONLI
                                </div>
                            <?php elseif ($arena['status'] == 'upcoming'): ?>
                                <div class="status-badge status-upcoming">
                                    <i class="far fa-clock"></i> TEZ KUNDA
                                </div>
                            <?php else: ?>
                                <div class="status-badge status-finished">
                                    YAKUNLANGAN
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="card-title"><?php echo htmlspecialchars($arena['title']); ?></div>
                            
                            <div class="card-meta">
                                <div class="meta-item"><i class="far fa-calendar-alt"></i> <?php echo $start_date; ?></div>
                                <div class="meta-item"><i class="far fa-clock"></i> <?php echo $start_time; ?></div>
                            </div>

                            <div class="card-desc">
                                <?php echo htmlspecialchars($arena['description']); ?>
                            </div>

                            <?php if ($arena['status'] == 'active'): ?>
                                <a href="arena.php?id=<?php echo $arena['id']; ?>" class="card-btn btn-enter">
                                    ARENAGA KIRISH <i class="fas fa-sign-in-alt"></i>
                                </a>
                            <?php elseif ($arena['status'] == 'upcoming'): ?>
                                <button class="card-btn btn-wait">
                                    BOSHLANISHI: <?php echo $start_time; ?>
                                </button>
                            <?php else: ?>
                                <button class="card-btn btn-closed">
                                    TURNIR TUGADI
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
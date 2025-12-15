<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$sql = "
    SELECT u.username, u.id, 
    COALESCE(SUM(CASE WHEN ua.is_correct = 1 THEN 10 ELSE 0 END), 0) as total_score 
    FROM users u 
    LEFT JOIN user_answers ua ON u.id = ua.user_id 
    GROUP BY u.id 
    ORDER BY total_score DESC, u.username ASC
";
$result = $conn->query($sql);

$leaders = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $leaders[] = $row;
    }
}
$top3 = array_slice($leaders, 0, 3);        
$challengers = array_slice($leaders, 3, 10); 
$others = array_slice($leaders, 13);         
function getInitials($name) {
    return strtoupper(substr($name, 0, 1));
}
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>LogicMaster - Reyting</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-gradient: linear-gradient(301deg, #0f0c29, #302b63, #24243e);
            --gold: #ffd700;
            --silver: #c0c0c0;
            --bronze: #cd7f32;
            --neon-blue: #00f2ff;
            --neon-purple: #bc13fe;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --dark-glass: rgba(0, 0, 0, 0.3);
        }

        body {
            margin: 0; font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient); background-attachment: fixed;
            color: white; min-height: 100vh;
        }

        .back-nav { padding: 20px; position: absolute; top: 0; left: 0; z-index: 10; }
        .back-btn {
            color: white; text-decoration: none; font-size: 20px;
            background: rgba(255,255,255,0.1); padding: 10px 20px; border-radius: 30px;
            backdrop-filter: blur(5px); transition: 0.3s;
        }
        .back-btn:hover { background: var(--neon-blue); color: #000; box-shadow: 0 0 15px var(--neon-blue); }

        .container { max-width: 800px; margin: 0 auto; padding: 60px 20px 20px; text-align: center; }
        
        h1 {
            font-family: 'Orbitron', sans-serif; font-size: 2.5rem; margin-bottom: 40px;
            text-transform: uppercase; letter-spacing: 2px;
            background: linear-gradient(to right, #fff, var(--neon-blue));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            text-shadow: 0 0 10px rgba(0,242,255,0.3);
        }
        .podium {
            display: flex; justify-content: center; align-items: flex-end;
            gap: 20px; margin-bottom: 60px; height: 280px;
        }
        .podium-item {
            display: flex; flex-direction: column; align-items: center; position: relative;
            transition: 0.3s;
        }
        .podium-item:hover { transform: translateY(-10px); }
        .avatar {
            width: 70px; height: 70px; border-radius: 50%;
            background: #333; display: flex; justify-content: center; align-items: center;
            font-weight: bold; font-size: 24px; border: 3px solid white;
            margin-bottom: -15px; z-index: 2; box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }
        .crown { position: absolute; top: -25px; font-size: 30px; color: gold; animation: bounce 2s infinite; }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
        
        .bar {
            width: 80px; border-radius: 15px 15px 0 0;
            display: flex; flex-direction: column; justify-content: flex-end; align-items: center;
            padding-bottom: 10px; color: #1a1a2e; font-weight: bold;
            animation: slideUp 0.8s forwards;
        }
        .rank-1 .bar { height: 160px; background: linear-gradient(180deg, var(--gold), #b8860b); box-shadow: 0 0 30px rgba(255, 215, 0, 0.4); }
        .rank-1 .avatar { border-color: var(--gold); width: 90px; height: 90px; font-size: 32px; }
        .rank-2 .bar { height: 120px; background: linear-gradient(180deg, var(--silver), #7f8c8d); }
        .rank-2 .avatar { border-color: var(--silver); }
        .rank-3 .bar { height: 90px; background: linear-gradient(180deg, var(--bronze), #8b4513); }
        .rank-3 .avatar { border-color: var(--bronze); }
        
        .p-name { margin-bottom: 5px; font-size: 14px; font-weight: bold; text-shadow: 0 2px 4px black; }
        @keyframes slideUp { from { height: 0; opacity: 0; } to { opacity: 1; } }
        .section-title {
            text-align: left; font-family: 'Orbitron'; color: #aaa; margin: 30px 0 10px; font-size: 14px; letter-spacing: 1px;
        }
        .list-container {
            display: flex; flex-direction: column; gap: 10px;
        }
        .list-item {
            display: flex; align-items: center; padding: 12px 20px;
            border-radius: 12px; transition: 0.2s;
            animation: fadeIn 0.5s ease forwards; opacity: 0;
        }
        .list-item:hover { transform: scale(1.02); }

        .rank-num { width: 30px; font-weight: bold; font-family: 'Orbitron'; text-align: center; }
        .list-avatar {
            width: 35px; height: 35px; border-radius: 50%;
            display: flex; justify-content: center; align-items: center; margin: 0 15px; font-size: 14px;
        }
        .list-name { flex: 1; text-align: left; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .list-score { font-weight: bold; font-family: 'Orbitron'; }
        .challengers-list .list-item {
            background: linear-gradient(90deg, rgba(188, 19, 254, 0.2), rgba(0, 242, 255, 0.1));
            border-left: 4px solid var(--neon-purple);
            border-right: 1px solid rgba(255,255,255,0.1);
            border-top: 1px solid rgba(255,255,255,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .challengers-list .list-item:hover {
            box-shadow: 0 0 15px rgba(188, 19, 254, 0.4);
            border-left-color: var(--neon-blue);
        }
        .challengers-list .rank-num { color: var(--neon-blue); font-size: 18px; }
        .challengers-list .list-avatar { background: var(--neon-purple); color: white; }
        .challengers-list .list-score { color: var(--neon-blue); font-size: 16px; }

        .others-list .list-item {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255,255,255,0.05);
            color: #ccc; padding: 8px 20px; /* Biroz kichikroq */
        }
        .others-list .list-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .others-list .rank-num { color: #666; font-size: 14px; }
        .others-list .list-avatar { background: #444; color: #aaa; width: 30px; height: 30px; font-size: 12px; }
        .others-list .list-score { color: #888; font-size: 14px; }

        .list-item.me {
            border: 2px solid var(--neon-blue) !important;
            background: rgba(0, 242, 255, 0.15) !important;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        @media(max-width: 600px) {
            .podium { height: auto; align-items: flex-end; gap: 10px; margin-top: 20px; }
            .bar { width: 60px; }
            .rank-1 .bar { height: 130px; }
            .rank-2 .bar { height: 90px; }
            .rank-3 .bar { height: 70px; }
            .avatar { width: 50px; height: 50px; font-size: 18px; }
            .rank-1 .avatar { width: 65px; height: 65px; }
        }
    </style>
</head>
<body>

    <div class="back-nav">
        <a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
    </div>

    <div class="container">
        <h1>LIDERLAR</h1>

        <div class="podium">
            <?php 
            if(isset($top3[1])): $u = $top3[1]; ?>
            <div class="podium-item rank-2">
                <div class="p-name"><?php echo htmlspecialchars($u['username']); ?></div>
                <div class="avatar"><?php echo getInitials($u['username']); ?></div>
                <div class="bar"><span class="list-score"><?php echo $u['total_score']; ?></span></div>
            </div>
            <?php endif; ?>

            <?php 
            if(isset($top3[0])): $u = $top3[0]; ?>
            <div class="podium-item rank-1">
                <i class="fas fa-crown crown"></i>
                <div class="p-name"><?php echo htmlspecialchars($u['username']); ?></div>
                <div class="avatar"><?php echo getInitials($u['username']); ?></div>
                <div class="bar"><span class="list-score"><?php echo $u['total_score']; ?></span></div>
            </div>
            <?php endif; ?>

            <?php 
            if(isset($top3[2])): $u = $top3[2]; ?>
            <div class="podium-item rank-3">
                <div class="p-name"><?php echo htmlspecialchars($u['username']); ?></div>
                <div class="avatar"><?php echo getInitials($u['username']); ?></div>
                <div class="bar"><span class="list-score"><?php echo $u['total_score']; ?></span></div>
            </div>
            <?php endif; ?>
        </div>
        <?php if(!empty($challengers)): ?>
        <div class="section-title"><i class="fas fa-fire" style="color:orange;"></i> KUCHLI O'NLIK</div>
        <div class="list-container challengers-list">
            <?php 
            $rank = 4;
            foreach($challengers as $user): 
                $is_me = ($user['id'] == $_SESSION['user_id']) ? 'me' : '';
            ?>
                <div class="list-item <?php echo $is_me; ?>" style="animation-delay: <?php echo ($rank - 3) * 0.1; ?>s;">
                    <div class="rank-num"><?php echo $rank; ?></div>
                    <div class="list-avatar"><?php echo getInitials($user['username']); ?></div>
                    <div class="list-name">
                        <?php echo htmlspecialchars($user['username']); ?>
                        <?php if($is_me) echo '<span style="font-size:10px; color:var(--neon-blue); margin-left:5px;">(Siz)</span>'; ?>
                    </div>
                    <div class="list-score"><?php echo $user['total_score']; ?></div>
                </div>
            <?php 
            $rank++;
            endforeach; 
            ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($others)): ?>
        <div class="section-title"><i class="fas fa-users"></i> BARCHA QATNASHCHILAR</div>
        <div class="list-container others-list">
            <?php 
            foreach($others as $user): 
                $is_me = ($user['id'] == $_SESSION['user_id']) ? 'me' : '';
            ?>
                <div class="list-item <?php echo $is_me; ?>">
                    <div class="rank-num">#<?php echo $rank; ?></div>
                    <div class="list-avatar"><?php echo getInitials($user['username']); ?></div>
                    <div class="list-name">
                        <?php echo htmlspecialchars($user['username']); ?>
                        <?php if($is_me) echo '<span style="font-size:10px; color:var(--neon-blue); margin-left:5px;">(Siz)</span>'; ?>
                    </div>
                    <div class="list-score"><?php echo $user['total_score']; ?></div>
                </div>
            <?php 
            $rank++;
            endforeach; 
            ?>
        </div>
        <?php endif; ?>

        <?php if(empty($leaders)): ?>
            <p style="color:#aaa; margin-top:50px;">Hozircha ma'lumot yo'q.</p>
        <?php endif; ?>

    </div>
</body>
</html>
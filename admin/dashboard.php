<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (file_exists('../db.php')) {
    require_once '../db.php';
} else {
    die("<div style='color:red; padding:20px; text-align:center;'><h1>Xatolik!</h1><p>'../db.php' fayli topilmadi. Admin papkasi ichida ekanligingizga ishonch hosil qiling.</p></div>");
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$u_data = $stmt->get_result()->fetch_assoc();

if (!$u_data || $u_data['role'] !== 'admin') {
    die("<h1 style='color:red; text-align:center; margin-top:50px;'>⛔ Kirish taqiqlangan! Siz Admin emassiz.</h1>");
}
if (isset($_POST['create_arena'])) {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $prize = $_POST['prize'];
    $start = $_POST['start_time'];
    $status = $_POST['status']; 

    $sql = "INSERT INTO arenas (title, description, prize, start_time, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

   

    $stmt->bind_param("sssss", $title, $desc, $prize, $start, $status);
    
    if($stmt->execute()){
        header("Location: ?page=arenas");
        exit;
    } else {
        die("Saqlashda xato: " . $stmt->error);
    }
}
if (isset($_GET['del_arena'])) {
    $id = intval($_GET['del_arena']);
    $conn->query("DELETE FROM arenas WHERE id=$id");
    header("Location: ?page=arenas"); 
    exit;
}
if (isset($_POST['link_question'])) {
    $arena_id = intval($_POST['arena_id']);
    $q_id = intval($_POST['question_id']);
    $points = intval($_POST['points']);
    $check = $conn->query("SELECT id FROM arena_questions WHERE arena_id=$arena_id AND question_id=$q_id");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO arena_questions (arena_id, question_id, points) VALUES ($arena_id, $q_id, $points)");
    }
    header("Location: ?page=arena_view&id=$arena_id"); 
    exit;
}
if (isset($_GET['unlink_q'])) {
    $link_id = intval($_GET['unlink_q']);
    $arena_id = intval($_GET['aid']);
    $conn->query("DELETE FROM arena_questions WHERE id=$link_id");
    header("Location: ?page=arena_view&id=$arena_id"); 
    exit;
}
if (isset($_POST['add_question'])) {
    $q_text = $_POST['question_text'];
    $answer = $_POST['answer'];
    $hint = $_POST['hint'] ?? '';
    $created_by = $_SESSION['user_id'];
    $db_image_path = ""; 
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $upload_dir = "../uploads/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = time() . "_" . uniqid() . "." . $file_ext;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $upload_dir . $new_filename)) {
            $db_image_path = "uploads/" . $new_filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO questions (question, answer, hint, image_path, created_by) VALUES (?, ?, ?, ?, ?)");
    if(!$stmt) { die("SQL Error (Questions): " . $conn->error); }
    
    $stmt->bind_param("ssssi", $q_text, $answer, $hint, $db_image_path, $created_by);
    
    if ($stmt->execute()) {
        if (isset($_POST['auto_link_arena_id'])) {
            $new_q_id = $stmt->insert_id;
            $aid = intval($_POST['auto_link_arena_id']);
            $conn->query("INSERT INTO arena_questions (arena_id, question_id, points) VALUES ($aid, $new_q_id, 10)");
            header("Location: ?page=arena_view&id=$aid"); 
            exit;
        }
        header("Location: ?page=questions"); 
        exit;
    }
}
if (isset($_GET['delete_q'])) {
    $id = intval($_GET['delete_q']);
    $res = $conn->query("SELECT image_path FROM questions WHERE id=$id");
    if($r = $res->fetch_assoc()) {
        if(!empty($r['image_path']) && file_exists("../".$r['image_path'])) { unlink("../".$r['image_path']); }
    }
    $conn->query("DELETE FROM questions WHERE id = $id");
    header("Location: ?page=questions"); 
    exit;
}

if (isset($_GET['ban_user'])) {
    $id = intval($_GET['ban_user']);
    if ($id != $user_id) {
        $conn->query("DELETE FROM users WHERE id = $id");
        $conn->query("DELETE FROM user_answers WHERE user_id = $id");
    }
    header("Location: ?page=users"); 
    exit;
}

if (isset($_GET['delete_msg'])) {
    $conn->query("DELETE FROM chat_messages WHERE id = " . intval($_GET['delete_msg']));
    header("Location: ?page=chat"); 
    exit;
}
$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_questions = $conn->query("SELECT COUNT(*) as c FROM questions")->fetch_assoc()['c'];
$total_arenas = $conn->query("SELECT COUNT(*) as c FROM arenas")->fetch_assoc()['c'];

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>LogicMaster Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #0f0c29; --panel-bg: #1a1a2e; --neon-blue: #00f2ff;
            --neon-purple: #bc13fe; --neon-green: #0aff00; --glass: rgba(255, 255, 255, 0.05);
            --border: rgba(255, 255, 255, 0.1);
        }
        body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--bg-dark); color: white; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 260px; background: var(--panel-bg); border-right: 1px solid var(--border); padding: 20px; display: flex; flex-direction: column; }
        .brand { font-family: 'Orbitron', sans-serif; font-size: 22px; color: var(--neon-blue); margin-bottom: 40px; }
        .nav-item { padding: 15px; color: #aaa; text-decoration: none; border-radius: 10px; margin-bottom: 5px; display: flex; gap: 10px; align-items: center; transition: 0.3s; }
        .nav-item:hover, .nav-item.active { background: var(--glass); color: white; border-left: 4px solid var(--neon-purple); }
        .main-content { flex: 1; padding: 30px; overflow-y: auto; background: radial-gradient(circle at top right, #24243e, #0f0c29); }
        .header-title { font-size: 24px; font-weight: bold; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 15px; color: var(--neon-blue); }
        .card { background: var(--glass); padding: 20px; border-radius: 15px; border: 1px solid var(--border); margin-bottom: 20px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border); }
        th { color: var(--neon-purple); font-size: 14px; }
        tr:hover { background: rgba(255,255,255,0.05); }
        .form-control { width: 100%; padding: 10px; background: rgba(0,0,0,0.3); border: 1px solid #444; color: white; border-radius: 5px; margin-bottom: 10px; box-sizing: border-box; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; color: white; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--neon-blue); color: black; }
        .btn-success { background: var(--neon-green); color: black; }
        .btn-danger { background: #ff0055; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; text-transform: uppercase; }
        .bg-active { background: rgba(10, 255, 0, 0.2); color: var(--neon-green); border: 1px solid var(--neon-green); }
        .bg-upcoming { background: rgba(255, 165, 0, 0.2); color: orange; border: 1px solid orange; }
        .bg-finished { background: rgba(255, 255, 255, 0.1); color: #aaa; }
        .arena-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media(max-width: 900px) { .arena-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand"><i class="fas fa-chess-king"></i> LM ADMIN</div>
        <a href="?page=dashboard" class="nav-item <?php echo $page=='dashboard'?'active':''; ?>"><i class="fas fa-home"></i> Dashboard</a>
        <a href="?page=arenas" class="nav-item <?php echo $page=='arenas'||$page=='arena_view'?'active':''; ?>"><i class="fas fa-trophy"></i> Arenalar</a>
        <a href="?page=questions" class="nav-item <?php echo $page=='questions'?'active':''; ?>"><i class="fas fa-database"></i> Savollar Bazasi</a>
        <a href="?page=users" class="nav-item <?php echo $page=='users'?'active':''; ?>"><i class="fas fa-users"></i> Foydalanuvchilar</a>
        <a href="?page=chat" class="nav-item <?php echo $page=='chat'?'active':''; ?>"><i class="fas fa-comments"></i> Chat</a>
        <a href="../dashboard.php" class="nav-item" style="margin-top: auto;"><i class="fas fa-arrow-left"></i> Saytga Qaytish</a>
    </div>
    <div class="main-content">
        <?php if ($page == 'dashboard'): ?>
            <div class="header-title">Boshqaruv Paneli</div>
            <div class="stats-grid">
                <div class="card"><h3><?php echo $total_users; ?></h3><p>Foydalanuvchilar</p></div>
                <div class="card"><h3><?php echo $total_questions; ?></h3><p>Jami Savollar</p></div>
                <div class="card"><h3><?php echo $total_arenas; ?></h3><p>Arenalar</p></div>
            </div>
        <?php endif; ?>
        <?php if ($page == 'arenas'): ?>
            <div class="header-title">Arenalar Boshqaruvi</div>
            
            <div class="card">
                <h3><i class="fas fa-plus-circle"></i> Yangi Arena Yaratish</h3>
                <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <input type="text" name="title" placeholder="Arena Nomi" class="form-control" required style="grid-column: span 2;">
                    <textarea name="description" placeholder="Qisqacha tavsif" class="form-control" style="grid-column: span 2;"></textarea>
                    <input type="text" name="prize" placeholder="Yutuq (masalan: Samsung S24)" class="form-control">
                    <input type="datetime-local" name="start_time" class="form-control" required>
                    <select name="status" class="form-control">
                        <option value="upcoming">Kutilmoqda (Upcoming)</option>
                        <option value="active">Jonli (Active)</option>
                        <option value="finished">Tugagan (Finished)</option>
                    </select>
                    <button type="submit" name="create_arena" class="btn btn-success" style="grid-column: span 2;">YARATISH</button>
                </form>
            </div>
            <div class="card">
                <h3>Mavjud Arenalar</h3>
                <table>
                    <thead><tr><th>ID</th><th>Nom</th><th>Vaqt</th><th>Status</th><th>Amal</th></tr></thead>
                    <tbody>
                        <?php 
                        $ar = $conn->query("SELECT * FROM arenas ORDER BY id DESC");
                        while($row = $ar->fetch_assoc()): 
                        ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td><b><?php echo htmlspecialchars($row['title']); ?></b><br><small style="color:#aaa"><?php echo htmlspecialchars($row['prize']); ?></small></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($row['start_time'])); ?></td>
                            <td><span class="badge bg-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
                            <td>
                                <a href="?page=arena_view&id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-cog"></i> Boshqarish</a>
                                <a href="?del_arena=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('O\'chirasizmi?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php if ($page == 'arena_view'): 
            $aid = intval($_GET['id']);
            $arena_res = $conn->query("SELECT * FROM arenas WHERE id=$aid");
            if($arena_res->num_rows == 0) { echo "Arena topilmadi"; exit; }
            $arena = $arena_res->fetch_assoc();
        ?>
            <div class="header-title">
                <a href="?page=arenas" style="color:white; margin-right:10px;"><i class="fas fa-arrow-left"></i></a>
                Arena: <?php echo htmlspecialchars($arena['title']); ?>
            </div>

            <div class="arena-grid">
                <div class="card">
                    <h3 style="color:var(--neon-green)">✅ Biriktirilgan Savollar</h3>
                    <?php
                    $linked = $conn->query("SELECT aq.id as link_id, aq.points, q.question, q.answer 
                                            FROM arena_questions aq JOIN questions q ON aq.question_id = q.id 
                                            WHERE aq.arena_id = $aid ORDER BY aq.id ASC");
                    if ($linked->num_rows > 0):
                    ?>
                        <table>
                            <thead><tr><th>Savol</th><th>Ball</th><th>Amal</th></tr></thead>
                            <tbody>
                                <?php while($l = $linked->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(substr($l['question'],0,50)); ?>...</td>
                                    <td style="color:yellow"><?php echo $l['points']; ?></td>
                                    <td><a href="?unlink_q=<?php echo $l['link_id']; ?>&aid=<?php echo $aid; ?>" class="btn btn-danger btn-sm">X</a></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color:#aaa; text-align:center;">Hozircha savollar yo'q</p>
                    <?php endif; ?>
                    <div style="margin-top:20px; border-top:1px solid #444; padding-top:10px;">
                        <h4>⚡ Tezkor yangi savol va biriktirish</h4>
                        <form method="POST">
                            <input type="hidden" name="auto_link_arena_id" value="<?php echo $aid; ?>">
                            <textarea name="question_text" placeholder="Yangi savol matni..." class="form-control" required rows="2"></textarea>
                            <input type="text" name="answer" placeholder="Javobi" class="form-control" required>
                            <button type="submit" name="add_question" class="btn btn-success" style="width:100%">BAZAGA QO'SHISH VA BIRIKTIRISH</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <h3 style="color:var(--neon-blue)">🏦 Umumiy Baza (Tanlang)</h3>
                    <?php
                    $bank = $conn->query("SELECT * FROM questions WHERE id NOT IN (SELECT question_id FROM arena_questions WHERE arena_id=$aid) ORDER BY id DESC LIMIT 50");
                    ?>
                    <div style="max-height: 500px; overflow-y: auto;">
                        <?php while($b = $bank->fetch_assoc()): ?>
                            <div style="background:rgba(255,255,255,0.03); padding:10px; margin-bottom:5px; border-radius:5px; display:flex; justify-content:space-between; align-items:center;">
                                <div style="font-size:13px;">
                                    <b>#<?php echo $b['id']; ?></b> <?php echo htmlspecialchars(substr($b['question'],0,40)); ?>...
                                    <br><small style="color:#888">Javob: <?php echo htmlspecialchars($b['answer']); ?></small>
                                </div>
                                <form method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="arena_id" value="<?php echo $aid; ?>">
                                    <input type="hidden" name="question_id" value="<?php echo $b['id']; ?>">
                                    <input type="number" name="points" value="10" style="width:50px; padding:5px; background:#222; color:white; border:1px solid #444;">
                                    <button type="submit" name="link_question" class="btn btn-primary btn-sm">+</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($page == 'questions'): ?>
            <div class="header-title">Global Savollar Bazasi</div>
            <div class="card">
                <h3>Yangi Savol (Bazaga)</h3>
                <form method="POST" enctype="multipart/form-data">
                    <textarea name="question_text" class="form-control" placeholder="Savol matni" required></textarea>
                    <div style="display:flex; gap:10px;">
                        <input type="text" name="answer" class="form-control" placeholder="Javob" required>
                        <input type="text" name="hint" class="form-control" placeholder="Hint (ixtiyoriy)">
                    </div>
                    <input type="file" name="image" class="form-control">
                    <button type="submit" name="add_question" class="btn btn-primary">BAZAGA QO'SHISH</button>
                </form>
            </div>
            <div class="card">
                <table>
                    <thead><tr><th>ID</th><th>Savol</th><th>Javob</th><th>Amal</th></tr></thead>
                    <tbody>
                        <?php $qs = $conn->query("SELECT * FROM questions ORDER BY id DESC LIMIT 50"); 
                        while($q = $qs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $q['id']; ?></td>
                            <td><?php echo htmlspecialchars(substr($q['question'],0,50)); ?>...</td>
                            <td><?php echo htmlspecialchars($q['answer']); ?></td>
                            <td><a href="?delete_q=<?php echo $q['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('O\'chirasizmi?')"><i class="fas fa-trash"></i></a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php if ($page == 'users'): ?>
            <div class="header-title">Foydalanuvchilar</div>
            <div class="card">
                <table>
                    <thead><tr><th>ID</th><th>Login</th><th>Role</th><th>Amal</th></tr></thead>
                    <tbody>
                        <?php $us = $conn->query("SELECT * FROM users ORDER BY id DESC LIMIT 50");
                        while($u = $us->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo $u['role']; ?></td>
                            <td><?php if($u['role']!='admin') echo "<a href='?ban_user={$u['id']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Rostdan ham?')\">BAN</a>"; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($page == 'chat'): ?>
            <div class="header-title">Chat</div>
            <div class="card">
                <table>
                    <thead><tr><th>Vaqt</th><th>User</th><th>Xabar</th><th>Amal</th></tr></thead>
                    <tbody>
                        <?php $ms = $conn->query("SELECT m.*, u.username FROM chat_messages m JOIN users u ON m.user_id=u.id ORDER BY m.id DESC LIMIT 50");
                        while($m = $ms->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('H:i', strtotime($m['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($m['username']); ?></td>
                            <td><?php echo htmlspecialchars($m['message']); ?></td>
                            <td><a href="?delete_msg=<?php echo $m['id']; ?>" class="btn btn-danger btn-sm">X</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
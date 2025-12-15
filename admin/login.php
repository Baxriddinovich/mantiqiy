<?php
session_start();
require_once '../db.php';
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password']);
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $is_valid = false;
        if (password_verify($password, $user['password'])) {
            $is_valid = true;
        } elseif ($password === $user['password']) {
            $is_valid = true;
        }

        if ($is_valid) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = 'admin';
            header("Location: index.php");
            exit;
        } else {
            $error = "Parol noto'g'ri!";
        }
    } else {
        $error = "Tizimda Admin topilmadi! (Bazada role='admin' user yo'q)";
    }
}
?>

<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <title>Kirish</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #0f0c29;
            color: white;
            font-family: sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 40px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 300px;
            text-align: center;
            box-shadow: 0 0 30px rgba(0, 242, 255, 0.2);
        }

        h2 {
            font-family: 'Orbitron', sans-serif;
            color: #00f2ff;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        input {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid #444;
            color: white;
            border-radius: 30px;
            box-sizing: border-box;
            outline: none;
            text-align: center;
            font-size: 16px;
        }

        input:focus {
            border-color: #00f2ff;
            box-shadow: 0 0 10px rgba(0, 242, 255, 0.2);
        }

        button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 30px;
            background: linear-gradient(90deg, #bc13fe, #00f2ff);
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            font-size: 16px;
        }

        button:hover {
            transform: scale(1.05);
        }

        .error {
            color: #ff0055;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h2>XUSH KELIBSIZ</h2>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="password" name="password" placeholder="Maxfiy kodni kiriting" required autofocus>
            <button type="submit">KIRISH <i class="fas fa-arrow-right"></i></button>
        </form>
    </div>
</body>

</html>
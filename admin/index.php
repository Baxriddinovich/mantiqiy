<?php
session_start();
if (isset($_SESSION['user']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit();
}
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $users_db = [
        ['username' => 'admin', 'password' => '12345', 'role' => 'admin'],
        ['username' => 'manager', 'password' => '12345', 'role' => 'manager'],
    ];

    $is_logged_in = false;

    foreach ($users_db as $user) {
        if ($user['username'] === $username && $user['password'] === $password) {

            if ($user['role'] === 'admin') {
                $_SESSION['user'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Siz admin emassiz! Kirish ruxsati yo'q.";
            }
            $is_logged_in = true;
            break;
        }
    }

    if (!$is_logged_in && empty($error)) {
        $error = "Login yoki parol noto'g'ri!";
    }
}
?>

<!DOCTYPE html>
<html lang="uz">

<head>
    <meta charset="UTF-8">
    <title>Admin Kirish</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f2f5;
        }

        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }

        .error {
            color: red;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>

<body>

    <form method="post" action="">
        <h2 style="text-align:center;">Admin Panel</h2>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <input type="text" name="username" placeholder="Login" required>
        <input type="password" name="password" placeholder="Parol" required>
        <button type="submit">Kirish</button>
    </form>

</body>

</html>
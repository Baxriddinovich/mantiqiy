<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Sozlamalar - LogicMaster</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-gradient: radial-gradient(circle at center, #302b63, #0f0c29, #24243e);
            --neon-blue: #00f2ff;
            --neon-purple: #bc13fe;
            --glass: rgba(255, 255, 255, 0.05);
            --border: rgba(255, 255, 255, 0.1);
        }

        body {
            margin: 0; padding: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg-gradient);
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .maintenance-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            animation: float 6s ease-in-out infinite;
        }

        .icon-box {
            font-size: 60px;
            margin-bottom: 20px;
            background: linear-gradient(to right, var(--neon-blue), var(--neon-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .fa-cog {
            animation: spin 4s linear infinite;
        }

        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes float { 
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 24px;
            margin: 0 0 10px 0;
            color: white;
            letter-spacing: 1px;
        }

        p {
            color: #aaa;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .btn-back {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(90deg, var(--neon-blue), var(--neon-purple));
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
            box-shadow: 0 0 15px rgba(188, 19, 254, 0.4);
        }

        .btn-back:hover {
            transform: scale(1.05);
            box-shadow: 0 0 25px rgba(0, 242, 255, 0.6);
        }
    </style>
</head>
<body>

    <div class="maintenance-card">
        <div class="icon-box">
            <i class="fas fa-cog fa-cog"></i>
        </div>
        
        <h1>Profil Sozlamalari</h1>
        
        <p>
            Ushbu bo'limda hozirda texnik tuzatish ishlari olib borilmoqda.<br>
            Tez orada yangi imkoniyatlar qo'shiladi!
        </p>

        <a href="dashboard.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Bosh Sahifa
        </a>
    </div>

</body>
</html>
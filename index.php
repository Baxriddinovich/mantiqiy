<?php
require_once 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $conn->real_escape_string($_POST['username']);
  $password = $_POST['password'];

  $sql = "SELECT * FROM users WHERE username='$username' OR email='$username' LIMIT 1";
  $result = $conn->query($sql);

  if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['firstname'] = $user['firstname'];
      $_SESSION['lastname'] = $user['lastname'];

      echo "<script>alert('Welcome, {$user['firstname']}!'); window.location.href='dashboard.php';</script>";
      exit;
    } else {
      echo "<script>alert('Incorrect password!'); window.location.href='login.php';</script>";
    }
  } else {
    echo "<script>alert('User not found!'); window.location.href='login.php';</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign in</title>
  <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
  <style>
    body {
      background-color: #F3EBF6;
      font-family: 'Ubuntu', sans-serif;
    }

    .main {
      background-color: #FFFFFF;
      width: 400px;
      height: auto;
      margin: 7em auto;
      border-radius: 1.5em;
      box-shadow: 0px 11px 35px 2px rgba(0, 0, 0, 0.14);
      padding-bottom: 30px;
    }

    .sign {
      padding-top: 40px;
      color: #8C55AA;
      font-weight: bold;
      font-size: 23px;
    }

    .un,
    .pass {
      width: 76%;
      color: rgb(38, 50, 56);
      font-weight: 700;
      font-size: 14px;
      letter-spacing: 1px;
      background: rgba(136, 126, 126, 0.04);
      padding: 10px 20px;
      border: none;
      border-radius: 20px;
      outline: none;
      box-sizing: border-box;
      border: 2px solid rgba(0, 0, 0, 0.02);
      margin-left: 46px;
      text-align: center;
      margin-bottom: 27px;
    }

    form.form1 {
      padding-top: 40px;
    }

    .un:focus,
    .pass:focus {
      border: 2px solid rgba(0, 0, 0, 0.18) !important;
    }

    .submit {
      cursor: pointer;
      border-radius: 5em;
      color: #fff;
      background: linear-gradient(to right, #9C27B0, #E040FB);
      border: 0;
      padding-left: 40px;
      padding-right: 40px;
      padding-bottom: 10px;
      padding-top: 10px;
      font-family: 'Ubuntu', sans-serif;
      margin-left: 35%;
      font-size: 13px;
      box-shadow: 0 0 20px 1px rgba(0, 0, 0, 0.04);
    }

    .forgot {
      text-shadow: 0px 0px 3px rgba(117, 117, 117, 0.12);
      color: #E1BEE7;
      padding-top: 15px;
    }

    a {
      text-shadow: 0px 0px 3px rgba(117, 117, 117, 0.12);
      color: #8C55AA;
      text-decoration: none;
    }

    .signup-btn {
      display: block;
      text-align: center;
      margin-top: 20px;
    }

    .signup-btn button {
      border: none;
      background: linear-gradient(to right, #E040FB, #9C27B0);
      color: white;
      border-radius: 30px;
      padding: 10px 25px;
      font-family: 'Ubuntu', sans-serif;
      font-size: 14px;
      cursor: pointer;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s;
    }

    .signup-btn button:hover {
      transform: scale(1.05);
    }

    @media (max-width: 600px) {
      .main {
        border-radius: 0px;
        width: 90%;
      }
    }
  </style>
</head>

<body>
  <div class="main">
    <p class="sign" align="center">Sign in</p>
    <form class="form1" method="post" action="">
      <input class="un" type="text" name="username" placeholder="Username or Email" required>
      <input class="pass" type="password" name="password" placeholder="Password" required>
      <button class="submit" type="submit" align="center">Sign in</button>
      <p class="forgot" align="center"><a href="#">Forgot Password?</a></p>
    </form>
    <div class="signup-btn">
      <button onclick="window.location.href='register.php'">Create account</button>
    </div>
  </div>
</body>

</html>
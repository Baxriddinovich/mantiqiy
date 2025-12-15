<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $firstname = $conn->real_escape_string($_POST['firstname']);
  $lastname = $conn->real_escape_string($_POST['lastname']);
  $username = $conn->real_escape_string($_POST['username']);
  $email = $conn->real_escape_string($_POST['email']);
  $phone = $conn->real_escape_string($_POST['phone']);
  $address = $conn->real_escape_string($_POST['address']);
  $password = $conn->real_escape_string($_POST['password']);

  $sql = "INSERT INTO users (firstname, lastname, username, email, phone, address, password)
            VALUES ('$firstname', '$lastname', '$username', '$email', '$phone', '$address', '$password')";

  if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Registration successful! You can now log in.'); window.location.href='index.php';</script>";
  } else {
    echo "<script>alert('Error: " . $conn->error . "'); window.location.href='register.php';</script>";
  }
  $conn->close();
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign up</title>
  <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
  <style>
    body {
      background-color: #F3EBF6;
      font-family: 'Ubuntu', sans-serif;
    }

    .main {
      background-color: #FFFFFF;
      width: 430px;
      margin: 4em auto;
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
      padding: 10px 40px;
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
      color: #8C55AA;
      text-decoration: none;
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
    <p class="sign" align="center">Create your account</p>
    <form class="form1" method="post" action="">
      <input class="un" type="text" name="firstname" placeholder="First Name" required>
      <input class="un" type="text" name="lastname" placeholder="Last Name" required>
      <input class="un" type="text" name="username" placeholder="Username" required>
      <input class="un" type="email" name="email" placeholder="Email" required>
      <input class="un" type="tel" name="phone" placeholder="Phone Number" required>
      <input class="un" type="text" name="address" placeholder="Address" required>
      <input class="pass" type="password" name="password" placeholder="Password" required>
      <button class="submit" type="submit" align="center">Register</button>
      <p class="forgot" align="center"><a href="index.php">Already have an account?</a></p>
    </form>
  </div>
</body>

</html>
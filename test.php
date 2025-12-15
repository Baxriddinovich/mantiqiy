<?php
require_once 'db.php';
$login_to_test = "Baxriddinovich_fev";
echo "<h2>Tekshiruv: $login_to_test</h2>";

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "Foydalanuvchi topildi!<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Role (bazada): [" . $user['role'] . "]<br>"; // Qavs ichida ko'rsatadi, probel bo'lsa bilinadi
    echo "Parol (bazada): " . $user['password'] . "<br><br>";
    if ($user['role'] === 'admin') {
        echo "<span style='color:green'>✅ Role to'g'ri (admin)</span><br>";
    } else {
        echo "<span style='color:red'>❌ Role xato! Bazada '{$user['role']}' turibdi.</span><br>";
    }
    if (strlen($user['password']) > 20) {
        echo "ℹ️ Parol shifrlangan (Hash) ko'rinadi.<br>";
    } else {
        echo "ℹ️ Parol oddiy matn (Plain text) ko'rinadi.<br>";
    }

} else {
    echo "<h3 style='color:red'>Bunday login bazada yo'q!</h3>";
}
?>
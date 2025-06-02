<?php
session_start();

$servername = "sql7.freesqldatabase.com";
$username = "sql7782546";
$password = "LlPCLaetc3";
$dbname = "sql7782546";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    // لتبسيط المثال: المستخدم المسؤول username: admin، password: 123456
    // أو تحقق من قاعدة البيانات بدل هذا الشرط
    if ($user === 'admin' && $pass === '123456') {
        $_SESSION['dashboard_loggedin'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "اسم المستخدم أو كلمة المرور غير صحيحة.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <title>تسجيل دخول لوحة AQUA</title>
    <style>
        body { font-family: Tahoma, sans-serif; direction: rtl; background:#f0f0f0; }
        .login-container { max-width: 350px; margin: 100px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px #aaa; }
        input, button { width: 100%; padding: 10px; margin: 10px 0; font-size: 16px; }
        button { background: #1a237e; color: #fff; border: none; cursor: pointer; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="login-container">
    <h2>تسجيل دخول لوحة AQUA</h2>
    <?php if ($error): ?>
        <p class="error"><?=htmlspecialchars($error)?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="اسم المستخدم" required />
        <input type="password" name="password" placeholder="كلمة المرور" required />
        <button type="submit">دخول</button>
    </form>
</div>
</body>
</html>

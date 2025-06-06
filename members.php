<?php
session_start();

if (!isset($_SESSION['dashboard_loggedin']) || $_SESSION['dashboard_loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$servername = "sql7.freesqldatabase.com";
$username = "sql7782546";
$password = "LlPCLaetc3";
$dbname = "sql7782546";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// جلب كل الأعضاء
$sql = "SELECT * FROM users ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <title>الأعضاء - لوحة تحكم AQUA</title>
    <style>
        body { font-family: Tahoma, sans-serif; direction: rtl; background: #eef2f7; margin: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 0 10px #ccc; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: center; }
        th { background: #1a237e; color: #fff; }
        tr:nth-child(even) { background: #f9f9f9; }
        .logout-btn { float: left; background: #e53935; color: #fff; border: none; padding: 10px 15px; cursor: pointer; border-radius: 5px; }
        a { color: #1a237e; text-decoration: none; }
        a:hover { text-decoration: underline; }
        h1 { margin-bottom: 20px; }
    </style>
</head>
<body>

<h1>قائمة الأعضاء</h1>

<form action="logout.php" method="POST" style="float:left;">
    <button type="submit" class="logout-btn">تسجيل خروج</button>
</form>

<table>
    <thead>
        <tr>
            <th>رقم العضو</th>
            <th>الاسم الكامل</th>
            <th>البريد الإلكتروني</th>
            <th>الرقم</th>
            <th>الرصيد (ل.س)</th>
            <th>تاريخ التسجيل</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= number_format($row['balance'], 2) ?></td>
                    <td><?= htmlspecialchars($row['created_at'] ?? 'غير معروف') ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">لا يوجد أعضاء حالياً.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<p><a href="index.php">&laquo; العودة إلى لوحة التحكم</a></p>

</body>
</html>

<?php $conn->close(); ?>

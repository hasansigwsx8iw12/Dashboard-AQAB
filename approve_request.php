<?php
session_start();

if (!isset($_SESSION['dashboard_loggedin']) || $_SESSION['dashboard_loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = intval($_POST['request_id']);
    $user_id = intval($_POST['user_id']);
    $amount = floatval($_POST['amount']);

    $servername = "sql7.freesqldatabase.com";
    $username = "sql7782546";
    $password = "LlPCLaetc3";
    $dbname = "sql7782546";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("فشل الاتصال: " . $conn->connect_error);
    }

    // نبدأ معاملة لعدم التداخل
    $conn->begin_transaction();

    try {
        // تحديث رصيد المستخدم
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $user_id);
        if (!$stmt->execute()) {
            throw new Exception("فشل تحديث الرصيد.");
        }
        $stmt->close();

        // تحديث حالة الطلب إلى "approved"
        $stmt2 = $conn->prepare("UPDATE requests SET status = 'approved', updated_at = NOW() WHERE id = ?");
        $stmt2->bind_param("i", $request_id);
        if (!$stmt2->execute()) {
            throw new Exception("فشل تحديث حالة الطلب.");
        }
        $stmt2->close();

        // اكتمال المعاملة
        $conn->commit();

        // إعادة التوجيه مع رسالة نجاح (يمكن تعديلها حسب الحاجة)
        header("Location: index.php?msg=تم+إضافة+الرصيد+بنجاح");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        echo "حدث خطأ: " . $e->getMessage();
    }

    $conn->close();

} else {
    header("Location: index.php");
    exit;
}

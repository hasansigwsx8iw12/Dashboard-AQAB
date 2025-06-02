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

// جلب الطلبات مع بيانات اسم المستخدم
$sql = "SELECT requests.id, users.fullname, users.balance, requests.amount, requests.receipt_path, requests.status, requests.created_at, requests.user_id
        FROM requests 
        JOIN users ON requests.user_id = users.id 
        ORDER BY requests.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <title>لوحة التحكم AQUA | طلبات شحن الرصيد</title>
    <p><a href="members.php">عرض الأعضاء</a></p>
    <style>
        body { font-family: Tahoma, sans-serif; direction: rtl; background: #eef2f7; margin: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 0 10px #ccc; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: center; }
        th { background: #1a237e; color: #fff; }
        tr:nth-child(even) { background: #f9f9f9; }
        img.receipt { max-width: 100px; max-height: 80px; cursor: pointer; border-radius: 5px; }
        .status-pending { color: orange; font-weight: bold; }
        .status-approved { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
        .logout-btn { float: left; background: #e53935; color: #fff; border: none; padding: 10px 15px; cursor: pointer; border-radius: 5px; }
        button.approve-btn {
            background: #4caf50;
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        button.approve-btn:disabled {
            background: #999;
            cursor: not-allowed;
        }
        /* Modal styles */
        .modal {
          display: none; 
          position: fixed; 
          z-index: 1000; 
          padding-top: 100px; 
          left: 0;
          top: 0;
          width: 100%; 
          height: 100%;
          overflow: auto; 
          background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
          background-color: #fefefe;
          margin: auto;
          padding: 20px;
          border: 1px solid #888;
          width: 300px;
          border-radius: 8px;
          text-align: center;
        }
        .modal-content button {
          margin: 10px 5px 0 5px;
          padding: 10px 20px;
          border: none;
          border-radius: 5px;
          cursor: pointer;
          font-weight: bold;
        }
        .btn-confirm {
          background-color: #4caf50;
          color: white;
        }
        .btn-cancel {
          background-color: #f44336;
          color: white;
        }
    </style>
</head>
<body>

<h1>طلبات شحن الرصيد - لوحة AQUA</h1>
<form action="logout.php" method="POST" style="float:left;">
    <button type="submit" class="logout-btn">تسجيل خروج</button>
</form>

<table>
    <thead>
        <tr>
            <th>رقم الطلب</th>
            <th>اسم المستخدم</th>
            <th>الرصيد الحالي (ر.س)</th>
            <th>المبلغ (ر.س)</th>
            <th>صورة الإيصال</th>
            <th>الحالة</th>
            <th>تاريخ الطلب</th>
            <th>التحكم</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= number_format($row['balance'], 2) ?></td>
                    <td><?= number_format($row['amount'], 2) ?></td>
                    <td>
                        <?php if ($row['receipt_path'] && file_exists($row['receipt_path'])): ?>
                            <a href="<?= htmlspecialchars($row['receipt_path']) ?>" target="_blank">
                                <img src="<?= htmlspecialchars($row['receipt_path']) ?>" alt="صورة الإيصال" class="receipt" />
                            </a>
                        <?php else: ?>
                            لا توجد صورة
                        <?php endif; ?>
                    </td>
                    <td class="status-<?= htmlspecialchars($row['status']) ?>">
                        <?= htmlspecialchars($row['status']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                            <button class="approve-btn" data-request-id="<?= $row['id'] ?>" data-user-id="<?= $row['user_id'] ?>" data-amount="<?= $row['amount'] ?>">موافقة وإضافة رصيد</button>
                        <?php else: ?>
                            <button class="approve-btn" disabled>موافق عليه</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">لا توجد طلبات حالياً.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- نموذج التأكيد (Modal) -->
<div id="approveModal" class="modal">
  <div class="modal-content">
    <h2>تأكيد إضافة الرصيد</h2>
    <p>هل تريد إضافة الرصيد للمستخدم؟</p>
    <p id="modalInfo"></p>
    <form id="approveForm" method="POST" action="approve_request.php">
        <input type="hidden" name="request_id" id="request_id" />
        <input type="hidden" name="user_id" id="user_id" />
        <input type="hidden" name="amount" id="amount" />
        <button type="submit" class="btn-confirm">تأكيد</button>
        <button type="button" class="btn-cancel" id="modalCancel">إلغاء</button>
    </form>
  </div>
</div>

<script>
    // جلب أزرار الموافقة
    const approveButtons = document.querySelectorAll('.approve-btn');
    const modal = document.getElementById('approveModal');
    const modalInfo = document.getElementById('modalInfo');
    const requestIdInput = document.getElementById('request_id');
    const userIdInput = document.getElementById('user_id');
    const amountInput = document.getElementById('amount');
    const modalCancel = document.getElementById('modalCancel');

    approveButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const requestId = btn.getAttribute('data-request-id');
            const userId = btn.getAttribute('data-user-id');
            const amount = btn.getAttribute('data-amount');

            modalInfo.textContent = `رقم الطلب: ${requestId}, المبلغ: ${parseFloat(amount).toFixed(2)} ر.س`;
            requestIdInput.value = requestId;
            userIdInput.value = userId;
            amountInput.value = amount;

            modal.style.display = 'block';
        });
    });

    modalCancel.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // اغلاق المودال عند الضغط خارج المحتوى
    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
</script>

</body>
</html>

<?php $conn->close(); ?>

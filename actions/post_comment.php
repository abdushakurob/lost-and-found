<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_login();

$item_id = (int) ($_POST['item_id'] ?? 0);
$body    = trim($_POST['body'] ?? '');
$uid     = $_SESSION['user_id'];

if ($item_id && $body) {
    $stmt = $pdo->prepare("INSERT INTO comments (item_id, user_id, body) VALUES (?, ?, ?)");
    $stmt->execute([$item_id, $uid, $body]);
}

header("Location: /pages/item.php?id=$item_id&flash=comment");
exit();

<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_login();

$item_id = (int) ($_POST['item_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
$uid     = $_SESSION['user_id'];

if ($item_id && $message) {
    $check = $pdo->prepare("SELECT user_id FROM items WHERE id = ?");
    $check->execute([$item_id]);
    $item = $check->fetch();

    if ($item && $item['user_id'] != $uid) {
        $stmt = $pdo->prepare("INSERT INTO responses (item_id, sender_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$item_id, $uid, $message]);
    }
}

header("Location: /pages/item.php?id=$item_id&flash=response");
exit();

<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_login();

$item_id = (int) ($_POST['item_id'] ?? 0);
$uid     = $_SESSION['user_id'];

$check = $pdo->prepare("SELECT user_id FROM items WHERE id = ?");
$check->execute([$item_id]);
$item = $check->fetch();

if ($item && $item['user_id'] == $uid) {
    $stmt = $pdo->prepare("UPDATE items SET status = 'resolved' WHERE id = ?");
    $stmt->execute([$item_id]);
}

header("Location: /pages/item.php?id=$item_id&flash=resolved");
exit();

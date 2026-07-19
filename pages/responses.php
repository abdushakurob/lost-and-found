<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_login();

$uid  = $_SESSION['user_id'];
$stmt = $pdo->prepare(
    "SELECT r.*, u.name AS sender_name, i.item_name, i.type, i.id AS item_id
     FROM responses r
     JOIN users u ON r.sender_id = u.id
     JOIN items i ON r.item_id = i.id
     WHERE i.user_id = ?
     ORDER BY r.created_at DESC"
);
$stmt->execute([$uid]);
$inbox = $stmt->fetchAll();

function initials_from_name(string $name): string {
    $p = explode(' ', trim($name));
    return strtoupper(substr($p[0], 0, 1) . (isset($p[1]) ? substr($p[1], 0, 1) : ''));
}

$page_title  = 'Inbox';
$active_page = 'inbox';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header">
  <h1>Inbox</h1>
  <p>Private messages sent to you about your posted items</p>
</div>

<?php if (empty($inbox)): ?>
  <div class="empty-state">
    <div class="empty-icon">Empty</div>
    <h3>No messages yet</h3>
    <p>When someone sends you a private response about one of your items, it will show up here.</p>
  </div>
<?php else: ?>
  <div class="list-gap">
    <?php foreach ($inbox as $r): ?>
      <div class="response-item">
        <div class="user-avatar"><?php echo initials_from_name($r['sender_name']); ?></div>
        <div class="response-content">
          <div class="response-sender"><?php echo htmlspecialchars($r['sender_name']); ?></div>
          <div class="response-item-ref">
            Re: <a href="/pages/item.php?id=<?php echo $r['item_id']; ?>">
              <?php echo htmlspecialchars($r['item_name']); ?>
            </a>
            <span class="badge badge-<?php echo $r['type']; ?>" style="margin-left:6px;"><?php echo strtoupper($r['type']); ?></span>
          </div>
          <div class="response-message"><?php echo nl2br(htmlspecialchars($r['message'])); ?></div>
        </div>
        <div class="response-time"><?php echo date('M j, g:ia', strtotime($r['created_at'])); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>

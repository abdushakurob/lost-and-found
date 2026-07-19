<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_login();

$uid  = $_SESSION['user_id'];
$stmt = $pdo->prepare(
    "SELECT i.*, 
            (SELECT COUNT(*) FROM comments c WHERE c.item_id = i.id) AS comment_count,
            (SELECT COUNT(*) FROM responses r WHERE r.item_id = i.id) AS response_count
     FROM items i
     WHERE i.user_id = ?
     ORDER BY i.created_at DESC"
);
$stmt->execute([$uid]);
$my_items = $stmt->fetchAll();

$page_title  = 'My Posts';
$active_page = 'my_posts';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header-row">
  <div class="page-header" style="margin-bottom:0">
    <h1>My Posts</h1>
    <p><?php echo count($my_items); ?> item<?php echo count($my_items) !== 1 ? 's' : ''; ?> reported</p>
  </div>
  <a href="/pages/post_item.php" class="btn btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
    Report Item
  </a>
</div>

<?php if (empty($my_items)): ?>
  <div class="empty-state">
    <h3>You haven't posted anything yet</h3>
    <p>Report a lost or found item to get started.</p>
    <a href="/pages/post_item.php" class="btn btn-primary">Report Item</a>
  </div>
<?php else: ?>
  <div class="list-gap">
    <?php foreach ($my_items as $item): ?>
      <a href="/pages/item.php?id=<?php echo $item['id']; ?>" class="item-card" style="display:flex; align-items:flex-start; gap:16px;">
        <div style="flex:1;">
          <div class="flex gap-2 items-center mb-2">
            <span class="badge badge-<?php echo $item['type']; ?>"><?php echo strtoupper($item['type']); ?></span>
            <span class="badge badge-<?php echo $item['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $item['status'])); ?></span>
          </div>
          <h3 style="font-size:15px; margin-bottom:4px;"><?php echo htmlspecialchars($item['item_name']); ?></h3>
          <p class="text-muted" style="font-size:13px; margin-bottom:10px;"><?php echo htmlspecialchars($item['location']); ?> · <?php echo date('M j, Y', strtotime($item['date_reported'])); ?></p>
          <div class="flex gap-3">
            <span class="item-meta-row">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              <?php echo $item['comment_count']; ?> comment<?php echo $item['comment_count'] != 1 ? 's' : ''; ?>
            </span>
            <span class="item-meta-row">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:12px;height:12px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              <?php echo $item['response_count']; ?> private response<?php echo $item['response_count'] != 1 ? 's' : ''; ?>
            </span>
          </div>
        </div>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:var(--text-muted);flex-shrink:0;margin-top:2px;"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>

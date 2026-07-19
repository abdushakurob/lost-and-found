<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_login();

$uid = $_SESSION['user_id'];

$total    = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
$lost     = $pdo->query("SELECT COUNT(*) FROM items WHERE type = 'lost'")->fetchColumn();
$found    = $pdo->query("SELECT COUNT(*) FROM items WHERE type = 'found'")->fetchColumn();
$resolved = $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'resolved'")->fetchColumn();

$my_stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE user_id = ?");
$my_stmt->execute([$uid]);
$my_count = $my_stmt->fetchColumn();

$recent = $pdo->query(
    "SELECT i.*, u.name AS poster_name
     FROM items i JOIN users u ON i.user_id = u.id
     ORDER BY i.created_at DESC LIMIT 6"
)->fetchAll();

$page_title  = 'Dashboard';
$active_page = 'dashboard';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header-row">
  <div class="page-header" style="margin-bottom:0">
    <h1>Good to see you, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?></h1>
    <p>Here's what's happening on recov.cv</p>
  </div>
  <a href="/pages/post_item.php" class="btn btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
    Report Item
  </a>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div>
      <div class="stat-label">Total Items</div>
      <div class="stat-value"><?php echo $total; ?></div>
    </div>
    <div class="stat-icon blue"></div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-label">Lost</div>
      <div class="stat-value"><?php echo $lost; ?></div>
    </div>
    <div class="stat-icon red"></div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-label">Found</div>
      <div class="stat-value"><?php echo $found; ?></div>
    </div>
    <div class="stat-icon green"></div>
  </div>
  <div class="stat-card">
    <div>
      <div class="stat-label">Resolved</div>
      <div class="stat-value"><?php echo $resolved; ?></div>
    </div>
    <div class="stat-icon yellow"></div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <span class="card-title">Recent Items</span>
    <a href="/pages/browse.php" class="btn btn-secondary btn-sm">View all</a>
  </div>
  <div class="card-body">
    <?php if (empty($recent)): ?>
      <div class="empty-state">
        <h3>Nothing here yet</h3>
        <p>Be the first to report a lost or found item.</p>
        <a href="/pages/post_item.php" class="btn btn-primary">Report Item</a>
      </div>
    <?php else: ?>
      <div class="items-grid">
        <?php foreach ($recent as $item): ?>
          <a href="/pages/item.php?id=<?php echo $item['id']; ?>" class="item-card">
            <div class="item-card-top">
              <span class="badge badge-<?php echo $item['type']; ?>"><?php echo strtoupper($item['type']); ?></span>
              <span class="badge badge-<?php echo $item['status']; ?>"><?php echo ucfirst($item['status']); ?></span>
            </div>
            <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
            <p class="item-desc"><?php echo htmlspecialchars($item['description']); ?></p>
            <div class="item-meta">
              <span class="item-meta-row">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <?php echo htmlspecialchars($item['location']); ?>
              </span>
              <span class="item-meta-row">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php echo date('M j', strtotime($item['date_reported'])); ?>
              </span>
              <span class="item-meta-row">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <?php echo htmlspecialchars($item['poster_name']); ?>
              </span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

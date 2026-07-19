<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_login();

$filter = $_GET['filter'] ?? 'all';

$where = match ($filter) {
    'lost'     => "WHERE i.type = 'lost'",
    'found'    => "WHERE i.type = 'found'",
    'open'     => "WHERE i.status = 'open'",
    'resolved' => "WHERE i.status = 'resolved'",
    default    => '',
};

$items = $pdo->query(
    "SELECT i.*, u.name AS poster_name
     FROM items i JOIN users u ON i.user_id = u.id
     $where
     ORDER BY i.created_at DESC"
)->fetchAll();

$page_title  = 'Browse Items';
$active_page = 'browse';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header-row">
  <div class="page-header" style="margin-bottom:0">
    <h1>Browse Items</h1>
    <p><?php echo count($items); ?> item<?php echo count($items) !== 1 ? 's' : ''; ?> found</p>
  </div>
  <a href="/pages/post_item.php" class="btn btn-primary">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
    Report Item
  </a>
</div>

<div class="filter-bar">
  <?php
  $filters = ['all' => 'All', 'lost' => 'Lost', 'found' => 'Found', 'open' => 'Open', 'resolved' => 'Resolved'];
  foreach ($filters as $key => $label):
  ?>
    <a href="?filter=<?php echo $key; ?>"
       class="filter-btn <?php echo $filter === $key ? 'active' : ''; ?>">
      <?php echo $label; ?>
    </a>
  <?php endforeach; ?>
</div>

<?php if (empty($items)): ?>
  <div class="empty-state">
    <h3>No items here</h3>
    <p>Nothing matches this filter yet.</p>
    <a href="browse.php" class="btn btn-secondary">Clear filter</a>
  </div>
<?php else: ?>
  <div class="items-grid">
    <?php foreach ($items as $item): ?>
      <a href="/pages/item.php?id=<?php echo $item['id']; ?>" class="item-card">
        <div class="item-card-top">
          <span class="badge badge-<?php echo $item['type']; ?>"><?php echo strtoupper($item['type']); ?></span>
          <span class="badge badge-<?php echo $item['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $item['status'])); ?></span>
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
            <?php echo date('M j, Y', strtotime($item['date_reported'])); ?>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>

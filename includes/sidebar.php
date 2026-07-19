<?php
if (!isset($active_page)) $active_page = '';

$name_parts = explode(' ', trim($_SESSION['user_name'] ?? 'User'));
$initials   = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));
?>
<aside class="sidebar">
  <div class="sidebar-logo">
    <a href="/pages/dashboard.php">
      <div class="logo-icon">L</div>
      <div class="logo-text">lostandfound</div>
    </a>
  </div>

  <nav class="sidebar-nav">
    <span class="nav-label">Main</span>

    <a href="/pages/dashboard.php" class="nav-item <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>

    <a href="/pages/browse.php" class="nav-item <?php echo $active_page === 'browse' ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      Browse Items
    </a>

    <span class="nav-label">My Account</span>

    <a href="/pages/post_item.php" class="nav-item <?php echo $active_page === 'post' ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
      Report Item
    </a>

    <a href="/pages/my_posts.php" class="nav-item <?php echo $active_page === 'my_posts' ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      My Posts
    </a>

    <a href="/pages/responses.php" class="nav-item <?php echo $active_page === 'inbox' ? 'active' : ''; ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      Inbox
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="/logout.php" class="nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </a>
  </div>
</aside>

<div class="main">
  <header class="topbar">
    <div class="topbar-title"><?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?></div>
    <div class="topbar-right">
      <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></span>
      <div class="user-avatar"><?php echo $initials; ?></div>
    </div>
  </header>
  <div class="content">

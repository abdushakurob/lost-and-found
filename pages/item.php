<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_login();

$uid = $_SESSION['user_id'];

$item_id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT i.*, u.name AS poster_name
     FROM items i JOIN users u ON i.user_id = u.id
     WHERE i.id = ?"
);
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: /pages/browse.php");
    exit();
}

$is_owner = $item['user_id'] == $uid;

$comments = $pdo->prepare(
    "SELECT c.*, u.name AS commenter_name
     FROM comments c JOIN users u ON c.user_id = u.id
     WHERE c.item_id = ?
     ORDER BY c.created_at ASC"
);
$comments->execute([$item_id]);
$comments = $comments->fetchAll();

$responses = [];
if ($is_owner) {
    $res_stmt = $pdo->prepare(
        "SELECT r.*, u.name AS sender_name
         FROM responses r JOIN users u ON r.sender_id = u.id
         WHERE r.item_id = ?
         ORDER BY r.created_at DESC"
    );
    $res_stmt->execute([$item_id]);
    $responses = $res_stmt->fetchAll();
}

$flash = $_GET['flash'] ?? '';

function initials_from(string $name): string {
    $p = explode(' ', trim($name));
    return strtoupper(substr($p[0], 0, 1) . (isset($p[1]) ? substr($p[1], 0, 1) : ''));
}

$page_title  = htmlspecialchars($item['item_name']);
$active_page = 'browse';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<?php if ($flash === 'comment'): ?>
  <div class="alert alert-success">Your comment was posted.</div>
<?php elseif ($flash === 'response'): ?>
  <div class="alert alert-success">Your private message was sent to the poster.</div>
<?php elseif ($flash === 'resolved'): ?>
  <div class="alert alert-success">This item has been marked as resolved.</div>
<?php endif; ?>

<div class="detail-layout">

  <!-- Left column -->
  <div>
    <div class="item-detail-card">
      <div class="flex gap-2 items-center">
        <span class="badge badge-<?php echo $item['type']; ?>"><?php echo strtoupper($item['type']); ?></span>
        <span class="badge badge-<?php echo $item['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $item['status'])); ?></span>
      </div>

      <h1 class="item-title"><?php echo htmlspecialchars($item['item_name']); ?></h1>
      <p class="item-description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>

      <div class="item-facts">
        <div>
          <div class="item-fact-label">Location</div>
          <div class="item-fact-value"><?php echo htmlspecialchars($item['location']); ?></div>
        </div>
        <div>
          <div class="item-fact-label">Date</div>
          <div class="item-fact-value"><?php echo date('F j, Y', strtotime($item['date_reported'])); ?></div>
        </div>
        <div>
          <div class="item-fact-label">Type</div>
          <div class="item-fact-value"><?php echo ucfirst($item['type']); ?> item</div>
        </div>
        <div>
          <div class="item-fact-label">Status</div>
          <div class="item-fact-value"><?php echo ucfirst(str_replace('_', ' ', $item['status'])); ?></div>
        </div>
      </div>

      <div class="poster-row">
        <div class="user-avatar" style="width:38px;height:38px;font-size:13px;">
          <?php echo initials_from($item['poster_name']); ?>
        </div>
        <div class="poster-info">
          <div class="poster-label">Posted by</div>
          <div class="poster-name"><?php echo htmlspecialchars($item['poster_name']); ?></div>
        </div>

        <?php if ($is_owner && $item['status'] !== 'resolved'): ?>
          <form action="/actions/resolve_item.php" method="POST" style="margin-left:auto;">
            <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
            <button class="btn btn-success btn-sm" type="submit">Mark as Resolved</button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <!-- Comment Thread -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">Comments (<?php echo count($comments); ?>)</span>
      </div>
      <div class="card-body">
        <?php if (empty($comments)): ?>
          <p class="text-muted" style="text-align:center; padding:20px 0;">No comments yet. Be the first to leave one.</p>
        <?php else: ?>
          <div class="comment-list mb-4">
            <?php foreach ($comments as $c): ?>
              <div class="comment">
                <div class="comment-avatar"><?php echo initials_from($c['commenter_name']); ?></div>
                <div class="comment-bubble">
                  <div class="comment-meta">
                    <span class="comment-name"><?php echo htmlspecialchars($c['commenter_name']); ?></span>
                    <span class="comment-time"><?php echo date('M j, g:ia', strtotime($c['created_at'])); ?></span>
                  </div>
                  <div class="comment-body"><?php echo nl2br(htmlspecialchars($c['body'])); ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="divider"></div>

        <form action="/actions/post_comment.php" method="POST">
          <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
          <div class="form-group">
            <label class="form-label">Leave a public comment</label>
            <textarea class="form-control" name="body" placeholder="Share any information you have about this item..." required></textarea>
            <div class="form-hint">Your name (<?php echo htmlspecialchars($_SESSION['user_name']); ?>) will be shown with your comment.</div>
          </div>
          <button class="btn btn-primary" type="submit">Post Comment</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Right column -->
  <div>
    <?php if ($is_owner): ?>
      <div class="side-panel">
        <div class="side-panel-header">
          <h3>Private Responses</h3>
          <p>Only you can see these messages</p>
        </div>
        <div class="side-panel-body">
          <?php if (empty($responses)): ?>
            <p class="text-muted" style="font-size:13px;">No one has responded privately yet.</p>
          <?php else: ?>
            <div class="list-gap">
              <?php foreach ($responses as $r): ?>
                <div style="border:1px solid var(--border); border-radius:8px; padding:14px;">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="user-avatar" style="width:30px;height:30px;font-size:11px;">
                      <?php echo initials_from($r['sender_name']); ?>
                    </div>
                    <div>
                      <div style="font-size:13px; font-weight:600;"><?php echo htmlspecialchars($r['sender_name']); ?></div>
                      <div class="comment-time"><?php echo date('M j, g:ia', strtotime($r['created_at'])); ?></div>
                    </div>
                  </div>
                  <p style="font-size:13px; color:var(--text-muted); line-height:1.6;"><?php echo nl2br(htmlspecialchars($r['message'])); ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

    <?php elseif ($item['status'] !== 'resolved'): ?>
      <div class="side-panel">
        <div class="side-panel-header">
          <h3>Contact the Poster</h3>
          <p>Send a private message to <?php echo htmlspecialchars($item['poster_name']); ?></p>
        </div>
        <div class="side-panel-body">
          <form action="/actions/post_response.php" method="POST">
            <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
            <div class="form-group">
              <label class="form-label">Your message</label>
              <textarea class="form-control"
                        name="message"
                        placeholder="<?php echo $item['type'] === 'lost' ? 'Describe where/how you found it...' : 'Explain why this item is yours...'; ?>"
                        required></textarea>
              <div class="form-hint">Your name (<?php echo htmlspecialchars($_SESSION['user_name']); ?>) will be visible to the poster.</div>
            </div>
            <button class="btn btn-primary btn-full" type="submit">Send Message</button>
          </form>
        </div>
      </div>

    <?php else: ?>
      <div class="side-panel">
        <div class="side-panel-body" style="text-align:center; padding:30px 20px;">
          <h3 style="font-size:15px; font-weight:600; margin-bottom:6px;">Item Resolved</h3>
          <p class="text-muted">This item has been marked as resolved by the poster.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

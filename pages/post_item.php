<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';
require_login();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid           = $_SESSION['user_id'];
    $item_name     = trim($_POST['item_name']     ?? '');
    $description   = trim($_POST['description']   ?? '');
    $type          = $_POST['type']          ?? '';
    $location      = trim($_POST['location']      ?? '');
    $date_reported = $_POST['date_reported'] ?? '';

    if (!in_array($type, ['lost', 'found'])) {
        $error = "Please select Lost or Found.";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO items (user_id, item_name, description, type, location, date_reported)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        if ($stmt->execute([$uid, $item_name, $description, $type, $location, $date_reported])) {
            $new_id = $pdo->lastInsertId();
            header("Location: /pages/item.php?id=$new_id&flash=posted");
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}

$page_title  = 'Report Item';
$active_page = 'post';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="page-header">
  <h1>Report an Item</h1>
  <p>Provide as much detail as possible — it helps others identify the item.</p>
</div>

<div class="card" style="max-width:680px;">
  <div class="card-body">
    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="post_item.php" method="POST">

      <div class="form-group">
        <label class="form-label">Item type</label>
        <div class="radio-group">
          <label class="radio-card">
            <input type="radio" name="type" value="lost" required
                   <?php echo ($_POST['type'] ?? '') === 'lost' ? 'checked' : ''; ?>>
            Lost
          </label>
          <label class="radio-card">
            <input type="radio" name="type" value="found"
                   <?php echo ($_POST['type'] ?? '') === 'found' ? 'checked' : ''; ?>>
            Found
          </label>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Item name</label>
        <input class="form-control" type="text" name="item_name"
               placeholder="e.g. Blue backpack, iPhone 14, Student ID card"
               value="<?php echo htmlspecialchars($_POST['item_name'] ?? ''); ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea class="form-control" name="description"
                  placeholder="Describe the item in detail — colour, size, distinguishing marks, brand, etc."
                  required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Location</label>
          <input class="form-control" type="text" name="location"
                 placeholder="e.g. Library Block B, Main Gate, Hostel A"
                 value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Date</label>
          <input class="form-control" type="date" name="date_reported"
                 value="<?php echo htmlspecialchars($_POST['date_reported'] ?? date('Y-m-d')); ?>" required>
        </div>
      </div>

      <div class="flex gap-2" style="margin-top:8px;">
        <button class="btn btn-primary" type="submit">Submit Report</button>
        <a href="/pages/browse.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

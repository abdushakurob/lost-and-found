<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>lostandfound — Recover what matters</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/public/css/style.css">
</head>
<body class="landing-page-active">

  <header class="landing-header">
    <a href="/" class="landing-brand">lostandfound</a>
    <nav class="landing-nav-links">
      <a href="#how" class="landing-link-item" style="display: inline-block;">How it works</a>
      <a href="/pages/login.php">Log in</a>
      <a href="/pages/register.php" class="btn-pill-dark">Get started</a>
    </nav>
  </header>

  <main class="landing-container">
    <section class="landing-hero-section">
      <p class="hero-eyebrow">Lost. Found. Returned.</p>
      <h1 class="hero-headline">Recover what matters</h1>
      <p class="hero-subtext">
        Post lost or found items, leave public tips, and message owners privately. Real names attached to every post.
      </p>
      <div class="cta-buttons">
        <a href="/pages/register.php" class="btn-cta-dark">START FOR FREE</a>
        <a href="/pages/browse.php" class="btn-cta-outline">BROWSE ITEMS</a>
      </div>
    </section>

    <section id="how" class="steps-section">
      <div class="step-card">
        <div class="step-card-inner">
          <div class="step-badge" style="background-color: #0f1b3d;">1</div>
          <div class="step-content">
            <h3>POST AN ITEM</h3>
            <p>Report a lost wallet, a found phone, a stray key. Thirty seconds and a real name are all it takes.</p>
          </div>
        </div>
      </div>

      <div class="step-card">
        <div class="step-card-inner">
          <div class="step-badge" style="background-color: #3b6fa0;">2</div>
          <div class="step-content">
            <h3>PUBLIC COMMENTS</h3>
            <p>Anyone can leave a tip or a lead. Every comment shows a real name. No anonymous drive-bys.</p>
          </div>
        </div>
      </div>

      <div class="step-card">
        <div class="step-card-inner">
          <div class="step-badge" style="background-color: #1e3a5f;">3</div>
          <div class="step-content">
            <h3>PRIVATE REPLIES</h3>
            <p>Reach the owner directly with a private message. Only the poster ever sees it.</p>
          </div>
        </div>
      </div>
    </section>

    <footer class="landing-footer">
      <span>&copy; <?php echo date('Y'); ?> lostandfound</span>
-      <div>
        <a href="#">Terms</a>
        <a href="#">Privacy</a>
        <a href="#">Contact</a>
      </div>
    </footer>
  </main>

</body>
</html>


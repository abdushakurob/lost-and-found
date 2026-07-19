# lostandfound — Lost and Found Portal Documentation

This document explains how the lostandfound application is structured: how the files connect to each other, how data moves through the app, and where everything is stored. It starts with the overall flow, then walks through each part in order.

---

## 1. Application Flow Overview

Every time someone uses the app, whether they're just opening the site or posting a comment, the request moves through the same four stops:

```
User opens the site or clicks something
      │
      ▼
index.php (checks if user is logged in)
      │
      ├── Not logged in ──► landing.php ──► register.php / login.php
      │
      └── Logged in ──► dashboard.php
                             │
                             ├── browse.php ──► item.php?id=X
                             ├── post_item.php
                             ├── my_posts.php
                             └── responses.php
                             │
                             ▼
                    (submitting any form)
                             │
                             ▼
                    actions/*.php (saves the data)
                             │
                             ▼
                    sends user back to the relevant page
```

In short, four things are happening, in this order:

1. **A check runs first** to see who the user is and what they're allowed to see.
2. **A page is shown**, displaying whatever information is relevant, pulled from storage.
3. **If the user submits something** (a comment, a new report, a reply), that gets handed off to a separate file whose only job is to save it.
4. **Everything that gets saved lands in one place**: the storage file that holds all the app's data.

The rest of this document goes through these four stops one at a time, in the order listed above.

---

## 2. Checking Who's Logged In

Before anything is shown to a user, the app checks whether they're logged in. This check happens through something called a session.

Here's the idea: once someone logs in, the app remembers them for as long as they're on the site, kind of like being handed a name tag when you walk into a building. As they move from page to page, the app checks that name tag to know it's still them, without asking them to log in again on every click.

`index.php` is the very first file a visitor's request touches. It doesn't show any content itself, it just checks: is this person wearing a name tag or not?

**If not logged in:**
`index.php` → `landing.php` (welcome screen) → `register.php` or `login.php`

**If logged in:**
`index.php` → `dashboard.php`

Keeping this check in one single file means every page in the app relies on the same rule, instead of each page having to figure it out separately.

`includes/auth.php` is where this logic lives:

```php
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function require_login(): void {
    if (!is_logged_in()) {
        header("Location: /pages/login.php");
        exit();
    }
}
```

`is_logged_in()` is simply asking "does this person have a name tag?" `require_login()` is the rule that any private page (like the dashboard or inbox) uses: if there's no name tag, send them straight to the login page instead of showing anything.

**On passwords:** the app never stores anyone's actual password. When someone registers, their password is scrambled into unreadable text before it's saved. When they log in, the app scrambles what they typed the same way and checks if it matches. This means even looking directly at the storage file would not reveal anyone's real password.

---

## 3. The Pages Users See

Once someone is confirmed as logged in, they land on pages. This is everything the user actually sees and clicks through.

```
[dashboard.php] (overview)
   ├── Browse Items  → [browse.php] → click a card → [item.php?id=X]
   ├── Report Item   → [post_item.php]
   ├── My Posts      → [my_posts.php]
   └── Inbox         → [responses.php]
```

- **dashboard.php**: the main hub after logging in.
- **browse.php**: a scrollable feed of everything reported lost or found.
- **item.php**: the detail view for one specific report.
- **post_item.php**: the form for reporting something lost or found.
- **my_posts.php**: a filtered view showing only the current user's own reports.
- **responses.php**: the inbox, showing private messages sent about the user's items.

One thing worth explaining: `item.php?id=X`. When someone clicks on an item in the feed, the page address includes a reference number for that exact item, for example `item.php?id=57`. The page reads that number and pulls up the matching report from storage. This is how one single page is able to display any of the thousands of reports in the system, rather than needing a separate page built for every single item.

Pages also contain the forms people fill out, like the comment box or the "report an item" form. But the pages themselves don't save anything. That job belongs to the next layer.

---

## 4. Saving What Users Submit

Whenever someone submits a form, whether that's leaving a comment, posting a report, or sending a reply, the information is handed off to a separate file whose only job is to save it and send the user back to where they were.

For example, `actions/post_comment.php` handles saving a comment:

```php
$stmt = $pdo->prepare("INSERT INTO comments (item_id, user_id, body) VALUES (?, ?, ?)");
$stmt->execute([$item_id, $_SESSION['user_id'], $body]);
header("Location: /pages/item.php?id=" . $item_id . "&flash=comment");
exit();
```

The flow is: `item.php` shows the comment form → the comment gets submitted to `post_comment.php` → that file saves it to storage → the user is sent back to `item.php`, now showing their new comment along with a small success message.

This separation matters for a practical reason: if saving happened directly on the page someone is viewing, refreshing the page right after submitting could accidentally submit the same comment twice. By saving first and then redirecting to a fresh page, that risk goes away.

The `&flash=comment` part at the end of the redirect is just a temporary tag the app uses to know it should display a "comment posted" message. It disappears after that one page load and is never actually stored anywhere.

---

## 5. Connecting to Storage

Any file that needs to read or save data first has to open a connection to the storage file. This connection is set up once, in `config/db.php`, and reused everywhere else.

```php
try {
    $db_path = __DIR__ . '/../storage/database.sqlite';
    $pdo = new PDO("sqlite:" . $db_path);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
```

In plain terms: this file locates the storage file and opens a line to it, so the rest of the app can read from it or write to it. If that connection somehow fails, for example the storage file goes missing, the app stops itself cleanly and shows an error, rather than continuing on and risking broken or corrupted data.

---

## 6. Where Everything Is Actually Stored

All of the app's data lives in a single file: `storage/database.sqlite`. Inside it, data is organized into four separate sections, similar to four drawers in a filing cabinet, each holding one kind of record:

- **users**: every account that's been registered
- **items**: every lost or found report
- **comments**: public tips left underneath a report
- **responses**: private messages sent between the finder and the item's owner

```sql
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    item_name TEXT NOT NULL,
    description TEXT NOT NULL,
    type TEXT CHECK(type IN ('lost', 'found')) NOT NULL,
    location TEXT NOT NULL,
    date_reported DATE NOT NULL,
    status TEXT CHECK(status IN ('open', 'resolved')) DEFAULT 'open',
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    body TEXT NOT NULL,
    FOREIGN KEY(item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS responses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id INTEGER NOT NULL,
    sender_id INTEGER NOT NULL,
    message TEXT NOT NULL,
    FOREIGN KEY(item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY(sender_id) REFERENCES users(id) ON DELETE CASCADE
);
```

A few things worth knowing about how this storage keeps itself organized and clean:

**Every record gets its own ID number automatically.** So even if two users are both named "John Doe," the app never confuses them, because one is stored as user #14 and the other as user #212. Whenever one drawer needs to reference something in another drawer (like an item needing to know who posted it), it does so using this ID number rather than a name.

**Some fields only accept specific values.** For example, an item's type can only be saved as `lost` or `found`, nothing else. If something else were ever submitted, storage simply refuses to save it. This acts as a safety net that keeps bad or incomplete data from ever making it in.

**Deleting an account cleans up after itself.** If a user deletes their account, all their reports, comments, and messages are automatically deleted along with it. Nothing gets left behind pointing to a user that no longer exists.

---

## 7. Directory Map

| Path | Purpose |
|---|---|
| `index.php` | First file reached; checks login status and directs the user |
| `landing.php` | Public welcome screen |
| `logout.php` | Logs the user out |
| `config/db.php` | Opens the connection to storage |
| `storage/database.sqlite` | The single file holding all app data |
| `public/css/style.css` | Site-wide styling |
| `includes/` | Shared pieces reused across pages (header, sidebar, footer) |
| `pages/` | Everything the user sees and interacts with |
| `actions/` | Files that save data submitted through forms |
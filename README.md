# lostandfound — Lost and Found Portal Documentation

This document explains how the lostandfound application is built, what each file does, and how all the pieces connect. It is written in simple terms so that anyone on the team can understand the structure.

---

## 1. How the App Works (The Lifecycle)

Every time someone visits the site or clicks a button, the application follows a set path:

```
User visits the site or clicks a button
      │
      ▼
1. The Check: index.php (checks if you are logged in)
      │
      ├── Not logged in ──► landing.php (welcome screen) ──► register.php / login.php
      │
      └── Logged in ──► dashboard.php (main member area)
                            ├── browse.php (lists all reported items)
                            │     └── item.php?id=X (displays a specific item details)
                            ├── post_item.php (form to report lost/found items)
                            ├── my_posts.php (user's personal reports list)
                            └── responses.php (private message inbox)
```

When a user submits a form (like writing a comment or sending a message):
1. The browser sends the details to a quiet, invisible processor file in the `actions/` folder.
2. The processor file saves the information to the database.
3. The processor file redirects the user back to the page they were viewing, which now shows the updated information.

---

## 2. File-by-File Guide

Here is a list of every file in the project and exactly what it does:

### Main Files
* **index.php**: The entrance controller. It checks if you are logged in. If yes, it redirects you to the dashboard. If no, it redirects you to the welcome page.
* **landing.php**: The welcome homepage with explanations and log in / get started buttons.
* **logout.php**: Clears the system memory of who is logged in and sends you back to the welcome page.

### Folder: config/
* **db.php**: Opens the connection to the database so files can read and save data. If the database file doesn't exist yet, this file automatically creates it.

### Folder: storage/
* **database.sqlite**: The single file where all system data is stored (users, reported items, comments, and messages).

### Folder: public/css/
* **style.css**: The single file that holds the appearance rules (colors, fonts, sizes, and layout spacing) for the entire site.

### Folder: includes/
These files contain shared code layouts that are reused across different pages to avoid writing the same code twice:
* **auth.php**: The gatekeeper. It checks if a user is logged in and blocks logged-out visitors from viewing protected member pages.
* **head.php**: The top header layout that sets up the HTML code, imports the Inter font, and applies the `style.css` stylesheet.
* **sidebar.php**: The navigation panel on the left of the dashboard and the top status bar.
* **footer.php**: The bottom layout that neatly closes page wrappers.

### Folder: pages/
These are the screens that users see in their browser:
* **register.php**: The form used to sign up for a new account.
* **login.php**: The form used to log in.
* **dashboard.php**: The homepage for logged-in users. It displays stats and a list of the 6 most recently reported items.
* **browse.php**: A list of all reported items on the site, with filters to show only lost, only found, or resolved items.
* **item.php**: The detailed page for a single item.
* **post_item.php**: The form used to report a lost or found item.
* **my_posts.php**: A list showing only the items that the logged-in user reported.
* **responses.php**: The private inbox displaying direct messages received from other users.

### Folder: actions/
These are invisible processor files. They do not display screens; they run in the background when a user submits a form, save the data, and redirect the user:
* **post_comment.php**: Saves a public comment left under an item.
* **post_response.php**: Saves a private message sent to an item poster.
* **resolve_item.php**: Marks an item status as resolved.

---

## 3. How Login and Sign Up Work

The system uses user accounts to ensure accountability:

* **Signing Up (`register.php`)**: When you fill out the registration form with your name, email, and password, the system checks if the email is already in use. To protect your password, the system scrambles it into an unreadable string (a hash) before saving it to the database file.
* **Logging In (`login.php`)**: When you enter your email and password, the system fetches your account details from the database. It scrambles the password you just typed and checks if it matches the scrambled password in the database.
* **The Visitor Pass (Sessions)**: On successful login, the server writes your user ID and name to a temporary memory file called a "Session" and gives your browser a tracking key. The system uses this key to remember who you are as you move from page to page.

---

## 4. How Pages Fetch and Display Items

To display items, pages must ask the database for information:

* **Fetching Lists (The Feed)**: When you open the dashboard (`dashboard.php`) or the browse feed (`browse.php`), the file sends a query to the database asking for a list of reported items, along with the names of the users who posted them. The page then loops through the results and formats them as cards.
* **Displaying a Specific Item (`item.php?id=X`)**: Building a separate, physical page for every reported item is impractical. Instead, the application uses a single template file called `item.php`. 
  
  When you click an item card, the browser is sent to a web link that includes a reference number, for example: `item.php?id=5`. The `?id=5` part is called a **query parameter** (or address query). It tells the browser and the web server which exact database record you want to look at. 
  
  The code inside `item.php` reads this reference number directly from the address bar, contacts the database, asks for the item record that matches ID number 5, and displays its details. If you change the address bar number to `item.php?id=12`, the page automatically queries the database for item number 12 and displays its details instead. This allows one file to dynamically display any number of items.

---

## 5. How the App Saves Information

To prevent double-posting errors (like submitting the same comment twice if a user refreshes the page), the app separates pages from database transactions:

* **The Input Form**: The user types a comment or message on `item.php` and clicks "Submit".
* **The Background Action**: The browser sends the typed text to an action handler (like `actions/post_comment.php`). This file has no visual layout; it connects to the database, writes the comment, and redirects the browser back to `item.php?id=X`.
* **The Success Message**: The action handler appends a code to the URL (like `&flash=comment`). The detail page reads this code, displays a "Comment posted" alert box, and removes the code from the address bar.

---

## 6. How the App Talks to the Database

The database is where all user profiles, reported items, comments, and messages are stored.

* **The Connection Setup (`config/db.php`)**: To read or write information, a file must import the connection script. This script opens a path to the database file `storage/database.sqlite`. If the connection fails, the script displays an error message and stops the application to prevent data corruption.
* **Automated Creation**: If the database file is missing (such as when launching the app for the first time), the connection script automatically creates the file and sets up the four lists (users, items, comments, and responses).
* **Cascade Deletes**: The lists are linked together. If a user deletes their account, the database automatically deletes all of their reported items, public comments, and private messages.
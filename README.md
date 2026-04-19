# 🔐 Vault Breakout — CSC 4370 Group Project 2

**Team:** Rohan Khandelwal & Mohit Kokane  
**Course:** CSC 4370 · Web Programming · Spring 2026  
**Topic:** 04 — The PHP Vault (Escape Room)

---

## Project Overview

Vault Breakout is a dynamic PHP-based escape room game where players solve 3 cryptographic puzzles to "break out" of a secure vault. The application demonstrates server-side PHP logic, session management, flat-file authentication, form processing, and a live leaderboard.

---

## 5 Core Requirements Coverage

| # | Requirement | Implementation |
|---|-------------|----------------|
| 1 | **Sessions & Cookies / Leaderboard** | `session_start()` on every page; `$_SESSION` tracks score, room, hints, attempts; `setcookie()` for cross-visit persistence; leaderboard sorted from flat file |
| 2 | **Form Processing** | All puzzle answers & auth forms use POST; `htmlspecialchars()` + `filter_input()` on all inputs; sticky values on error re-render |
| 3 | **Login & Registration** | `register.php` creates accounts in `data/users.txt` using `password_hash()`; `login.php` validates with `password_verify()`; `logout.php` calls `session_destroy()`; all game routes guarded with `requireLogin()` |
| 4 | **Game Logic (PHP)** | `room.php` drives all puzzle logic server-side; conditional branching on correct/wrong answers; arrays/loops manage room progression; brute-force lockout with `$_SESSION['lockout_until']` |
| 5 | **Rubric Alignment** | Responsive CSS3 (Flexbox/Grid); no JavaScript; Scrum documented in `sprint_log.html`; Development Journal included |

---

## File Structure

```
Project2_webdev/
├── index.php           # Home / Dashboard
├── login.php           # Authentication
├── register.php        # New user registration
├── start.php           # Initialize game session
├── room.php            # Core puzzle rooms (1–3)
├── escape.php          # Results + player profile card
├── leaderboard.php     # Top scores
├── logout.php          # Session destroy
├── includes/
│   ├── config.php      # Constants, room defs, helpers
│   ├── header.php      # Shared HTML head + nav
│   └── footer.php      # Shared footer
├── assets/
│   └── css/style.css   # Full design system
├── data/
│   ├── users.txt       # Flat-file user store (password_hash)
│   ├── leaderboard.txt # Score records
│   └── .htaccess       # Block direct HTTP access to data/
├── sprint_log.html     # Scrum sprint documentation
├── dev_journal.md      # Daily development journal
└── README.md           # This file
```

---

## Setup Instructions (XAMPP / MAMP)

1. Copy the project folder to `htdocs/` (XAMPP) or `Sites/` (MAMP)
2. Start Apache
3. Visit `http://localhost/Project2_webdev/`
4. Ensure `data/` directory is **writable** by PHP:
   ```
   chmod 775 data/
   ```
5. Register a new account and start playing

---

## Team Roles (Scrum)

| Role | Member |
|------|--------|
| Product Owner | Rohan Khandelwal |
| Scrum Master | Mohit Kokane |
| Dev Team | Rohan Khandelwal, Mohit Kokane |

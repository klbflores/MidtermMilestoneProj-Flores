# 🍳 TipidKusina: Barangay Student Budget Meal Sharing

A responsive, secure web platform built with plain Object-Oriented PHP, MySQL/PDO, and vanilla front-end technologies (HTML5, CSS3, JavaScript). Designed around the theme of **Filipino Student Budget Meals**, TipidKusina allows community members to share affordable recipes, customize ingredient lists dynamically, leave cooking feedback, and save favorites asynchronously without page reloads.

---

## 📌 Project Overview
- **Repository Name:** `MidtermMilestoneProj-Flores`
- **Database Name:** `tipid_kusina_db`
- **Architecture:** Plain PHP (OOP), PDO, Session Auth, Vanilla JS, CSS Grid/Flexbox
- **Theme:** Filipino Student Budget Meals (Carinderia / Tipid Hacks)

---

## 🚀 Key Features

### 1. Authentication & Session Security
- **Strict Access Control:** Member-only pages (`index.php`, `recipe_create.php`, `recipe_edit.php`, etc.) redirect unauthenticated visitors to `login.php`.
- **Password Security:** Plaintext passwords are never saved; hashed using `password_hash()` (Bcrypt) and checked with constant-time `password_verify()`.
- **Session Management:** Centralized session gates via `auth_check.php` with session hijacking mitigation.

### 2. Recipe Publishing & Dynamic Ingredients
- **Multi-Field Inputs:** Form dynamically creates and deletes ingredient input rows in the DOM using JavaScript, capturing items and quantities via array inputs (`ingredient_name[]`, `ingredient_qty[]`).
- **Atomic Operations:** Uses PDO Transactions (`beginTransaction`, `commit`, `rollBack`) to guarantee that recipe parent details and individual ingredient rows are saved together without data corruption.
- **Sticky Form State:** Preserves all typed inputs, selections, and multiline text in the event of client/server validation errors.

### 3. Discoverability & Exploration
- **Real-Time Chronological Feed:** Lists newest student recipes first.
- **Search & Filtering:** Search by keyword/ingredient and narrow meals down using category dropdowns without clearing input states.

### 4. Interactive Feedback & Ownership Controls
- **Community Feedback:** Members can comment with money-saving cooking tips.
- **Strict Authorization:** Only the original author can edit or delete their own recipes and comments.
- **Edited Tag:** Visually displays an `(edited)` indicator whenever a recipe or comment has been modified after publication.
- **Safe Relational Deletion:** Deleting a recipe safely cascades and clears associated ingredients, comments, and favorites.

### 5. Asynchronous Bookmarks (No Page Reload)
- **Zero-Reload Toggle:** Toggling a recipe bookmark sends an asynchronous `fetch()` POST request to `toggle_favorite.php`.
- **State Toggle:** The heart icon updates instantaneously in the browser viewport without interrupting the user experience or refreshing the screen.
- **My Favorites Page:** Dedicated view displaying bookmarked budget dishes.

### 6. Custom Value Feature: Estimated Student Budget (₱)
- **Allowance Tracker:** Includes an estimated cost per serving attribute (`estimated_cost`) in the database, recipe forms, and feed cards to highlight meals fitting student daily budgets.

---

## 🗄️ Database Architecture
The application uses 6 normalized relational MySQL tables:
1. `users` — Site member credentials and account timestamps.
2. `categories` — Pre-seeded budget meal categories.
3. `recipes` — Recipe titles, descriptions, instructions, cost, and edit flags.
4. `ingredients` — Individual ingredient rows linked to recipes (`1:N`).
5. `comments` — Member feedback and advice linked to recipes (`1:N`).
6. `favorites` — Unique member-to-recipe bookmark junction (`N:M`).

---

## 🛡️ Security Non-Negotiables Implemented
- **SQL Injection Defense:** All dynamic queries utilize PDO prepared statements with parameter placeholders (`?`). Native prepared statement emulation is disabled (`PDO::ATTR_EMULATE_PREPARES => false`).
- **Cross-Site Scripting (XSS) Mitigation:** All user-supplied output is sanitized through `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` before HTML rendering.
- **Input Validation:** Strict server-side validation checks implemented via `classes/Validator.php`.

---

## 📁 File Structure
```text
MidtermMilestoneProj-Flores/
├── classes/
│   ├── Database.php          # Singleton PDO connection wrapper
│   ├── Validator.php         # Server-side data sanitation & validation
│   ├── AuthService.php       # Member registration & login verification
│   ├── RecipeService.php     # Recipe CRUD & transactional writes
│   └── FavoriteService.php   # User bookmark management
├── auth_check.php            # Session gatekeeper guards
├── db.php                    # PDO instance entry point
├── register.php              # Account creation view
├── login.php                 # Member authentication view
├── logout.php                # Session teardown
├── index.php                 # Recipe feed, search, and category filter
├── recipe_create.php         # Dynamic recipe submission form
├── recipe_detail.php         # Single recipe view & feedback section
├── recipe_edit.php           # Author edit form with sticky inputs
├── recipe_delete.php         # Transactional deletion script
├── comment_actions.php       # Comment creation, inline editing, and deletion
├── toggle_favorite.php       # Asynchronous JSON endpoint for bookmarks
├── my_favorites.php          # User's bookmarked recipes view
├── style.css                 # Responsive warm Filipino-themed stylesheet
├── app.js                    # Dynamic DOM inputs, inline editing, & async fetch
├── tipid_kusina_db.sql       # Exported database schema & seed data
└── README.md                 # Project documentation

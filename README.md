# PHP_Laravel12_Revisionable

## Overview

This project demonstrates a complete end‑to‑end Laravel Revision History System using the **venturecraft/revisionable** package.
It allows developers to automatically track every change made to database records, view previous values, identify who made updates, and maintain a clean revision log.

This system is useful for audit logs, admin panels, blog edits, product updates, and legal data tracking.

---

## Core Capabilities

* Track every change in a model
* View old and new field values
* Identify which user made changes
* Maintain revision limits
* Automatically clean old revisions
* Display revision history in the UI

---

## Step‑By‑Step Implementation

### Step 1 — Create New Laravel Project

```bash
composer create-project laravel/laravel laravel-revisionable
cd laravel-revisionable
```

---

### Step 2 — Database Configuration

Open `.env` file and update database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_revisionable
DB_USERNAME=root
DB_PASSWORD=
```

Create the database manually in MySQL or phpMyAdmin.

---

### Step 3 — Install Revisionable Package

```bash
composer require venturecraft/revisionable
```

---

### Step 4 — Publish Package Files

```bash
php artisan vendor:publish --provider="Venturecraft\Revisionable\RevisionableServiceProvider"
```

This command creates:

* Revisions migration
* Configuration file

---

### Step 5 — Run Migrations

```bash
php artisan migrate
```

Tables created:

* users
* revisions

---

### Step 6 — Create Post Migration

```bash
php artisan make:migration create_posts_table
```

Fields:

* id
* title
* content
* is_published
* user_id (Foreign Key)
* timestamps

Then run:

```bash
php artisan migrate
```

---

### Step 7 — Create Models

#### Post Model

Add:

* `RevisionableTrait`
* Fillable fields
* Relationships
* Revision settings

Important Settings:

| Setting            | Meaning                   |
| ------------------ | ------------------------- |
| revisionEnabled    | Enable tracking           |
| historyLimit       | Maximum revisions stored  |
| revisionCleanup    | Auto delete old revisions |
| dontKeepRevisionOf | Ignore selected fields    |

#### User Model

Add `RevisionableTrait` if user changes should also be tracked.

---

### Step 8 — Create Controller

```bash
php artisan make:controller PostController --resource
```

Controller Methods:

| Method    | Purpose             |
| --------- | ------------------- |
| index     | List posts          |
| create    | Show form           |
| store     | Save post           |
| show      | Post with revisions |
| edit      | Edit form           |
| update    | Update post         |
| destroy   | Delete post         |
| revisions | Full history        |

---

### Step 9 — Routes

`routes/web.php`

```
Route::resource('posts', PostController::class);
Route::get('posts/{post}/revisions', [PostController::class, 'revisions'])->name('posts.revisions');
```

---

### Step 10 — Views (UI)

Create folder:

```
resources/views/posts
```

Files:

| File                  | Purpose                         |
| --------------------- | ------------------------------- |
| layouts/app.blade.php | Layout and navigation           |
| index.blade.php       | List posts                      |
| create.blade.php      | Add post                        |
| edit.blade.php        | Edit post                       |
| show.blade.php        | Show post with recent revisions |
| revisions.blade.php   | Full history                    |

UI uses Bootstrap 5.

---

### Step 11 — Factories

```bash
php artisan make:factory PostFactory
```

Factories generate fake data using Faker.

---

### Step 12 — Seeder

```bash
php artisan make:seeder DatabaseSeeder
```

Seeder Example:

* 3 users
* 5 posts per user

Run:

```bash
php artisan db:seed
```

---

### Step 13 — Run Server

```bash
php artisan serve
```

Visit:

```
http://127.0.0.1:8000/posts
```
<img width="1700" height="552" alt="image" src="https://github.com/user-attachments/assets/9ca9521c-fd34-4aa7-89e6-1932130a1e22" />
<img width="1689" height="506" alt="image" src="https://github.com/user-attachments/assets/cc277ccb-3e2d-4197-b059-cdfdd4d3912f" />


---

## Internal Working

| Action               | Result             |
| -------------------- | ------------------ |
| Create Post          | Revision saved     |
| Update Title         | Old and new stored |
| Change Author        | Tracked            |
| Delete Post          | Logged             |
| Edit Multiple Fields | All saved          |

---

## Revisions Table Structure

| Column            | Meaning          |
| ----------------- | ---------------- |
| revisionable_id   | Model ID         |
| revisionable_type | Model name       |
| key               | Field changed    |
| old_value         | Old data         |
| new_value         | New data         |
| user_id           | User who changed |
| created_at        | Timestamp        |

---

## Key Features

### Field‑Level Tracking

View exact field changes.

### User Attribution

Identify which user performed edits.

### Revision Limit

Prevent excessive database growth.

### Cleanup System

Automatically remove old revisions.

### UI History Page

Readable change log for users.

---

## Example Revision Output

User: Mihir
Field: Title
Old: Laravel CRUD
New: Laravel CRUD Advanced
Date: 2026‑02‑11 18:30

---

## Advanced Options

### Ignore Fields

```
protected $dontKeepRevisionOf = ['updated_at'];
```

### Limit Revisions

```
protected $historyLimit = 100;
```

### Disable Tracking Temporarily

```
$post->disableRevision();
```

---

## Common Errors and Fixes

| Error               | Fix                             |
| ------------------- | ------------------------------- |
| No revisions saving | Add trait to model              |
| Migration error     | Run vendor publish again        |
| User null           | Ensure authentication login     |
| Old values empty    | Enable revisionCreationsEnabled |

---

## Best Practices

* Track only important fields
* Limit revision history
* Add indexing on revision table
* Avoid tracking timestamps

---

## Real‑World Use Cases

* Blog editing history
* Admin audit logs
* Product price changes
* User profile edits
* Legal and compliance tracking

---

## License

MIT License

# PHP_Laravel12_Revisionable

## Overview

This project demonstrates a complete end‑to‑end Laravel Revision History System using the **venturecraft/revisionable** package.
It allows developers to automatically track every change made to database records, view previous values, identify who made updates, and maintain a clean revision log.

This system is useful for audit logs, admin panels, blog edits, product updates, and legal data tracking.

---

## Core Capabilities

* Track every change in a model
* View old and new field values
* Identify which user made changes
* Maintain revision limits
* Automatically clean old revisions
* Display revision history in the UI

---

## Step‑By‑Step Implementation

### Step 1 — Create New Laravel Project

```bash
composer create-project laravel/laravel laravel-revisionable
cd laravel-revisionable
```

---

### Step 2 — Database Configuration

Open `.env` file and update database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_revisionable
DB_USERNAME=root
DB_PASSWORD=
```

Create the database manually in MySQL or phpMyAdmin.

---

### Step 3 — Install Revisionable Package

```bash
composer require venturecraft/revisionable
```

---

### Step 4 — Publish Package Files

```bash
php artisan vendor:publish --provider="Venturecraft\Revisionable\RevisionableServiceProvider"
```

This command creates:

* Revisions migration
* Configuration file

---

### Step 5 — Run Migrations

```bash
php artisan migrate
```

Tables created:

* users
* revisions

---

### Step 6 — Create Post Migration

```bash
php artisan make:migration create_posts_table
```

Fields:

* id
* title
* content
* is_published
* user_id (Foreign Key)
* timestamps

Then run:

```bash
php artisan migrate
```

---

### Step 7 — Create Models

#### Post Model

Add:

* `RevisionableTrait`
* Fillable fields
* Relationships
* Revision settings

Important Settings:

| Setting            | Meaning                   |
| ------------------ | ------------------------- |
| revisionEnabled    | Enable tracking           |
| historyLimit       | Maximum revisions stored  |
| revisionCleanup    | Auto delete old revisions |
| dontKeepRevisionOf | Ignore selected fields    |

#### User Model

Add `RevisionableTrait` if user changes should also be tracked.

---

### Step 8 — Create Controller

```bash
php artisan make:controller PostController --resource
```

Controller Methods:

| Method    | Purpose             |
| --------- | ------------------- |
| index     | List posts          |
| create    | Show form           |
| store     | Save post           |
| show      | Post with revisions |
| edit      | Edit form           |
| update    | Update post         |
| destroy   | Delete post         |
| revisions | Full history        |

---

### Step 9 — Routes

`routes/web.php`

```
Route::resource('posts', PostController::class);
Route::get('posts/{post}/revisions', [PostController::class, 'revisions'])->name('posts.revisions');
```

---

### Step 10 — Views (UI)

Create folder:

```
resources/views/posts
```

Files:

| File                  | Purpose                         |
| --------------------- | ------------------------------- |
| layouts/app.blade.php | Layout and navigation           |
| index.blade.php       | List posts                      |
| create.blade.php      | Add post                        |
| edit.blade.php        | Edit post                       |
| show.blade.php        | Show post with recent revisions |
| revisions.blade.php   | Full history                    |

UI uses Bootstrap 5.

---

### Step 11 — Factories

```bash
php artisan make:factory PostFactory
```

Factories generate fake data using Faker.

---

### Step 12 — Seeder

```bash
php artisan make:seeder DatabaseSeeder
```

Seeder Example:

* 3 users
* 5 posts per user

Run:

```bash
php artisan db:seed
```

---

### Step 13 — Run Server

```bash
php artisan serve
```

Visit:

```
http://127.0.0.1:8000/posts
```
<img width="1700" height="552" alt="image" src="https://github.com/user-attachments/assets/9ca9521c-fd34-4aa7-89e6-1932130a1e22" />
<img width="1689" height="506" alt="image" src="https://github.com/user-attachments/assets/cc277ccb-3e2d-4197-b059-cdfdd4d3912f" />


---

## Internal Working

| Action               | Result             |
| -------------------- | ------------------ |
| Create Post          | Revision saved     |
| Update Title         | Old and new stored |
| Change Author        | Tracked            |
| Delete Post          | Logged             |
| Edit Multiple Fields | All saved          |

---

## Revisions Table Structure

| Column            | Meaning          |
| ----------------- | ---------------- |
| revisionable_id   | Model ID         |
| revisionable_type | Model name       |
| key               | Field changed    |
| old_value         | Old data         |
| new_value         | New data         |
| user_id           | User who changed |
| created_at        | Timestamp        |

---

## Key Features

### Field‑Level Tracking

View exact field changes.

### User Attribution

Identify which user performed edits.

### Revision Limit

Prevent excessive database growth.

### Cleanup System

Automatically remove old revisions.

### UI History Page

Readable change log for users.

---

## Example Revision Output

User: Mihir
Field: Title
Old: Laravel CRUD
New: Laravel CRUD Advanced
Date: 2026‑02‑11 18:30

---

## Advanced Options

### Ignore Fields

```
protected $dontKeepRevisionOf = ['updated_at'];
```

### Limit Revisions

```
protected $historyLimit = 100;
```

### Disable Tracking Temporarily

```
$post->disableRevision();
```

---

## Common Errors and Fixes

| Error               | Fix                             |
| ------------------- | ------------------------------- |
| No revisions saving | Add trait to model              |
| Migration error     | Run vendor publish again        |
| User null           | Ensure authentication login     |
| Old values empty    | Enable revisionCreationsEnabled |

---

## Best Practices

* Track only important fields
* Limit revision history
* Add indexing on revision table
* Avoid tracking timestamps

---

## Real‑World Use Cases

* Blog editing history
* Admin audit logs
* Product price changes
* User profile edits
* Legal and compliance tracking

---

## License

MIT License


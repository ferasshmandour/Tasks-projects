# Task Solution: Posts Module with Error Handling, Gate, Trait & Helper

This document describes each step implemented to solve the practical task.

---

## **Step 1: Error Handler** (Centralized API Error Responses)

**Location:** `bootstrap/app.php`

**What was done:**
- Added exception handlers in the `withExceptions()` callback
- **ValidationException** → Returns JSON with `422` status and validation errors
- **ModelNotFoundException** → Returns JSON with `404` status and "Resource not found" message
- **AuthorizationException** → Returns JSON with `403` status and the exception message

**Important:** No try-catch blocks are used inside controllers. Exceptions bubble up and are caught centrally.

```php
$exceptions->renderable(function (ValidationException $e, $request) {
    if ($request->expectsJson()) {
        return response()->json(['message' => '...', 'errors' => $e->errors()], 422);
    }
});
```

---

## **Step 2: Traits** (OwnershipTrait)

**Location:** `app/Traits/OwnershipTrait.php`

**What was done:**
- Created `OwnershipTrait` with method `isOwner(Model $model): bool`
- Compares `auth()->id()` with `$model->user_id` to check ownership
- Used inside `PostPolicy` for update and delete authorization

```php
public function isOwner(Model $model): bool
{
    return auth()->id() === $model->user_id;
}
```

---

## **Step 3: Helper** (currentUserId)

**Location:** `app/Helpers/helpers.php`

**What was done:**
- Created global helper `currentUserId(): ?int` that returns `auth()->id()`
- Registered in `composer.json` under `autoload.files`
- Ran `composer dump-autoload` to load the helper

**Usage in PostController:**
```php
Post::create([
    ...$validated,
    'user_id' => currentUserId(),
]);
```

---

## **Step 4: Gate** (access-admin-panel)

**Location:** `app/Providers/AppServiceProvider.php`

**What was done:**
- Defined Gate named `access-admin-panel`
- Logic: Allow only users where `$user->role === 'admin'`
- Used in route middleware: `->middleware('can:access-admin-panel')` on `/api/admin` endpoint

**Note:** Gate is for general authorization, not tied to a specific model.

```php
Gate::define('access-admin-panel', function ($user) {
    return $user->role === 'admin';
});
```

---

## **Step 5: Policy** (PostPolicy)

**Location:** `app/Policies/PostPolicy.php`

**What was done:**
- Created `PostPolicy` with `OwnershipTrait`
- **update()** and **delete()** methods return `$this->isOwner($post)`
- Used in controller via:
  - `Gate::allows('update', $post)` for update (returns bool)
  - `Gate::authorize('delete', $post)` for delete (throws if unauthorized)

**Note:** Policy is model-specific authorization for the Post model.

---

## **Step 6: Post Module** (Model, Migration, Controller, Routes)

**Files created:**
- `app/Models/Post.php` – Model with `title`, `content`, `user_id`
- `database/migrations/2025_01_31_000001_create_posts_table.php`
- `database/migrations/2025_01_31_000002_add_role_to_users_table.php` – Adds `role` for admin gate
- `app/Http/Controllers/Api/PostController.php` – CRUD with policy checks
- `routes/api.php` – API resource routes for posts + admin endpoint

**API Endpoints:**
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/posts` | List all posts |
| POST | `/api/posts` | Create post (uses `currentUserId()`) |
| GET | `/api/posts/{id}` | Show post |
| PUT/PATCH | `/api/posts/{id}` | Update post (policy: owner only) |
| DELETE | `/api/posts/{id}` | Delete post (policy: owner only) |
| GET | `/api/admin` | Admin panel (gate: admin only) |

---

## **Step 7: Running the Application**

1. Run migrations: `php artisan migrate`
2. Seed admin user: `php artisan db:seed`
   - Admin: `admin@example.com` / `password` (role: admin)
   - User: `test@example.com` / `password` (role: user)
3. For API auth, use session-based auth (log in via web) or install Laravel Sanctum for token-based auth

---

## **Summary Checklist**

| # | Requirement | Status |
|---|-------------|--------|
| 1 | Error Handler (ValidationException, ModelNotFoundException, AuthorizationException) | Done |
| 2 | OwnershipTrait with isOwner() | Done |
| 3 | Helper currentUserId() | Done |
| 4 | Gate access-admin-panel | Done |
| 5 | PostPolicy for update/delete | Done |
| 6 | No try-catch in controllers | Done |

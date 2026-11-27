# DWCC QuizGuard — Admin Module Defense Guide & Documentation

This document covers **every page, button, filter, modal, backend controller, database effect, and security guard** in the **Admin** module of **DWCC QuizGuard**.

---

## 0. Explain Like I'm a Kid (Simple Analogy Guide)

Imagine **QuizGuard** is a big digital school, and the **Admin** is the **School Principal** who holds the master keys:

- **The Security Guard at the Door (`auth` & `role:admin`):**  
  Before anyone can enter the Principal's office (`/admin/*`), a guard checks two things: *"Are you logged in?"* and *"Are you really the Principal?"* If a student or teacher tries to sneak in, the guard says **"No! 403 Unauthorized!"**

- **Admin Dashboard (`System Health`) = The Car Dashboard / Doctor Checkup:**  
  Just like a car dashboard shows your speed and if the engine is okay, this page checks if the database is awake and fast (in milliseconds), how many kids are taking a quiz right now, if anyone typed the wrong password, or if anyone got caught cheating in the last 24 hours.
  - **Refresh button:** Checks the pulse again right now.
  - **View logs button:** Opens the big notebook of everything that happened.

- **User Management = The School ID Card Office:**  
  This is where the Principal makes accounts for students, teachers, and other admins.
  - **Add User:** Makes **1 new ID card** at a time. If you don't type a password, the computer automatically makes one using their **LASTNAME + School ID** (like `MACARAIG49543`).
  - **Import CSV:** Like a **magic photocopier**—you give it a spreadsheet list of 100 students, and it makes all 100 accounts at once!
  - **Pending & OTP:** New accounts start as **Pending** (sleeping). The system emails them a **secret 6-digit code (OTP)**—they have to prove it's really their email before their account wakes up (`Active`).
  - **Edit User (Pencil button):** Fixes a typo in someone's name, email, department, or gives them a new password. *(Rule: You cannot demote yourself!)*
  - **Deactivate / Activate:** Turns someone's ID card **OFF** (like pausing a library card so they can't log in) or **ON** again. *(Rule: You cannot turn off your own card, and you cannot turn on a `Pending` card—they must use their email code first!)*

- **System Logs = The School Security Notebook:**  
  Every time someone logs in, fails a password, gets caught cheating, or when the Principal creates/edits a user, the computer writes a line in this notebook with the **time, who did it, and what happened**.
  - **Search, From/To Dates, Role, Action:** Like using a magnifying glass and calendar to find exact pages in the notebook (e.g., *"Show me everyone who failed to log in yesterday"*).

### How the Code Works (Step-by-Step, Like a Restaurant)

When you click any button in the Admin panel, Laravel follows **5 simple steps**:

1. **The View (`resources/views/admin/*.blade.php`) = The Menu & Plate**  
   This is the HTML + Tailwind CSS page you see. When you click **Filter**, **Create user**, **Edit**, or **Deactivate**, the browser sends a `GET`, `POST`, or `PUT` request to a URL (like `/admin/users`).
2. **The Route (`routes/web.php`) = The Waiter**  
   `routes/web.php` looks at the URL and decides which Controller method should handle it (for example, `POST /admin/users` goes to `UserController@store`).
3. **The Middleware (`RoleMiddleware.php`) = The Bouncer**  
   Before the Chef starts cooking, `auth` checks if you are logged in, and `role:admin` checks if your `role` column is `'admin'`. If not, it stops right there with **403 Unauthorized**.
4. **The Controller (`app/Http/Controllers/...`) = The Chef**  
   - **Dashboard (`DashboardController@admin`):** Runs `microtime(true)` before and after `DB::select('select 1')` to measure database speed in milliseconds, loops 12 times (`$i = 11` down to `0`) to count events in each 2-hour block, and counts active users/quizzes/violations.
   - **Users (`UserController`):**
     - `index()`: Starts `User::query()`, adds `where(...)` only for the filters you filled in, and returns `paginate(20)`.
     - `store()` / `import()`: Checks your inputs with `$request->validate(...)` (e.g., 5-digit `school_id_num`, unique `dwcc_email`, valid `department`), hashes the password with `Hash::make()`, saves the user as `pending`, writes a line to `activity_logs` via `ActivityLog::record()`, and calls `$user->sendOtp()` to email a 6-digit code.
     - `update()`: Uses `Rule::unique(...)->ignore($user->user_id, 'user_id')` so saving the same user doesn't complain that their own email/ID already exists, blocks self-demotion, and only re-hashes `password_hash` if you typed a new password.
     - `toggle()`: Blocks `pending` users and blocks `Auth::id()` (yourself), then flips `status` between `'active'` and `'inactive'` and logs it.
   - **Logs (`ActivityLogController@index`):** Starts `ActivityLog::with('user')`, adds filters (`role`, `action`, `q` search using `CONCAT(first_name, ' ', last_name)`), converts `from`/`to` dates with `Carbon::parse()` (`startOfDay()` to `endOfDay()`, swapping if reversed), and paginates 20 rows newest-first.
5. **The Model & Database (`User.php`, `ActivityLog.php`, MySQL) = The Fridge & Notebook**  
   Eloquent Models turn PHP calls like `User::create(...)` or `ActivityLog::record(...)` into safe SQL queries, save the rows in MySQL, and hand the data back to the Blade View to display on your screen.

---
 put 
## 1. Access Control & Routing

All Admin routes are protected by **two middlewares**:
1. `auth` — Ensures the user is logged in (redirects guests to `/login`).
2. `role:admin` (`App\Http\Middleware\RoleMiddleware`) — Checks `Auth::user()->role` (case-insensitive). If a `student` or `teacher` tries to access `/admin/*`, Laravel aborts with **HTTP 403 Unauthorized**.

### Route Definitions (`routes/web.php`)
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users');
    Route::post('/admin/users', [UserController::class, 'store']);
    Route::post('/admin/users/{id}/toggle', [UserController::class, 'toggle']);
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::post('/admin/users/import', [UserController::class, 'import']);
    Route::get('/admin/logs', [ActivityLogController::class, 'index'])->name('admin.logs');
});
```

### Role Middleware (`app/Http/Middleware/RoleMiddleware.php`)
```php
public function handle(Request $request, Closure $next, ...$roles)
{
    if (!Auth::check()) {
        return redirect('/login');
    }

    $userRole = strtolower(trim((string) Auth::user()->role));
    $allowedRoles = array_map(fn($r) => strtolower(trim($r)), $roles);

    if (!in_array($userRole, $allowedRoles, true)) {
        abort(403, 'Unauthorized. Your role (' . Auth::user()->role . ') cannot access this portal.');
    }

    return $next($request);
}
```

---

## 2. Global Admin Layout (`resources/views/shared_layouts/admin.blade.php`)

Every Admin page shares this sidebar + top header layout:

| UI Element | Action / Route | What Happens |
| :--- | :--- | :--- |
| **Dashboard** (Sidebar) | `GET /admin/dashboard` | Opens **System Health** dashboard. Active link is highlighted with `bg-white/15`. |
| **Users** (Sidebar) | `GET /admin/users` | Opens **User Management**. |
| **System Logs** (Sidebar) | `GET /admin/logs` | Opens **System Logs** audit trail. |
| **Profile Card** (Sidebar bottom) | Display only | Shows admin initials avatar, full name, and role (`Admin`). |
| **Logout** (Icon button) | `POST /logout` | Logs out the current admin, invalidates the session, and redirects to `/login`. |
| **Flash Banners** | Automatic | Displays green `session('success')` messages or red `$errors->all()` validation errors at the top of `<main>`. |

---

## 3. Admin Dashboard — System Health (`/admin/dashboard`)

**Controller:** `DashboardController@admin` (`app/Http/Controllers/DashboardController.php`)  
**View:** `resources/views/admin/dashboard.blade.php`

### Buttons & Interactive Elements
| Button / Link | Action | What Happens |
| :--- | :--- | :--- |
| **Refresh** | `GET /admin/dashboard` | Reloads the page to re-measure database latency (`DB::select('select 1')`) and fetch fresh live counts. |
| **View logs** | `GET /admin/logs` | Navigates directly to the full **System Logs** page. |
| **View all logs &rarr;** | `GET /admin/logs` | Navigates from the *Recent Security Events* card to **System Logs**. |

### Dashboard Sections & Data Sources
1. **Header Status Badge**:
   - Green **`All systems operational`** if `DB::select('select 1')` succeeds; otherwise red **`Database not responding`**.
   - Displays `Last checked {timestamp}`.
2. **Live Metrics (4 Cards)**:
   - **Active quiz sessions**: `Attempt::where('status', 'in_progress')->count()`
   - **Failed logins (24h)**: `ActivityLog::where('action', 'LOGIN_FAILED')->where('created_at', '>=', now()->subDay())->count()`
   - **Violations (24h)**: `ViolationLog::where('occurred_at', '>=', now()->subDay())->count()` (also shows total all-time violations)
   - **Database query time**: Real response time in `ms` measured around `DB::select('select 1')`.
3. **System Totals (5 Cards)**:
   - Active **Students**, Active **Teachers**, Active **Subjects**, Active **Quizzes**, and total **Quiz attempts**.
4. **Security Events Over Time (24h Bar Chart)**:
   - Groups the last 24 hours into **12 two-hour buckets**.
   - Sums `LOGIN_FAILED` (from `activity_logs`) + `ViolationLog` entries per bucket.
   - Highlights bars with `>= 3` events in amber (`bg-amber-300`), otherwise emerald (`bg-emerald-200`).
5. **Service Status Panel**:
   - **Web Application**: Operational.
   - **Database (MySQL)**: Operational / Down + query latency in ms.
   - **Email (OTP codes)**: Shows `config('mail.default')` (`Testing (log only)` if `log`, `Configured` otherwise).
6. **Recent Security Events Table**:
   - Shows the latest **5** `activity_logs` where `action` is `LOGIN_FAILED` or `QUIZ_AUTO_SUBMITTED`.

### Dashboard Controller Code
```php
public function admin()
{
    $start = microtime(true);
    try {
        DB::select('select 1');
        $dbOk = true;
    } catch (\Exception $e) {
        $dbOk = false;
    }
    $dbMs = round((microtime(true) - $start) * 1000);

    $since = now()->subDay();

    // Last 24 hours in 12 blocks of 2 hours (oldest first): failed logins + violations
    $chart = [];
    for ($i = 11; $i >= 0; $i--) {
        $end  = now()->subHours($i * 2);
        $from = $end->copy()->subHours(2);

        $events = ActivityLog::where('action', 'LOGIN_FAILED')
            ->where('created_at', '>', $from)->where('created_at', '<=', $end)->count()
            + ViolationLog::where('occurred_at', '>', $from)->where('occurred_at', '<=', $end)->count();

        $chart[] = ['label' => $end->format('g A'), 'events' => $events];
    }

    $max = max(1, max(array_column($chart, 'events')));
    foreach ($chart as $k => $bar) {
        $chart[$k]['height'] = max(2, round($bar['events'] / $max * 85));
    }

    return view('admin.dashboard', [
        'students'       => User::where('role', 'student')->where('status', 'active')->count(),
        'teachers'       => User::where('role', 'teacher')->where('status', 'active')->count(),
        'subjects'       => Subject::where('status', 'active')->count(),
        'quizzes'        => Quiz::where('status', 'active')->count(),
        'attempts'       => Attempt::count(),
        'violations'     => ViolationLog::count(),
        'activeSessions' => Attempt::where('status', 'in_progress')->count(),
        'failedLogins'   => ActivityLog::where('action', 'LOGIN_FAILED')->where('created_at', '>=', $since)->count(),
        'violations24'   => ViolationLog::where('occurred_at', '>=', $since)->count(),
        'dbOk'           => $dbOk,
        'dbMs'           => $dbMs,
        'mailer'         => config('mail.default'),
        'chart'          => $chart,
        'chartTotal'     => array_sum(array_column($chart, 'events')),
        'recent'         => ActivityLog::with('user')
            ->whereIn('action', ['LOGIN_FAILED', 'QUIZ_AUTO_SUBMITTED'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(),
        'checkedAt'      => now()->format('M d, g:i A'),
    ]);
}
```

---

## 4. User Management (`/admin/users`)

**Controller:** `App\Http\Controllers\Admin\UserController`  
**View:** `resources/views/admin/users.blade.php`  
**Allowed Departments:** `SIT`, `SOA`, `SOE`, `SBHTM`

### Every Button & Control Explained

| Button / Control | Route / Trigger | What It Does & What Happens in the Backend |
| :--- | :--- | :--- |
| **Import CSV** (Dropdown) &rarr; **Import** | `POST /admin/users/import` (`UserController@import`) | 1. Validates `.csv`/`.txt` file (`max:2048` KB).<br>2. Strips UTF-8 BOM (`\xEF\xBB\xBF`) and lowercases headers.<br>3. Required CSV columns: `role, school_id_num, first_name, middle_name, last_name, dwcc_email, password, department`.<br>4. Skips blank rows; validates each row (5+ digit unique `school_id_num`, unique `dwcc_email`, allowed department).<br>5. If `password` cell is empty, generates default password `UPPERCASE_LASTNAME` + `school_id_num` (e.g., `MACARAIG49543`).<br>6. Creates user with `status = 'pending'`, sends 6-digit OTP email via `$user->sendOtp()`.<br>7. Logs `USERS_IMPORTED` in `activity_logs` if at least 1 user was created, and reports created count + any line-by-line errors. |
| **Add User** (Dropdown) &rarr; **Create user** | `POST /admin/users` (`UserController@store`) | 1. Normalizes `role` (lowercase) and `department` (uppercase).<br>2. Validates `role` (`student\|teacher\|admin`), `school_id_num` (`>= 10000`, unique), `first_name`, `middle_name` (optional), `last_name`, `dwcc_email` (unique), `password` (nullable, min 8), `department` (`SIT\|SOA\|SOE\|SBHTM`).<br>3. If `password` is blank, auto-generates `UPPERCASE_LASTNAME` + `school_id_num`.<br>4. Saves user with `status = 'pending'` and hashed password.<br>5. Records `USER_CREATED` in `activity_logs`.<br>6. Sends 6-digit OTP to `dwcc_email` (`$user->sendOtp()`). |
| **Search input (`q`)** | `GET /admin/users?q=...` | Splits search text by spaces and matches every word against `first_name`, `last_name`, or `dwcc_email`. |
| **All / Students / Teachers** (Tabs) | `GET /admin/users` or `?role=student\|teacher` | Filters the table by role and displays live total counts (`$counts['all']`, `students`, `teachers`). |
| **Department** & **Status** dropdowns + **Filter** | `GET /admin/users` | Filters by `department` (`SIT`, `SOA`, `SOE`, `SBHTM`) and/or `status` (`active`, `pending`, `inactive`), paginated at **20 users per page**. |
| **Edit User** (Pencil Icon) &rarr; **Save Changes** | JS `openEditModal(this)` &rarr; `PUT /admin/users/{id}` (`UserController@update`) | 1. Opens `#editUserModal` pre-filled with the user's `data-*` attributes.<br>2. Validates fields (ignoring current user's own `school_id_num` and `dwcc_email` for uniqueness).<br>3. **Self-demotion guard**: If the logged-in admin tries to change their own role away from `admin`, it blocks the update with an error.<br>4. Updates `password_hash` **only if** a new password (min 8) was entered.<br>5. Logs `USER_UPDATED` in `activity_logs`. |
| **Deactivate / Activate** | `POST /admin/users/{id}/toggle` (`UserController@toggle`) | 1. **Pending guard**: Not shown for `pending` users (displays `Awaiting OTP`); backend also rejects toggling `pending` accounts.<br>2. **Self-deactivation guard**: Blocks an admin from deactivating their own account.<br>3. Flips `status` between `active` and `inactive`.<br>4. Logs `USER_ACTIVE` or `USER_INACTIVE` in `activity_logs`. |

### Key `UserController` Code (`app/Http/Controllers/Admin/UserController.php`)
```php
// Default password generator: UPPERCASE(last_name) + school_id_num
private function generateDefaultPassword(string $lastName, string $schoolIdNum): string
{
    $cleanLast = strtoupper(preg_replace('/\s+/', '', trim($lastName)));
    $cleanId   = trim($schoolIdNum);

    return $cleanLast . $cleanId;
}

// Create user + log + send OTP
public function store(Request $request)
{
    $request->merge([
        'role'       => strtolower(trim((string) $request->role)),
        'department' => strtoupper(trim((string) $request->department)),
    ]);

    $request->validate([
        'role'          => 'required|in:student,teacher,admin',
        'school_id_num' => 'required|integer|min:10000|unique:users,school_id_num',
        'first_name'    => 'required|max:45',
        'middle_name'   => 'nullable|max:45',
        'last_name'     => 'required|max:45',
        'dwcc_email'    => 'required|email|max:45|unique:users,dwcc_email',
        'password'      => 'nullable|min:8',
        'department'    => ['required', 'string', 'max:45', Rule::in($this->getDepartments())],
    ], [
        'school_id_num.min' => 'School ID number must be at least 5 digits.',
    ]);

    $rawPassword = $request->filled('password')
        ? $request->password
        : $this->generateDefaultPassword($request->last_name, (string) $request->school_id_num);

    $user = User::create([
        'role'          => $request->role,
        'school_id_num' => $request->school_id_num,
        'first_name'    => $request->first_name,
        'middle_name'   => $request->middle_name,
        'last_name'     => $request->last_name,
        'dwcc_email'    => $request->dwcc_email,
        'password_hash' => Hash::make($rawPassword),
        'department'    => $request->department,
        'status'        => 'pending',
    ]);

    ActivityLog::record(
        Auth::user()->school_id_num,
        Auth::user()->role,
        'USER_CREATED',
        'Created ' . $user->role . ' account: ' . $user->school_id_num
    );

    try {
        $user->sendOtp();
    } catch (\Exception $e) {
        return redirect('/admin/users')->with('success', 'User created, but the email could not be sent.');
    }

    return redirect('/admin/users')->with('success', 'User created. A verification code was sent to their email.');
}

// Toggle Active / Inactive
public function toggle($id)
{
    $user = User::where('user_id', $id)->firstOrFail();

    if ($user->status === 'pending') {
        return back()->withErrors(['user' => 'This account must be activated with its OTP.']);
    }

    if ($user->user_id == Auth::id()) {
        return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
    }

    $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

    ActivityLog::record(
        Auth::user()->school_id_num,
        Auth::user()->role,
        'USER_' . strtoupper($user->status),
        'Set user ' . $user->school_id_num . ' to ' . $user->status
    );

    return redirect('/admin/users')->with('success', 'User is now ' . $user->status . '.');
}
```

---

## 5. System Logs (`/admin/logs`)

**Controller:** `App\Http\Controllers\Admin\ActivityLogController`  
**View:** `resources/views/admin/logs.blade.php`  
**Model:** `App\Models\ActivityLog`

### Every Button & Filter Explained
| Control | Behavior | What Happens |
| :--- | :--- | :--- |
| **Search input (`q`)** | Submit on Enter / **Filter** | Searches `school_id_num`, `action`, `description`, or related user's full name (`CONCAT(first_name, ' ', last_name)`). |
| **From:** & **To:** (`type="date"`) | Auto-submits on change | Syncs `min`/`max` dates in JS. Backend parses with `Carbon::parse()` (`startOfDay()` to `endOfDay()`), ignores invalid dates safely, and **auto-swaps** if `from > to`. |
| **Role** dropdown | Auto-submits on change | Filters by `student`, `teacher`, or `admin`. |
| **Action** dropdown | Auto-submits on change | Dynamically populated from distinct `action` values in `activity_logs` (e.g., `LOGIN`, `LOGIN_FAILED`, `USER_CREATED`, `ACCOUNT_VERIFIED`, `QUIZ_AUTO_SUBMITTED`). |
| **Filter** button | `GET /admin/logs` | Submits the filter form. |
| **Reset** link | `GET /admin/logs` | Clears all filters and resets to the default view (newest first, **20 logs per page**). |
| **Previous / Next** | Pagination links | Navigates pages while preserving active query filters (`withQueryString()`). |

### `ActivityLogController` Code (`app/Http/Controllers/Admin/ActivityLogController.php`)
```php
public function index(Request $request)
{
    $query = ActivityLog::with('user');

    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }
    if ($request->filled('action')) {
        $query->where('action', $request->action);
    }

    if ($request->filled('q')) {
        $q = trim($request->q);
        $query->where(function ($w) use ($q) {
            $w->where('school_id_num', 'like', "%$q%")
              ->orWhere('action', 'like', "%$q%")
              ->orWhere('description', 'like', "%$q%")
              ->orWhereHas('user', function ($u) use ($q) {
                  $u->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$q%"]);
              });
        });
    }

    // Date range filter (inclusive of full start/end days; swaps if reversed)
    $fromDate = null;
    $toDate = null;

    if ($request->filled('from')) {
        try {
            $fromDate = Carbon::parse($request->from)->startOfDay();
        } catch (\Throwable $e) {
            $fromDate = null;
        }
    }

    if ($request->filled('to')) {
        try {
            $toDate = Carbon::parse($request->to)->endOfDay();
        } catch (\Throwable $e) {
            $toDate = null;
        }
    }

    if ($fromDate && $toDate && $fromDate->gt($toDate)) {
        $swappedFrom = $toDate->copy()->startOfDay();
        $swappedTo = $fromDate->copy()->endOfDay();
        $fromDate = $swappedFrom;
        $toDate = $swappedTo;
    }

    if ($fromDate) {
        $query->where('created_at', '>=', $fromDate);
    }
    if ($toDate) {
        $query->where('created_at', '<=', $toDate);
    }

    $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
    $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

    return view('admin.logs', ['logs' => $logs, 'actions' => $actions]);
}
```

---

## 6. Quick Defense Q&A Cheat Sheet

- **Q: How is the Admin portal secured from students or teachers?**  
  **A:** Every `/admin/*` route is wrapped in `['auth', 'role:admin']`. `RoleMiddleware` checks the authenticated user's `role` and returns `403 Unauthorized` if it is not `admin`.
- **Q: Why does a newly created or CSV-imported user start as `pending` instead of `active`?**  
  **A:** For security and email verification. The system generates a 6-digit OTP (`User::sendOtp()`), stores it hashed with a 10-minute expiry, and requires the user to verify at `/verify-otp` before logging in.
- **Q: What happens if the Admin leaves the password blank when adding or importing a user?**  
  **A:** `generateDefaultPassword()` creates `UPPERCASE_LASTNAME` + `school_id_num` (e.g., `TOLENTINO47696`) and hashes it with `Hash::make()`.
- **Q: Can an Admin accidentally lock themselves out?**  
  **A:** No. `UserController@update` blocks an admin from changing their own role away from `admin`, and `UserController@toggle` blocks an admin from deactivating their own account.
- **Q: How are Admin actions tracked?**  
  **A:** Every major action (`USER_CREATED`, `USER_UPDATED`, `USER_ACTIVE`, `USER_INACTIVE`, `USERS_IMPORTED`) calls `ActivityLog::record()`, which is immediately searchable and filterable in **System Logs**.

# DWCC QuizGuard

**DWCC QuizGuard** is a responsive web-based quiz administration, anti-cheat proctoring, and academic reporting platform built with **Laravel 13** for **Divine Word College of Calapan (DWCC)**. Accessible from both desktop and mobile web browsers without requiring a separate native mobile app installation, it supports **Admin**, **Teacher**, and **Student** portals with OTP email verification, real-time exam monitoring, SIT-transmuted grading, and automated PDF/Excel/CSV reporting.

> **Mobile Browser Note (Students):** Mobile web browser access is designed for **student** quiz-taking and portal views as a responsive web application. Because proctoring relies on browser APIs (such as full-screen requests, viewport checks, and tab/window focus events), proctoring behavior can vary across mobile browsers compared to desktop browsers.

---

## Tech Stack & Local Environment

- **Local Server Stack:** Laragon (Apache / Nginx + PHP 8.3+ + MySQL)
- **Database Manager:** HeidiSQL
- **Backend:** Laravel 13 (`laravel/framework ^13.17`)
- **Frontend:** Blade Templates, Tailwind CSS, Chart.js
- **PDF Generation & Parsing:** `barryvdh/laravel-dompdf` (`^3.1`), `smalot/pdfparser` (`^2.13`)
- **Spreadsheets:** Native PHP `ZipArchive` multi-sheet `.xlsx` & `.csv` generation
- **Authentication & Security:** Session auth, Email OTP verification, SQL-safe password complexity enforcement, Role-based middleware (`admin`, `teacher`, `student`)

---

## Key Features

### 1. Admin Portal
- **User Management (`/admin/users`)**: Create, edit, search, filter, and toggle user accounts (`student`, `teacher`, `admin`).
- **Bulk CSV Import**: Import users with automatic department normalization (`SIT`, `SOA`, `SOE`, `SBHTM`), minimum 5-digit School ID validation, and automatic default passwords (`LASTNAME` + `SCHOOL_ID`, e.g., `TOLENTINO47696`).
- **System Activity Logs (`/admin/logs`)**: Tracks failed logins, account changes, password updates, auto-submissions, and report exports.
- **Dashboard (`/admin/dashboard`)**: Displays live database latency, active exam sessions, 24-hour security events chart, and mailer status.

### 2. Teacher Portal
- **Subjects & Sections (`/teacher/subjects`, `/teacher/sections`)**: Manage subjects, create uppercase-normalized sections without duplicates, lock/unlock rosters, and manage enrolled students.
- **Question Bank Library (`/teacher/question-banks`)**: Upload and manage reusable question banks (`smalot/pdfparser` supported) with a minimum 10-item requirement.
- **Quiz Setup & Editing (`/teacher/quizzes`)**:
  - Create quizzes manually or from an existing Question Bank, and edit existing quizzes anytime.
  - Enforces **SIT Transmutation Table (No Exam - 65)** point totals (`10, 15, 20, ..., 100` pts; passing at `>= 75%` transmuted).
  - Configurable options: time limit, shuffle questions, shuffle choices, **allow post-quiz answer review**, **prevent backtracking**, and **allowed violation limit**.
- **Quiz Results & Analytics (`/teacher/quizzes/{id}/results`)**:
  - Distinct student submission counts (displaying each student's highest-scoring try) with a modal to inspect all tries.
  - Visual **Chart.js** graphs for student scores and per-question accuracy (with percentage tooltips).
  - Wayground-style question accuracy pills (`2/10 correct`), average time per question, student search, and dynamic violation-count filter.
  - Detailed attempt inspection (`/teacher/attempts/{id}`) showing student answers (`A - Term`), correct answers, and proctoring violation logs with duration.
- **Bundled Reports & Email Export (`/teacher/reports`)**:
  - Filter teacher quizzes by **Subject**, **Section**, and **Date Range**.
  - Export filtered reports directly to the teacher's email as **exclusively PDF + CSV** (for single quizzes) or **multi-page PDF + multi-sheet `.xlsx` workbook** (one page/sheet per quiz for bundled exports).

### 3. Student Portal
- **Dashboard & Quizzes (`/student/dashboard`, `/student/quizzes`)**: Join quizzes by access code, track Available/Upcoming/Completed quizzes, and auto-enroll into the quiz section upon starting.
- **Proctored Exam Room (`/student/attempts/{id}`)**:
  - Enforces full-screen mode and blocks browser side-panels or split-screen overlays via viewport-to-screen checks.
  - Detects and logs **tab switching**, **window switching**, **full-screen exit**, and **split-screen resize** events with burst deduplication.
  - Automatically submits and locks the attempt when violations exceed the teacher's allowed limit or when the timer expires.
  - Prevents advancing with unanswered questions and supports optional no-backtracking lock.
- **Practice Reviewer (`/student/reviewer`)**: Ungraded flashcard-style practice mode scoped strictly to question banks from the student's enrolled subjects.
- **Profile, Quiz History & Review (`/student/profile`)**:
  - View transmuted quiz history and open an interactive **Answer Review** modal (when enabled by the teacher), with **Download PDF** and **Email PDF** export options.
  - Enforces custom password setup after email/OTP verification with eye toggles, disabled copy/paste, and SQL-safe special character validation.

---

## Database Schema Overview

Core tables managed in MySQL (HeidiSQL):
- `users`, `activity_logs`
- `subjects`, `sections`, `section_students`
- `question_banks`, `questions`
- `quizzes`, `attempts`, `student_answers`, `violation_logs`

---


## Local Setup & Installation (Laragon + HeidiSQL)

### 1. Place Project in Laragon
Clone or move the project folder inside Laragon's `www` directory (e.g., `C:\laragon\www\quizguard`):

```bash
cd D:\laragon\www
git clone [https://github.com/jct05/QUIZGUARD.git](https://github.com/jct05/QUIZGUARD.git)
cd QUIZGUARD
```

### 2. Start Laragon Services

Open Laragon and click **Start All** to start Apache/Nginx and MySQL. (Ensure Laragon is using PHP 8.3+.)

### 3. Create Database in HeidiSQL

1. In Laragon, click **Database** to launch HeidiSQL.
2. Connect to your local MySQL server (`127.0.0.1`, port `3306`, user `root`, empty password by default in Laragon).
3. Right-click the connection in the left panel → **Create new** → **Database**.
4. Name it `quizguard` (collation `utf8mb4_unicode_ci`) and click **OK**.

### 4. Install Dependencies & Configure .env

Open Laragon's Terminal inside `D:\laragon\www\QUIZGUARD` (or your project directory):

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```
Update your .env file with your Laragon MySQL and SMTP mail settings (required for OTP verification and emailed PDF/CSV reports):

APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Asia/Manila

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quizguard
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file

QUEUE_CONNECTION=database

CACHE_STORE=file

MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-dwcc.quizguard@gmail.com
MAIL_PASSWORD="kwsbyfpsbejuetjg"
MAIL_FROM_ADDRESS="dwcc.quizguard@gmail.com"
MAIL_FROM_NAME="Quizguard"

### 5. Run Database Migrations

Ensure your `.env` database settings match the `quizguard` database you created in HeidiSQL, then run:

```bash
php artisan migrate --seed
```

### 6. Run the Application

**Via Artisan server:**

```bash
php artisan serve
```

## Useful Artisan Commands

```bash
# Clear compiled Blade views after editing templates
php artisan view:clear

# Clear configuration cache after editing .env or config files
php artisan config:clear

# Clear application, config, route, and view caches at once
php artisan optimize:clear

# Re-run seeders standalone
php artisan db:seed

# Drop all tables, re-run migrations, and re-seed (destructive: wipes existing data)
php artisan migrate:fresh --seed

# Inspect registered application routes
php artisan route:list
```

## License

Created as an academic project/capstone/coursework output for **Systems Integration** and **Advanced Database** at **Divine Word College of Calapan (DWCC)**.
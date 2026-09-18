# User Manual

> Last updated: 2026-09-18
> Update this file when the install steps or any role's screens or flow change.

---

The material below is reproduced from the appendices of the project report, so that the installation and
day-to-day usage instructions live in the documentation hub and not only in the submitted document.

> **Environment note:** A2.4–A2.5 describe the XAMPP + MySQL development setup used for this project. `.env.example`
> as shipped in the repository defaults to `DB_CONNECTION=sqlite`; either connection works, as long as the `DB_*`
> block matching it is the one left uncommented. See [`setup-and-conventions.md`](setup-and-conventions.md).

## Appendix 2: Installation Manual

### A2.1 Prerequisites

Grail is a standard Laravel application and can be installed on any machine that meets the following minimum requirements. The development and testing environment used for this project was a local XAMPP stack on Windows, and the instructions below are written for that setup; the same steps apply with trivial changes on macOS or Linux.

· XAMPP 8.2.x (bundles Apache 2.4 and MySQL 8.0/MariaDB) - or a standalone Apache/Nginx and MySQL 8.0 install.

· PHP 8.2 or later, with the extensions Laravel requires (mbstring, openssl, PDO, pdo_mysql, tokenizer, xml, ctype, json, bcmath) - all enabled by default in XAMPP.

· Composer 2.x, for installing PHP (backend) dependencies.

· Node.js 18 LTS or later with npm, for building the Tailwind CSS front end.

· Git (optional), for cloning the repository instead of copying a ZIP archive.

### A2.2 Obtaining the Source Code

Place the project inside the XAMPP web root so Apache can serve it, then move into that directory for the remaining steps:

```bash
cd C:\xampp\htdocs

git clone <repository-url> grail

cd grail
```

### A2.3 Installing Dependencies

Install the PHP and JavaScript dependencies declared in composer.json and package.json:

```bash
composer install
npm install
```

### A2.4 Environment Configuration

Copy the example environment file and generate the application encryption key. Then edit .env so the database connection matches the local MySQL instance created in XAMPP. The database itself, grail_db, is created empty in the next step by the migrator, not by hand:

```bash
copy .env.example .env
php artisan key:generate
```

Then configure the following values in .env:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=grail_db
DB_USERNAME=root
DB_PASSWORD=
```

Before continuing, start the Apache and MySQL services from the XAMPP Control Panel and confirm MySQL is listening on port 3306. The most common installation failure at the next step is simply that MySQL has not been started yet.

### A2.5 Database Migration and Seeding

Running the migrations builds every table described in the schema documentation in dependency order; seeding then populates them with a representative working dataset so the system is immediately usable for demonstration and testing rather than starting empty:

```bash
php artisan migrate
php artisan db:seed
```

> **MySQL note:** `php artisan migrate` does not create the MySQL schema — create `grail_db` once (phpMyAdmin, or
> `CREATE DATABASE grail_db;`) before migrating. On SQLite the file has to exist first as well
> (`touch database/database.sqlite`).

The seeder creates a single default administrator account, printed to the console as it runs. Its credentials are:

- Email: admin@grail.school
- Password: 12345678

These credentials are intentionally simple for a development install and must be changed immediately after the first login on any deployment a real user will access.

### A2.6 Building the Front-End Assets

Grail’s views are styled with Tailwind CSS and compiled through Vite. For a one-off production build:

```bash
npm run build
```

During development it is more convenient to run the Vite dev server alongside the application so style and script changes reload automatically:

```bash
npm run dev
```

### A2.7 Starting the Application

Two equivalent ways of serving the application were used over the course of this project. Through XAMPP’s Apache, the application is reachable at:

http://localhost/grail/public

Alternatively, Laravel’s own lightweight development server avoids any Apache virtual-host configuration and was the server used for the screenshots in this report:

```bash
php artisan serve
```

This serves the application at:

http://127.0.0.1:8000

### A2.8 Troubleshooting

The table below lists the installation problems actually encountered while setting up and running Grail during development, and how each was resolved.

| Symptom                                                                                                                     | Likely Cause                                                                                                          | Resolution                                                                                                                                                                                                                                 |
| --------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| SQLSTATE[HY000] [2002] on any page that queries the database (e.g. “No such file or directory” / a socket permission error) | MySQL is not running, or is listening on a different port/socket than the one in .env                                 | Open the XAMPP Control Panel and confirm the MySQL module shows “Running” (start it if not). Check C:\xampp\mysql\data\mysql_error.log for the reason it failed to start - most often another MySQL service, or port 3306, already in use. |
| “SQLSTATE[42S02]: Base table or view not found” while running migrations                                                    | A migration ran out of dependency order (for example, a foreign key referencing a table created by a later migration) | Run php artisan migrate:fresh to rebuild all tables from scratch in filename (timestamp) order rather than migrating incrementally over a partial schema.                                                                                  |
| Blank or unstyled pages, or a 404 for a .css or .js file                                                                    | The Tailwind/Vite build has not been run, or the dev server was stopped                                               | Run npm run build for a static build, or keep npm run dev running alongside php artisan serve during development.                                                                                                                          |
| “could not find driver” for pdo_mysql                                                                                       | The pdo_mysql PHP extension is disabled                                                                               | In php.ini, uncomment extension=pdo_mysql and restart Apache from the XAMPP Control Panel.                                                                                                                                                 |
| 419 Page Expired on login                                                                                                   | The session/cache tables are stale or APP_KEY is unset                                                                | Confirm php artisan key:generate has been run, then clear cached config: php artisan config:clear.                                                                                                                                         |

---

## Appendix 3: User Manual

### A3.1 Signing In

Every user, regardless of role, signs in from the same login screen with the email address and password issued to them. Grail then redirects the user to the dashboard for their role - Administrator, Teacher, Parent or Student - and every screen described below is reached from that dashboard’s navigation. A user who is deactivated by an administrator cannot sign in until reactivated.

### A3.2 Administrator

· Dashboard - a summary view of school-wide counts (students, teachers, classes) and shortcuts into the modules below.

· Students - register a new student, edit their profile and class assignment, and view or soft-delete an existing record; deleted students are retained (not erased) so historical attendance, grades and fee records stay intact.

· Teachers - register a teacher, set which subjects they are qualified to teach (independent of any class), assign them to teach a specific subject in a specific class, and optionally make them the homeroom teacher of a class. These are three distinct actions: a subject qualification alone does not put a teacher in front of a class, and a class can only have one homeroom teacher at a time, assigned either from this screen or from the Classes screen described next.

· Classes - create a class (for example, “Grade 10A”), attach it to a grade level, and set or change its homeroom teacher from a dropdown of registered teachers, independently of that teacher’s own qualifications screen.

· Subjects - maintain the master list of subjects offered by the school.

· Grade Levels - maintain the ordered list of grade levels (for example, Grade 8 through Grade 12) that classes and timetable periods are organised around.

· Parents/Guardians - register a parent account and link it to one or more students, so that a single login can see every one of their children.

· Fees - create a fee for a student built up from individual line items (tuition, boarding, activity fees, etc.), which the system totals automatically; edit or delete a fee; look up a fee instantly from the payment reference a parent quotes; act on many fees at once (mark cleared, mark overdue, send a reminder, export to CSV, or delete) from the fees list; and manage the fee category list used when building line items.

· User Accounts - activate or deactivate a login, force a password reset, or change a user’s role.

· Audit Logs - review a read-only trail of who created, changed or deleted a record and when, for accountability over sensitive actions such as grade changes and fee adjustments.

· Announcements - publish a notice targeted at a specific role, grade level or class, or the whole school.

· Academic Calendar - maintain academic years and the terms within them, and mark which one is current so the rest of the system (grades, fees, timetables) defaults to it.

· Timetable - build each class’s weekly timetable by placing a subject and teacher into a period/day slot, copy a timetable between terms, or clear it.

· Promotions - move a cohort of students from one class or grade level to the next at the end of an academic year, with the mapping reviewable before it is applied and reversible via rollback if a mistake is made.

· Report Cards - oversight of term report cards across the school (see also A3.3, where the class teacher finalises them).

### A3.3 Teacher

· Dashboard - the classes and subjects assigned to the signed-in teacher, and shortcuts into today’s tasks.

· Attendance - mark each student in a class-subject Present, Absent or Late for a given date; every entry is attributed to the recording teacher.

· Marks - enter continuous-assessment and examination scores for a class-subject against the term’s maximum score; entries are validated against that maximum before being saved.

· Assignments - create an assignment for a class-subject, review and grade student submissions.

· Class Roster - the list of students in a class the teacher is the homeroom teacher for, or teaches a subject in.

· Timetable - the teacher’s own weekly schedule, drawn from the slots an administrator has built.

· Report Cards - for a homeroom teacher only: add a class-teacher comment to each student’s term report card, then finalise the term - which computes each student’s term average and class rank and locks the card so parents and students can see it. A finalised term can be reopened by an administrator if a correction is needed.

· Performance - a summary of how a class or subject is trending over a term, to help spot students who need attention before report cards are finalised.

### A3.4 Parent

· Dashboard - an overview for the currently selected child.

· Child switcher - a parent linked to more than one student at the school can switch between children from a single login; every other screen reflects whichever child is currently selected.

· Timetable - the selected child’s weekly class timetable.

· Attendance - the selected child’s attendance history.

· Performance - a running view of the selected child’s marks across the term, ahead of the formal report card.

· Reports - view and download the selected child’s finalised report card for a chosen term and year once the class teacher has finalised it.

· Assignments - the selected child’s outstanding and past assignments.

· Fees - the selected child’s fee balance and payment history, including the printable payment reference used to pay at the school office or by mobile money, and a receipt for each recorded payment.

· Announcements - notices published to the parent’s role, or to their child’s class or grade level.

· Settings - update contact details and change password.

### A3.5 Student

· Dashboard - a personal summary for the signed-in student.

· Results - the student’s own marks and, once finalised, term report cards.

· Assignments - outstanding and past assignments and, where enabled, online submission.

· Timetable - the student’s own weekly class timetable.

### A3.6 Registration and Access Model

Grail does not create active student or parent accounts immediately upon sign-up. Instead, registration is review-based and follows a formal approval workflow managed by the administrator.

A new user submits a registration request through the public sign-up form. The request is stored in the system as a pending registration record and remains inaccessible until it is reviewed. An administrator can then approve the request, which creates the corresponding account and links it to the appropriate parent or student record, or reject it if the details are incomplete or not suitable for admission. This process ensures that account creation remains auditable and consistent with the school’s admission and governance procedures.

### A3.7 Default Credentials and Security Note

The default seeded administrator account is:

- Email: admin@grail.school
- Password: 12345678

This account is intended for local development and demonstration only. It should be changed immediately after the first login on any real or shared environment, and all production deployments should enforce a stronger password policy and secure hosting configuration.

### A3.8 Quick Start Summary

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run dev
php artisan serve
```

The application is then available through the local development URL or through the XAMPP-hosted URL depending on the deployment method chosen by the user.

---

End of Appendix 3.

> See also: [`setup-and-conventions.md`](setup-and-conventions.md) (environment, conventions and gotchas) and
> [`assets-and-icons.md`](assets-and-icons.md) (why the icon fonts are served locally).

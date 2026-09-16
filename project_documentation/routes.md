# Routes

> Last updated: 2026-09-16
> Update this file when routes are added or modified.

---

## 7. Routes

`routes/web.php` (domain routes) + `routes/auth.php` (Breeze + registration). **224 routes** in total
(`php artisan route:list`). Every domain group below is wrapped in `auth` plus `role:<role>`.

> **Note:** no `/profile` routes are registered any more. `ProfileController` still exists in
> `app/Http/Controllers/`, but user profile editing now happens through each portal's `settings` action.

### 7.1 Public

| Method | URI          | Name        | Middleware       | Action                          |
| ------ | ------------ | ----------- | ---------------- | ------------------------------- |
| GET    | `/`          | —           | none             | returns `welcome` view          |
| GET    | `/welcome`   | `welcome`   | none             | returns `welcome` view          |
| GET    | `/up`        | —           | none             | Laravel health check            |
| GET    | `/dashboard` | `dashboard` | `auth, verified` | `DashboardController@index` (redirects by role) |

### 7.2 Admin — prefix `admin`, name `admin.`, middleware `auth, role:admin`

| Method          | URI                                                    | Name                                    | Action                                              |
| --------------- | ------------------------------------------------------ | --------------------------------------- | --------------------------------------------------- |
| GET             | `/admin/dashboard`                                     | `admin.dashboard`                       | `DashboardController@adminDashboard`                |
| GET             | `/admin/settings`                                      | `admin.settings`                        | `AdminController@settings`                          |
| GET             | `/admin/examinations`                                  | `admin.examinations`                    | `AdminController@examinations`                      |
| Resource        | `/admin/teachers`                                      | `admin.teachers.*`                      | `AdminTeacherController`                            |
| POST            | `/admin/teachers/{teacher}/assignments`                | `admin.teachers.assign`                 | `AdminTeacherController@assignSubject`              |
| DELETE          | `/admin/teachers/{teacher}/assignments/{classSubject}` | `admin.teachers.unassign`               | `AdminTeacherController@unassignSubject`            |
| Resource        | `/admin/parents`                                       | `admin.parents.*`                       | `AdminParentController`                             |
| Resource        | `/admin/classes`                                       | `admin.classes.*`                       | `AdminClassController`                              |
| Resource        | `/admin/subjects`                                      | `admin.subjects.*`                      | `AdminSubjectController`                            |
| GET             | `/admin/students/export`                               | `admin.students.export`                 | `AdminController@export`                            |
| Resource        | `/admin/students`                                      | `admin.students.*`                      | `AdminController`                                   |
| GET             | `/admin/students/{student}/financials`                 | `admin.students.financials`             | `ReportController@studentFinancials`                |
| GET             | `/admin/students/{student}/statement`                  | `admin.students.statement`              | `ReportController@statement`                        |
| POST            | `/admin/students/{student}/refund-credit`              | `admin.students.refund-credit`          | `AdminController@refundCredit`                      |
| Resource        | `/admin/fees`                                          | `admin.fees.*`                          | `FeeController`                                     |
| GET             | `/admin/fees-lookup`                                   | `admin.fees.lookup`                     | `FeeController@lookup`                              |
| POST            | `/admin/fees/bulk-action`                              | `admin.fees.bulk-action`                | `FeeController@bulkAction`                          |
| POST            | `/admin/fees/{fee}/send-reminder`                      | `admin.fees.send-reminder`              | `FeeController@sendReminder`                        |
| POST            | `/admin/fees/{fee}/payments`                           | `admin.fees.payments.store`             | `PaymentController@store`                           |
| GET             | `/admin/payments/{payment}/receipt`                    | `admin.payments.receipt`                | `PaymentController@receipt`                         |
| GET             | `/admin/payment-submissions`                           | `admin.payment-submissions.index`       | `PaymentSubmissionController@index`                 |
| GET             | `/admin/payment-submissions/{submission}`              | `admin.payment-submissions.show`        | `PaymentSubmissionController@show`                  |
| POST            | `/admin/payment-submissions/{submission}/approve`      | `admin.payment-submissions.approve`     | `PaymentSubmissionController@approve`               |
| POST            | `/admin/payment-submissions/{submission}/reject`       | `admin.payment-submissions.reject`      | `PaymentSubmissionController@reject`                |
| GET             | `/admin/registration-requests`                         | `admin.registration-requests.index`     | `RegistrationRequestController@index`               |
| GET             | `/admin/registration-requests/{registrationRequest}`   | `admin.registration-requests.show`      | `RegistrationRequestController@show`                |
| POST            | `/admin/registration-requests/{registrationRequest}/approve` | `admin.registration-requests.approve` | `RegistrationRequestController@approve` |
| POST            | `/admin/registration-requests/{registrationRequest}/reject`  | `admin.registration-requests.reject`  | `RegistrationRequestController@reject`  |
| GET             | `/admin/report-cards`                                  | `admin.report-cards.index`              | `ReportCardController@index`                        |
| GET             | `/admin/report-cards/{student}`                        | `admin.report-cards.show`               | `ReportCardController@show`                         |
| POST            | `/admin/report-cards/{class}/unfinalize`               | `admin.report-cards.unfinalize`         | `ReportCardController@unfinalize`                   |
| GET             | `/admin/announcements/preview`                         | `admin.announcements.preview`           | `AnnouncementController@preview`                    |
| Resource        | `/admin/announcements` (except `show`)                 | `admin.announcements.*`                 | `AnnouncementController`                            |
| GET             | `/admin/promotions`                                    | `admin.promotions.index`                | `PromotionController@index`                         |
| GET             | `/admin/promotions/mappings`                           | `admin.promotions.mappings`             | `PromotionController@mappings`                      |
| PUT             | `/admin/promotions/mappings`                           | `admin.promotions.mappings.save`        | `PromotionController@saveMappings`                  |
| GET             | `/admin/promotions/class/{class}`                      | `admin.promotions.show`                 | `PromotionController@show`                          |
| POST            | `/admin/promotions/class/{class}`                      | `admin.promotions.store`                | `PromotionController@store`                         |
| POST            | `/admin/promotions/{batch}/rollback`                   | `admin.promotions.rollback`             | `PromotionController@rollback`                      |
| GET/POST/PUT/DELETE | `/admin/settings/categories` (+ `/{feeCategory}`)  | `admin.categories.*`                    | `FeeCategoryController`                             |
| GET             | `/admin/settings/payments`                             | `admin.settings.payments`               | `PaymentSettingsController@edit`                    |
| PUT             | `/admin/settings/payments`                             | `admin.settings.payments.update`        | `PaymentSettingsController@update`                  |
| GET             | `/admin/audit-logs`                                    | `admin.audit-logs.index`                | `AuditLogController@index`                          |
| GET             | `/admin/users`                                         | `admin.users.index`                     | `UserAccountController@index`                       |
| PUT             | `/admin/users/{user}/status`                           | `admin.users.status`                    | `UserAccountController@toggleActive`                |
| PUT             | `/admin/users/{user}/reset-password`                   | `admin.users.reset-password`            | `UserAccountController@resetPassword`               |
| PUT             | `/admin/users/{user}/role`                             | `admin.users.role`                      | `UserAccountController@updateRole`                  |
| Resource        | `/admin/academic-years`                                | `admin.academic-years.*`                | `AcademicYearController`                            |
| Resource        | `/admin/terms`                                         | `admin.terms.*`                         | `TermController`                                    |
| Resource        | `/admin/holidays`                                      | `admin.holidays.*`                      | `HolidayController`                                 |
| Resource        | `/admin/grade-levels`                                  | `admin.grade-levels.*`                  | `GradeLevelController`                              |
| Resource        | `/admin/periods`                                       | `admin.periods.*`                       | `PeriodController`                                  |
| GET             | `/admin/timetable`                                     | `admin.timetable.index`                 | `TimetableController@index`                         |
| POST            | `/admin/timetable/slots`                               | `admin.timetable.slots.store`           | `TimetableController@store`                         |
| POST            | `/admin/timetable/copy`                                | `admin.timetable.copy`                  | `TimetableController@copyToTerm`                    |
| POST            | `/admin/timetable/clear`                               | `admin.timetable.clear`                 | `TimetableController@clear`                         |
| GET             | `/admin/reports/fee-collection`                        | `admin.reports.fee-collection`          | `ReportController@feeCollection`                    |
| GET             | `/admin/reports/fee-collection/export`                 | `admin.reports.fee-collection.export`   | `ReportController@exportFeeCollection`              |
| GET             | `/admin/reports/attendance`                            | `admin.reports.attendance`              | `AnalyticsController@attendance`                    |
| GET             | `/admin/reports/attendance/export`                     | `admin.reports.attendance.export`       | `AnalyticsController@exportAttendance`              |
| GET             | `/admin/reports/aging`                                 | `admin.reports.aging`                   | `AnalyticsController@aging`                         |
| GET             | `/admin/reports/aging/export`                          | `admin.reports.aging.export`            | `AnalyticsController@exportAging`                   |
| GET             | `/admin/reports/school-wide`                           | `admin.reports.school-wide`             | `AnalyticsController@schoolWide`                    |
| GET             | `/admin/reports/school-wide/export`                    | `admin.reports.school-wide.export`      | `AnalyticsController@exportSchoolWide`              |
| PUT             | `/admin/reports/school-wide/threshold`                 | `admin.reports.school-wide.threshold`   | `AnalyticsController@saveThreshold`                 |

### 7.3 Teacher — prefix `teacher`, name `teacher.`, middleware `auth, role:teacher`

| Method   | URI                                                             | Name                                     | Action                                          |
| -------- | --------------------------------------------------------------- | ---------------------------------------- | ----------------------------------------------- |
| GET      | `/teacher/dashboard`                                            | `teacher.dashboard`                      | `TeacherController@dashboard`                   |
| GET      | `/teacher/marks`                                                | `teacher.marks`                          | `TeacherController@marks`                       |
| POST     | `/teacher/marks`                                                | `teacher.marks.store`                    | `TeacherController@storeMarks`                  |
| GET      | `/teacher/classes`                                              | `teacher.classes`                        | `TeacherController@classes`                     |
| GET      | `/teacher/classes/{class}/roster`                               | `teacher.classes.roster`                 | `TeacherController@roster`                      |
| GET      | `/teacher/students/{student}`                                   | `teacher.students.show`                  | `TeacherController@studentProfile`              |
| GET      | `/teacher/timetable`                                            | `teacher.timetable`                      | `TeacherController@timetable`                   |
| GET      | `/teacher/attendance`                                           | `teacher.attendance`                     | `TeacherController@attendance`                  |
| POST     | `/teacher/attendance`                                           | `teacher.attendance.store`               | `TeacherController@storeAttendance`             |
| GET      | `/teacher/performance`                                          | `teacher.performance`                    | `TeacherController@performance`                 |
| POST     | `/teacher/performance/finalize`                                 | `teacher.performance.finalize`           | `TeacherController@finalizeGrades`              |
| POST     | `/teacher/performance/unfinalize-request`                       | `teacher.performance.unfinalize-request` | `TeacherController@unfinalizeRequest`           |
| GET      | `/teacher/announcements`                                        | `teacher.announcements`                  | `TeacherController@announcements`               |
| POST     | `/teacher/announcements/read-all`                               | `teacher.announcements.read-all`         | `TeacherController@readAllAnnouncements`        |
| POST     | `/teacher/announcements/{announcement}/read`                    | `teacher.announcements.read`             | `TeacherController@readAnnouncement`            |
| GET      | `/teacher/settings`                                             | `teacher.settings`                       | `TeacherController@settings`                    |
| PATCH    | `/teacher/settings`                                             | `teacher.settings.update`                | `TeacherController@updateSettings`              |
| GET      | `/teacher/assignments`                                          | `teacher.assignments.index`              | `Teacher\AssignmentController@index`            |
| GET      | `/teacher/assignments/create`                                   | `teacher.assignments.create`             | `Teacher\AssignmentController@create`           |
| POST     | `/teacher/assignments`                                          | `teacher.assignments.store`              | `Teacher\AssignmentController@store`            |
| GET      | `/teacher/assignments/{assignment}/edit`                        | `teacher.assignments.edit`               | `Teacher\AssignmentController@edit`             |
| PUT      | `/teacher/assignments/{assignment}`                             | `teacher.assignments.update`             | `Teacher\AssignmentController@update`           |
| DELETE   | `/teacher/assignments/{assignment}`                             | `teacher.assignments.destroy`            | `Teacher\AssignmentController@destroy`          |
| GET      | `/teacher/assignments/{assignment}/submissions`                 | `teacher.assignments.submissions`        | `Teacher\AssignmentController@submissions`      |
| PUT      | `/teacher/assignments/{assignment}/submissions/{submission}`    | `teacher.assignments.grade`              | `Teacher\AssignmentController@grade`            |
| GET      | `/teacher/report-cards`                                         | `teacher.report-cards.index`             | `Teacher\ReportCardController@index`            |
| GET      | `/teacher/report-cards/class/{class}`                           | `teacher.report-cards.show`              | `Teacher\ReportCardController@show`             |
| POST     | `/teacher/report-cards/class/{class}/finalize`                  | `teacher.report-cards.finalize`          | `Teacher\ReportCardController@finalize`         |
| POST     | `/teacher/report-cards/class/{class}/students/{student}/comment`| `teacher.report-cards.comment`           | `Teacher\ReportCardController@saveOverallComment` |
| GET      | `/teacher/report-cards/class/{class}/students/{student}/preview`| `teacher.report-cards.preview`           | `Teacher\ReportCardController@preview`          |
| GET      | `/teacher/report-cards/subjects/{classSubject}`                 | `teacher.report-cards.subjects`          | `Teacher\ReportCardController@subjectComments`  |
| POST     | `/teacher/report-cards/subjects/{classSubject}`                 | `teacher.report-cards.subjects.save`     | `Teacher\ReportCardController@saveSubjectComments` |

### 7.4 Parent — prefix `parent`, name `parent.`, middleware `auth, role:parent`

| Method | URI                                              | Name                           | Action                                        |
| ------ | ------------------------------------------------ | ------------------------------ | --------------------------------------------- |
| GET    | `/parent/dashboard`                              | `parent.dashboard`             | `ParentController@dashboard`                  |
| GET    | `/parent/children`                               | `parent.children`              | `ParentController@children`                   |
| POST   | `/parent/children/switch`                        | `parent.switch-child`          | `ParentController@switchChild`                |
| GET    | `/parent/attendance`                             | `parent.attendance`            | `ParentController@attendance`                 |
| GET    | `/parent/performance`                            | `parent.performance`           | `ParentController@performance`                |
| GET    | `/parent/reports`                                | `parent.reports`               | `ParentController@reports`                    |
| GET    | `/parent/timetable`                              | `parent.timetable`             | `ParentController@timetable`                  |
| GET    | `/parent/assignments`                            | `parent.assignments`           | `ParentController@assignments`                |
| GET    | `/parent/fees`                                   | `parent.fees`                  | `ParentController@fees`                       |
| GET    | `/parent/fees/{fee}`                             | `parent.fees.show`             | `ParentController@showFee` (ownership check → redirect to Fees) |
| GET    | `/parent/payments/{payment}/receipt`             | `parent.payments.receipt`      | `ParentController@receipt`                    |
| POST   | `/parent/payment-proofs`                         | `parent.payment-proofs.store`  | `Parent\PaymentSubmissionController@store`    |
| GET    | `/parent/children/{student}/report-cards/{term}` | `parent.report-card`           | `ParentController@reportCard` (finalized only) |
| GET    | `/parent/announcements`                          | `parent.announcements`         | `ParentController@announcements`              |
| POST   | `/parent/announcements/read-all`                 | `parent.announcements.read-all`| `ParentController@readAllAnnouncements`       |
| POST   | `/parent/announcements/{announcement}/read`      | `parent.announcements.read`    | `ParentController@readAnnouncement`           |
| GET    | `/parent/settings`                               | `parent.settings`              | `ParentController@settings`                   |
| PATCH  | `/parent/settings`                               | `parent.settings.update`       | `ParentController@updateSettings`             |

### 7.5 Student — prefix `student`, name `student.`, middleware `auth, role:student`

| Method | URI                                          | Name                             | Action                                     |
| ------ | -------------------------------------------- | -------------------------------- | ------------------------------------------ |
| GET    | `/student/dashboard`                         | `student.dashboard`              | `StudentController@dashboard`              |
| GET    | `/student/results`                           | `student.results`                | `StudentController@results`                |
| GET    | `/student/attendance`                        | `student.attendance`             | `StudentController@attendance`             |
| GET    | `/student/timetable`                         | `student.timetable`              | `StudentController@timetable`              |
| GET    | `/student/settings`                          | `student.settings`               | `StudentController@settings`               |
| GET    | `/student/assignments`                       | `student.assignments.index`      | `Student\AssignmentController@index`       |
| GET    | `/student/assignments/{assignment}`          | `student.assignments.show`       | `Student\AssignmentController@show`        |
| POST   | `/student/assignments/{assignment}/submit`   | `student.assignments.submit`     | `Student\AssignmentController@submit`      |
| GET    | `/student/report-cards`                      | `student.report-cards`           | `StudentController@reportCards`            |
| GET    | `/student/report-cards/{term}`               | `student.report-card`            | `StudentController@reportCard` (finalized only) |
| GET    | `/student/announcements`                     | `student.announcements`          | `StudentController@announcements`          |
| POST   | `/student/announcements/read-all`            | `student.announcements.read-all` | `StudentController@readAllAnnouncements`   |
| POST   | `/student/announcements/{announcement}/read` | `student.announcements.read`     | `StudentController@readAnnouncement`       |

### 7.6 Auth (`routes/auth.php`)

| Method | URI                              | Name                     | Middleware                        | Action                                          |
| ------ | -------------------------------- | ------------------------ | --------------------------------- | ----------------------------------------------- |
| GET    | `/login`                          | `login`                  | web                               | `AuthenticatedSessionController@create`         |
| POST   | `/login`                          | —                        | web, guest                        | `AuthenticatedSessionController@store`          |
| GET    | `/register`                       | `register`               | web, guest                        | `ParentRegistrationController@create`           |
| POST   | `/register`                       | —                        | web, guest, `throttle:5,1`        | `ParentRegistrationController@store`            |
| GET    | `/register/submitted`             | `registration.submitted` | web, guest                        | `ParentRegistrationController@submitted`        |
| GET    | `/forgot-password`                | `password.request`       | web, guest                        | `PasswordResetLinkController@create`            |
| POST   | `/forgot-password`                | `password.email`         | web, guest                        | `PasswordResetLinkController@store`             |
| GET    | `/reset-password/{token}`         | `password.reset`         | web, guest                        | `NewPasswordController@create`                  |
| POST   | `/reset-password`                 | `password.store`         | web, guest                        | `NewPasswordController@store`                   |
| GET    | `/verify-email`                   | `verification.notice`    | web, auth                         | `EmailVerificationPromptController`             |
| GET    | `/verify-email/{id}/{hash}`       | `verification.verify`    | web, auth, signed, `throttle:6,1` | `VerifyEmailController`                         |
| POST   | `/email/verification-notification`| `verification.send`      | web, auth, `throttle:6,1`         | `EmailVerificationNotificationController@store` |
| GET    | `/confirm-password`               | `password.confirm`       | web, auth                         | `ConfirmablePasswordController@show`            |
| POST   | `/confirm-password`               | —                        | web, auth                         | `ConfirmablePasswordController@store`           |
| PUT    | `/password`                       | `password.update`        | web, auth                         | `PasswordController@update`                     |
| POST   | `/logout`                         | `logout`                 | web, auth                         | `AuthenticatedSessionController@destroy`        |

---

_End of routes documentation._

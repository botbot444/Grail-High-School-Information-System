# Grail SIS — User Testing Plan

> For anyone trying out the system by hand — you don't need to know how to
> code to use this. If you're looking for the technical/developer testing
> checklist instead, see `manual-testing-guide.md` in this same folder.

---

## What this is for

This is a set of real, everyday tasks — enroll a student, take attendance,
pay a fee, and so on — written so anyone can pick a role, follow the steps,
and tell us whether the system actually works the way it's supposed to.

You don't need to understand the code. You just need to follow the steps,
compare what you see against what the step says you *should* see, and tell
us when those two things don't match.

## Before you start

- **Where to go:** ask whoever gave you this plan for the web address
  (it'll look something like `http://something/login`).
- **Don't worry about breaking anything.** That's the point of testing —
  finding what's broken *before* real families and teachers rely on it. If
  something goes wrong, that's a successful test, not a mistake.
- **If several of you are testing together**, it's fastest to split up by
  role — one person works through "As an Administrator," another through
  "As a Teacher," and so on — rather than everyone doing the same thing.
- **Write things down as you go.** Don't rely on remembering it later. For
  each step, a quick "✅ worked" or "❌ [what happened instead]" is enough.

### Test accounts

Use these unless you were given different ones:

| Role    | Email                            | Password   |
|---------|-----------------------------------|------------|
| Admin   | `admin@grail.school`             | `12345678` |
| Teacher | `english.language@grail.school`  | `12345678` |
| Parent  | `parent@grail.school`            | `12345678` |
| Student | `student@grail.school`           | `12345678` |

**Important:** if you're switching between two of these accounts in the same
browser window, click **Sign Out** first before logging in as someone else.
Just typing a new email/password into the login page without signing out
first can behave unexpectedly.

---

## How to report what you find

For anything that doesn't work as described, note:

1. **What you were doing** (which step, in which scenario)
2. **What you expected to happen** (copy it from the step)
3. **What actually happened** instead
4. **A screenshot**, if you can — it's the single most useful thing you can
   include

You don't need to diagnose *why* it happened or suggest a fix. "I clicked
Save and the page showed an error" is a complete, useful report on its own.

---

## Scenario index

- [As an Administrator](#as-an-administrator) — enrollment, staffing, fees, the school calendar, timetables
- [As a Teacher](#as-a-teacher) — classes, attendance, marks, assignments, report cards
- [As a Parent](#as-a-parent) — registration, checking on your child, paying fees
- [As a Student](#as-a-student) — your own results, timetable, assignments

---

## As an Administrator

### 1. Enroll a new student

1. Log in as the admin account.
2. Go to **Students** in the sidebar, then **Add Student**.
3. Fill in the student's name, date of birth, gender, and class. Leave the
   "Student Login" email blank for now.
4. Click **Save**. **Expected:** you land back on the student list with a
   confirmation message, and a student number was generated automatically —
   you shouldn't have had to type one in.
5. Open the new student's record and click **Edit**. Fill in the "Student
   Login" email field this time, and save. **Expected:** a one-time password
   is shown on screen — write it down, it won't be shown again.
6. Sign out, and try logging in as that student with the email and password
   from step 5. **Expected:** you're asked to set a new password before you
   can do anything else.

### 2. Add a teacher and assign them to a class

1. As admin, go to **Teachers → Add Teacher**.
2. Fill in their name, email, and optionally tick a class or subject for
   them.
3. Save. **Expected:** a one-time password is shown, same as for students.
4. Open the teacher's profile and use the **Assign a subject** form to give
   them a class + subject combination.
5. Try assigning the *same* class + subject to a *different* teacher.
   **Expected:** you're warned that it's already taught by someone else, and
   asked to confirm before it's handed over.

### 3. Set up the school calendar

1. Go to **School Setup → Academic Years**. Create a new academic year (any
   future dates are fine).
2. Go to **Terms**. Add a term inside that new academic year. Try adding a
   *second* term with overlapping dates. **Expected:** it's rejected with a
   clear message about the overlap.
3. Go to **Holidays**. Add a holiday inside an existing term's date range.
   Then check that term's "school days" count on the Terms page — it should
   have gone down by one.
4. Go to **Periods**. Confirm each grade level has its own list of periods
   (Period 1, Period 2, Break, etc.) and that you can add one.

### 4. Build a class timetable

1. Go to **School Setup → Timetables**.
2. Pick a class and term. For an empty day/period slot, choose a subject and
   teacher, then **Save**.
3. Try assigning a teacher to a slot where they're already teaching a
   *different* class at the same day/time. **Expected:** it's rejected with
   a conflict message, and nothing is saved.
4. Use the **Copy to term…** dropdown and **Copy** button to duplicate a
   class's schedule into a different term, and confirm it actually shows up
   there.

### 5. Record a fee and a payment

1. Go to **Finance → Fees → Add Fee**. Create a fee for a student with at
   least one line item.
2. Open the fee and use **Record Payment** to log a payment against it.
   **Expected:** the balance updates correctly.
3. Try recording a payment *larger* than the balance. **Expected:** the
   extra is kept as account credit for that student, not lost or rejected.

### 6. Review a parent's proof of payment

*(Do this after "As a Parent" scenario 3 below, or ask another tester to do
that scenario first.)*

1. Go to **Finance → Payment Approvals**.
2. Open a pending submission. **Expected:** you can see the proof image/file
   the parent uploaded, and the claimed amount/method/date.
3. **Approve** it. **Expected:** a real payment is recorded and the fee
   balance updates.
4. On a *different* pending submission, **Reject** it with a reason.
   **Expected:** no payment is recorded, and the reason is something the
   parent will be able to see.

### 7. Review a registration request

*(Do this after "As a Parent" scenario 1 below.)*

1. Go to **People → Registration Requests**.
2. Open a pending request and review the parent + child details shown.
3. **Approve** it. **Expected:** you can now find the new student in the
   student list, and the parent's account works with the password they
   chose at sign-up.

### 8. Promote students at year-end

1. Go to **Academics → Promotion**.
2. Pick a class, and for each student choose Promote / Retain / Graduate.
3. Submit. **Expected:** students move to their new class (or are marked
   graduated), and you can find a rollback option if you made a mistake.

### 9. Post an announcement

1. Go to **Announcements → New Announcement**.
2. Write one, choose who it's visible to (e.g. just parents, or just one
   class), and publish it.
3. Log in as a parent or student who should see it and confirm it shows up
   in their **Announcements** page.

---

## As a Teacher

### 1. Take attendance

1. Log in as the teacher account.
2. Go to **Record Attendance**, pick a class, and mark each student Present,
   Absent, or Late.
3. Save. **Expected:** if you view that class's attendance again, your
   marks are still there.

### 2. Enter marks

1. Go to **Enter Marks**, pick a class and subject.
2. Enter scores for a few students and save.
3. **Expected:** the scores show up correctly if you reload the page, and
   (if you can check) the same numbers appear on the student's own results
   page.

### 3. Create and grade an assignment

1. Go to **Assignments → New assignment** for one of your classes.
2. Give it a title, due date, and save.
3. Log in as a student in that class and submit something for it (see
   Student scenario 3 below), then come back as the teacher and grade the
   submission.

### 4. Finalize report cards

1. Go to **Report Cards** for one of your classes.
2. Add any subject comments needed, then **Finalize**.
3. **Expected:** once finalized, the report card becomes visible to the
   student and parent, and you can no longer casually edit the marks that
   went into it (there should be a specific "request unfinalize" step if you
   need to change something).

---

## As a Parent

### 1. Register yourself and a child

1. Sign out of any other account. Go to the login page and click **Register
   you and your child**.
2. Fill in your own details, choose your own password, and fill in your
   child's details (a made-up name and email are fine for testing).
3. Submit. **Expected:** you land on a "submitted for review" page — you are
   **not** logged in, and there's no way to sign in yet with the details you
   just entered.
4. Ask an admin tester to approve or reject it (see Admin scenario 6/7
   above), then try logging in with the password you chose in step 2.

### 2. Check on your child

1. Log in as the parent test account.
2. Look through **Attendance**, **Performance**, and **Reports** for your
   child. **Expected:** the numbers you see here should roughly match what
   the same child's teacher/admin would see for them — nothing wildly
   different.

### 3. Submit proof of payment

1. Go to **Fees**.
2. Scroll to **Submit Proof of Payment**, pick an outstanding fee, fill in
   the amount/method/date, and upload a photo or PDF (any image works for
   testing).
3. Submit. **Expected:** your balance does **not** change yet — you should
   see the submission listed as "Pending Review" further down the page.
4. Have an admin tester approve it (Admin scenario 6), then reload this page
   — your balance should now be updated and the submission should show
   "Approved."

### 4. Update your own settings

1. Go to **Settings**.
2. Change your phone number or address and save.
3. Change your password, then sign out and log back in with the new one to
   confirm it actually took effect.

---

## As a Student

### 1. First login and forced password change

1. Log in with a student account that has a temporary/one-time password
   (created in Admin scenario 1, or given to you by another tester).
2. **Expected:** you're taken straight to a Settings page and told to set a
   new password before doing anything else.
3. Set a new password. **Expected:** you can now use the rest of the
   portal normally.

### 2. View your own information

1. Look through **My Results**, **Attendance**, **Timetable**, and **Report
   Cards**. **Expected:** everything shown is specific to you — no other
   student's data should appear anywhere.

### 3. Submit an assignment

1. Go to **Assignments**, open one that's still open for submission.
2. Add a note and/or attach a file (if the assignment allows files), and
   submit.
3. **Expected:** the assignment now shows as submitted, and you can no
   longer change it once a teacher has graded it.

---

## A note on what "success" looks like

A perfect run where nothing goes wrong is a *fine* outcome, but it's not
actually the most useful one — if you hit something confusing, slow, or
broken, that's exactly the kind of thing this plan exists to catch. Report
it plainly and move on to the next step; you don't need to work around it
or figure out whether it's "worth mentioning."

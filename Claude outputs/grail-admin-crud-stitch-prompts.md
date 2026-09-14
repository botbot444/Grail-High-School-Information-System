# Google Stitch prompts — Admin CRUD pages (Parents, Classes, Subjects)

Grail SIS already has a polished Material Design 3 look on the Students and Teachers admin pages. The Parents, Classes, and Subjects pages still use the original bare-bones scaffolding, so these 10 prompts describe each missing/outdated screen in the same visual language, grounded in the actual fields the backend already supports. Paste one prompt at a time into Stitch — each is self-contained.

Shared style language baked into every prompt below (so keep it consistent if you tweak wording): a clean Material Design 3 admin dashboard — soft off-white background, white/very-light-gray rounded cards (12px+ radius) with subtle shadows, a single indigo/blue accent color used sparingly for primary buttons and active states, small bold uppercase gray micro-labels above form fields, Google's Material Symbols outlined icon set throughout, generous whitespace, and a persistent dark-ish left sidebar + top header (already built — just leave room for them: ~260px sidebar on the left, ~72px header on top).

---

## 1. Create Parent

```
Design an admin dashboard page titled "Add New Parent / Guardian" for a school information system, in a clean Material Design 3 style.

Layout: persistent left sidebar (~260px, dark surface, nav icons + labels) and a top header bar (~72px) — assume these already exist, focus the design on the main content area next to them. Inside the main content area:

- A breadcrumb trail at the top: "Dashboard / Parents & Guardians / Add New"
- Page title "Add New Parent / Guardian" with a one-line gray subtitle underneath, and on the right a "Cancel" outlined button
- A two-column form below (roughly 60/40 split):
  - Left column, card "Personal Information": First Name and Last Name text inputs side by side, then a full-width Address textarea, then Occupation and National ID text inputs side by side
  - Right column, top card "Contact Information": Email input (with a small mail icon inside the field) and Phone input (with a phone icon inside the field)
  - Right column, below it, card "Link Children (optional)": a search box, then a scrollable checkbox grid/list of student cards (each showing student avatar initials, full name, and a small student ID chip) that admin can tick to link as this parent's children
  - Right column, bottom: a soft info banner card with a key icon, explaining "A secure one-time password will be generated automatically and shown after the account is created. The parent will be required to set their own password on first login."
- A sticky bottom-right action bar with "Cancel" and a primary indigo "Create Parent" button with a person-add icon
- Include a subtle success toast mockup in the bottom-right corner (dark rounded pill with a checkmark icon and short text) to show the confirmation state
```

---

## 2. Edit Parent

```
Design an admin dashboard page titled "Edit Parent / Guardian" for a school information system, in the same clean Material Design 3 style as the Add New Parent page (off-white background, rounded white cards, indigo accent, Material Symbols icons, persistent left sidebar + top header already exist off-canvas).

Layout:
- Breadcrumb: "Dashboard / Parents & Guardians / Maria Banda / Edit"
- Page title "Edit Parent / Guardian" with the parent's name as subtitle, "Cancel" outlined button top-right
- Two-column form (60/40 split), all fields pre-filled with example data:
  - Left column, card "Personal Information": First Name, Last Name, Address (textarea), Occupation, National ID — same layout as create
  - Right column, top card "Contact Information": Email and Phone, pre-filled
  - Right column, card "Account": read-only rows showing the linked login email and "Member since [date]", plus a separate secondary button "Reset Password" (distinct from the save action below, styled as an outlined warning-tinted button with a lock-reset icon)
  - Right column, card "Linked Children": the same searchable checkbox grid of students as the create page, but with this parent's current children already checked/highlighted
- Sticky bottom-right action bar: "Cancel" and a primary "Save Changes" button with a save icon
```

---

## 3. View Parent / Guardian (profile page)

```
Design an admin dashboard "Parent Profile" page for a school information system, Material Design 3 style, off-white background, rounded white cards, indigo accent, Material Symbols outlined icons, persistent left sidebar + top header already exist off-canvas. This is a read-only detail view reached by clicking a "view" eye icon on the Parents table — it should follow the same profile-page pattern already used for student and teacher profiles in this app: a header card followed by tabbed content.

Layout:
- Breadcrumb "Dashboard / Parents & Guardians / Maria Banda"
- A profile header card: a short light-indigo banner strip across the top, a large circular avatar with initials overlapping the banner on the left, the parent's full name in bold next to it, a subtitle "Guardian · 2 linked children", and a small status pill ("Active" in green, or "Password Reset Required" in amber). Top-right of this card: an outlined "Edit" button with a pencil icon and a plain text "Back to Parents" link
- Below the header card, a row of tabs: "Overview", "Linked Children", "Account & Security" (Overview active/underlined by default)
- Overview tab content: a card with a two-column definition-list style grid showing Email, Phone, Address, Occupation, and National ID, each with a small leading icon and gray uppercase micro-label above the value
- Linked Children tab content: a grid of child cards, each showing the student's avatar initials, full name, class name, and student ID chip, with the whole card clickable/hoverable (implying it links to that student's own profile)
- Account & Security tab content: a card showing the linked login email, "Member since [date]", and the account status pill again, plus a secondary outlined button "Reset Password" with a lock-reset icon
- Show the Overview tab as the active/visible one in the mockup
```

---

## 4. Parents & Guardians table (index)

```
Design an admin dashboard "Parents & Guardians" list page for a school information system, Material Design 3 style, off-white background, rounded white cards, indigo accent, Material Symbols outlined icons, persistent left sidebar + top header already exist off-canvas (design just the main content area).

Layout:
- Breadcrumb "Dashboard / Parents & Guardians"
- Header row: title "Parents & Guardians", gray subtitle, and on the right a primary indigo "+ Add Parent" button with a person-add icon
- A filter bar card: a search input ("Search by name or email"), a dropdown filter "All / Has Children Linked / No Children Linked", and a "Clear all" text link
- A data table card with:
  - Columns: Guardian (circular avatar with initials + full name + email underneath), Phone, Occupation, Children Linked (a small numeric badge/pill), Account Status (a pill: green "Active" or amber "Password Reset Required"), Actions (right-aligned)
  - 6-8 example rows of realistic parent data
  - Row actions as small icon buttons: an eye icon "View" (links to the Parent Profile page), a pencil icon "Edit", a trash icon "Delete"
  - Table header row with a slightly darker gray background and bold uppercase small labels
  - A pagination footer ("Showing 1-8 of 42" with page number buttons)
- An empty-state variant note is not needed — just the populated table
```

---

## 5. Create Class

```
Design an admin dashboard page titled "Create New Class" for a school information system, Material Design 3 style, off-white background, rounded white cards with subtle shadow, indigo accent, Material Symbols outlined icons, persistent left sidebar + top header already exist off-canvas.

Layout:
- Breadcrumb "Dashboard / Classes / Add New"
- Page title "Create New Class", gray subtitle, "Cancel" outlined button top-right
- A two-column layout (roughly 60/40):
  - Left column, card "Class Details": Class Name text input (helper text "e.g. 10A"), Grade Level text input (helper text "e.g. Grade 10"), and a searchable Homeroom Teacher dropdown (shows teacher avatar + name in the list)
  - Right column, card "Subjects Offered": a searchable checkbox grid of subject cards (subject name only, each a small rounded chip-like checkbox card) that can be multi-selected
- Sticky bottom-right action bar: "Cancel" and a primary "Create Class" button with an add/school icon
```

---

## 6. Edit Class

```
Design an admin dashboard page titled "Edit Class" for a school information system, same Material Design 3 style as Create New Class (off-white background, rounded white cards, indigo accent, Material Symbols icons, persistent left sidebar + top header already exist off-canvas).

Layout:
- Breadcrumb "Dashboard / Classes / 10A / Edit"
- Page title "Edit Class — 10A", "Cancel" outlined button top-right
- Two-column layout (60/40), fields pre-filled:
  - Left column, card "Class Details": Class Name, Grade Level, Homeroom Teacher dropdown — pre-filled with example values
  - Left column, below it, a small read-only summary card with two stats side by side: "24 Students Enrolled" and "5 Subjects Assigned", each with an icon
  - Right column, card "Subjects Offered": the same searchable checkbox grid of subjects, with this class's currently-assigned subjects pre-checked/highlighted
- Sticky bottom-right action bar: "Cancel" and a primary "Save Changes" button with a save icon
```

---

## 7. Classes table (index)

```
Design an admin dashboard "Classes" list page for a school information system, Material Design 3 style, off-white background, rounded white cards, indigo accent, Material Symbols outlined icons, persistent left sidebar + top header already exist off-canvas.

Layout:
- Breadcrumb "Dashboard / Classes"
- Header row: title "Classes", gray subtitle, primary indigo "+ Add Class" button top-right with an add icon
- Filter bar card: a search input ("Search by class name"), a "Grade Level" dropdown filter, "Clear all" link
- Data table card with columns: Class Name (bold, e.g. "10A"), Grade Level, Homeroom Teacher (avatar + name, or "Unassigned" in gray italics), Subjects (small stacked chips showing 2-3 subject names plus a "+2 more" chip if there are more), Students Enrolled (numeric badge), Actions (eye/view, pencil/edit, trash/delete icon buttons)
- 6-8 example rows of realistic class data across different grade levels
- Pagination footer at the bottom
```

---

## 8. Create Subject

```
Design a simple admin dashboard page titled "Add New Subject" for a school information system, Material Design 3 style, off-white background, indigo accent, Material Symbols outlined icons, persistent left sidebar + top header already exist off-canvas.

This is a very lightweight form (the subject only has a name), so keep the layout compact and centered rather than a full multi-column form:
- Breadcrumb "Dashboard / Subjects / Add New"
- Page title "Add New Subject", short gray subtitle ("Subjects can be assigned to classes and teachers once created")
- A single centered card (max width ~480px) with one field: "Subject Name" text input with helper text "e.g. Mathematics, English Literature"
- Below the input, a "Cancel" outlined button and a primary "Create Subject" button with an add icon, aligned right within the card
```

---

## 9. Edit Subject

```
Design a simple admin dashboard page titled "Edit Subject" for a school information system, same style as Add New Subject (Material Design 3, off-white background, indigo accent, Material Symbols icons, persistent left sidebar + top header already exist off-canvas).

Layout:
- Breadcrumb "Dashboard / Subjects / Mathematics / Edit"
- Page title "Edit Subject", "Cancel" outlined button top-right
- A single centered card (max width ~480px) with the "Subject Name" field pre-filled ("Mathematics")
- Below the card, a small read-only info row/card showing usage context: "Taught in 6 classes" and "Assigned to 3 teachers" as two small stat chips with icons — purely informational, not editable
- "Cancel" and primary "Save Changes" button, aligned right
```

---

## 10. Subjects table (index)

```
Design an admin dashboard "Subjects" list page for a school information system, Material Design 3 style, off-white background, rounded white cards, indigo accent, Material Symbols outlined icons, persistent left sidebar + top header already exist off-canvas.

Layout:
- Breadcrumb "Dashboard / Subjects"
- Header row: title "Subjects", gray subtitle, primary indigo "+ Add Subject" button top-right with an add icon
- A simple search bar card above the table ("Search subjects by name")
- Data table card with columns: Subject Name (bold), Classes Offering It (numeric badge), Teachers Assigned (numeric badge), Actions (eye/view, pencil/edit, trash/delete icon buttons)
- 8-10 example rows of realistic subject names (Mathematics, English, Physics, Chemistry, Biology, History, Geography, Computer Science, etc.)
- Pagination footer at the bottom
```

---

### Notes for when you bring the Stitch output back

- Parent create/edit already power real student-linking and a generated one-time password (see the "Link Children" and info-banner sections above) — that logic is already implemented in `AdminParentController`, so the UI just needs to expose it.
- The Parent Profile (view) page mirrors the tabbed profile pattern already used for students and teachers elsewhere in the app, so it'll feel consistent once built — same header-card-plus-tabs shape, just with parent-relevant fields and linked children instead of grades/attendance.
- Class create/edit map to `class_name`, `grade_level`, `teacher_id`, and a `subject_ids[]` multi-select (synced via `class->subjects()->sync()`).
- Subject create/edit only have one real field today (`subject_name`) — I kept those two prompts deliberately minimal rather than inventing fields (like a subject code) the backend doesn't store yet. If you want a subject code/description down the line, that's a quick migration + form field addition — just say the word.

# Copilot Instructions for Grail

## Working style

- Plan before implementing changes: inspect the relevant routes, controllers, models, views, and tests first, then outline the approach before editing code.
- Prefer minimal, targeted changes that fit the existing Laravel structure instead of introducing new patterns unless the request clearly requires them.
- When the scope is ambiguous, pause and ask for clarification rather than making assumptions.

## Project-specific expectations

- This project is a Laravel 12 application with Blade views, Tailwind CSS, Alpine.js, Vite, and SQLite by default.
- Follow the existing architecture: routes in routes/web.php, controller logic in app/Http/Controllers, Eloquent models in app/Models, and UI in resources/views.
- Keep role-based behavior consistent for admin, teacher, parent, and student flows.
- Preserve existing naming conventions and relationships used by the current models and migrations.

## Change management

- For significant feature work or structural changes, update PROJECT_DOCUMENTATION.md to reflect the new behavior or architecture.
- If database changes are involved, add or update migrations and keep them consistent with the existing schema style.
- Prefer reusing existing components, helpers, and patterns already used in the repository.

## Verification

- Verify changes with the most relevant command available after editing, such as PHPUnit tests or a targeted Artisan command.
- If frontend assets are affected, verify the build with the appropriate Vite or npm workflow.
- Do not claim a change is complete without checking the relevant output.

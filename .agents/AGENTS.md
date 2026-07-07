# Project-Scoped Rules for Antigravity Coding Assistant

## JavaScript Execution & Asset Loading in Laravel + Vite

When writing scripts in Laravel Blade views (e.g., using `@push('scripts')`) with Vite asset bundling (`@vite`):

1. **Vite Script Deferral**:
   Vite compiled scripts (`app.js`) are imported as ES modules (`<script type="module">`). By definition, these run asynchronously after the DOM has been parsed.
   
2. **Inline Scripts Race Condition**:
   Standard inline `<script>` blocks inside Blade layouts execute synchronously during document parsing, **before** the Vite module scripts run.
   
3. **Reference Errors Prevention**:
   Because of this execution order, global window registers like `window.axios` or Alpine.js variables defined in Vite will be `undefined` during inline script execution.
   
4. **The Safe Load Listener Rule**:
   * **Never** call `axios.get()`, `axios.post()`, or access third-party bundle classes synchronously in the global scope of an inline script tag.
   * **Always** wrap initial data fetches (`fetchSchools()`, `fetchDocuments()`, etc.) and animation observer initializations inside a `window.addEventListener('load', ...)` or `DOMContentLoaded` event listener.
   * This ensures the application entry scripts have finished registering all modules and window keys beforehand.

## Execution Rules & Code Modification Permissions

1. **Auto-Action Consent**:
   The developer has given pre-approval/explicit consent for the assistant to perform file edits, command execution, asset building, and setup verification without asking for confirmation.
   * **Rule:** Do not ask the user for permission or confirmation before making modifications, creating files, or executing terminal commands. Proceed with implementing the solution directly.
   * **Exceptions (MUST confirm):**
     * Deleting any existing files.
     * Moving or renaming existing files or folders.
     * Dangerous operations that delete database data (e.g. `db:wipe` or `migrate:fresh` without seeding).


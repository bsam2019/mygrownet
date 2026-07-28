# GrowFinance UI Architecture v2

**Status:** Design Proposal
**Related:** [GROWFINANCE_ENTERPRISE_ARCHITECTURE.md](GROWFINANCE_ENTERPRISE_ARCHITECTURE.md) v6.0, [FUTURE_VISION.md](FUTURE_VISION.md)
**Changelog from v1:** Resolved density-key contradiction (§7/§11.3 now agree on a platform-wide key). Trimmed Transaction Grid Phase A scope — paste, drag-fill, and duplicate-row moved to Phase B (§4). Added idempotency and tenant-scoping requirements to the API layer (§12.4). Fixed stale section numbering (§9.x → §11.x). Noted the real effort cost of serving two audiences in Phase A (§11.1).

**Goal:** Beat Pastel/Sage on daily-use speed without sacrificing modern, intuitive first-use experience.
**Stack:** Laravel + Vue 3 + Inertia.js + Tailwind + Pinia (reference implementation — the UI architecture is framework-independent provided the implementation supports component-based rendering, reactive state management, and client-initiated server communication for transactional actions).

---

## 1. Design Principles

These govern every decision below. If a future feature request conflicts with one of these, that's a flag to slow down, not a rule to break silently.

1. **Progressive disclosure over memorization.** Every keyboard shortcut or power-user path must have a visible, discoverable, mouse-usable equivalent. Nothing is "keyboard-only." Speed is an accelerant layered on a UI that already makes sense without it.
2. **Two audiences, one codebase.** New users need affordances (tooltips, visible buttons, empty states with guidance). Daily users need zero friction (density, hotkeys, no confirmation dialogs on routine actions). Solve this with a **density/assist mode toggle**, not two separate UIs. Note: serving both audiences well in the same phase is more work than picking one first — see §11.1 for the effort implication.
3. **Zero perceived latency on transactional actions.** Client-side computed totals, optimistic posting, background validation. The server round-trip should never be something the user waits on to keep typing.
4. **Density is a preference, not a default.** Ship "comfortable" as default for new tenants; let users opt into "compact" once fluent. This is a per-user setting, not a global CSS decision baked into components.
5. **Defer heavy infrastructure until proven necessary.** Don't build a persistent multi-tab workspace manager or adopt a heavy grid library speculatively — validate the need with real Taradasi usage first.

---

## 2. Information Architecture

### 2.1 Navigation model
- **Global bar** (top of viewport): logo/workspace switcher, global search/command palette trigger, notifications, user menu. No dropdown mega-menus — command palette replaces deep menu diving.
- **Primary navigation** (typically left column on desktop, collapsible on mobile): flat list of top-level modules (Customers, Suppliers, Journal, Ledger, Reports, Settings) — not nested. Each module can show a lightweight contextual sub-list (e.g., under "Customers": Invoices, Quotations, Statements) but this is a simple expandable list, not a separate 3-column Action Explorer in v1.
- **Workspace** (center area): the actual workspace — list views, entry forms, reports. Full-bleed on transactional screens (no wasted padding once inside a Journal Entry or Invoice form).

### 2.2 Command palette (`Ctrl+K` / `Cmd+K`)
- Universal search across: actions ("new invoice", "post journal"), customer/supplier names, invoice numbers, account codes and names, report names, settings pages, and help articles. Think VS Code Command Palette, not a simple navigation menu.
- Fuzzy natural-language matching — "trial bal" resolves to Trial Balance, "pay rec" resolves to Accounts Receivable.
- Doubles as a discoverability tool — surfacing this prominently (a visible search bar, not just a hidden shortcut) helps new users learn the module structure without memorizing anything.
- Recent + frequent actions shown by default when opened empty.
- Results grouped by category (Actions, Records, Reports, Settings, Help) with keyboard navigation by group.

---

## 3. Key Screens (v1 scope)

| Screen | Priority | Notes |
|---|---|---|
| Journal Entry (line grid) | P0 | Core of the "feels fast" experience — see §4 |
| Invoice / Line Entry | P0 | Shares the same grid component as Journal Entry |
| Customer/Supplier List | P1 | Standard list view, filters, no special grid needed yet |
| Ledger Query / Account History | P1 | Read-heavy, may need grouping later — don't over-build now |
| Trial Balance / Reports | P2 | Defer grid-library decision until this is scoped |
| Settings (incl. density toggle) | P1 | Small but important — houses the comfortable/compact preference |

---

## 4. The Transactional Grid (highest-priority component)

Scope narrowly: this is for **line-item entry** (journal lines, invoice lines) — not a general-purpose data table.

**Component boundary (explicit):** All list views (Customer/Supplier list, Ledger Query results, Report tables) use a separate, simpler `SimpleTable.vue` component — read-only, sortable columns, filter bar, no inline editing. Only `TransactionGrid.vue` supports inline editing, keyboard navigation, and live validation. Do not extend `TransactionGrid.vue` for read-only tables; do not add inline editing to `SimpleTable.vue`. If a screen needs both (e.g., Invoice create/edit with a line grid at top and a customer info panel below), compose both components on the same page — they are siblings, not nested variants.

**Phase A behavior (keep this genuinely small):**
- `Tab` / `Enter` moves focus across/down cells.
- `Enter` on the last cell of a row: validates the row, auto-inserts a new empty row, focus moves to its first cell.
- Numbers auto-format on blur (currency, decimals).
- Running debit/credit totals and balance status computed client-side via Pinia/computed properties — visible and updating live, no server call needed to see if the batch balances.
- `F2`: quick account/customer lookup inline in the active cell — shows balance tooltip for the selected account.
- Inline account balance lookup: hovering or focusing an account cell shows the current account balance (from preloaded data, no server call).
- Inline tax calculation: if a tax rate is set on the invoice/journal header, entering a net amount automatically computes and displays the tax amount in a read-only column.
- `Esc`: cancel current row edit / close any open cell-level popup.

**Explicitly deferred to Phase B (not Phase A):** `Ctrl+D` row duplication, `Delete` on empty row, paste-from-Excel/CSV with column auto-mapping, and drag-fill with sequence detection. These are genuine Excel-grid features — the kind of functionality grid libraries spend years hardening against edge cases (malformed clipboard data, partial pastes over existing rows, drag-fill direction/step detection). Including them in Phase A would turn `TransactionGrid.vue` from "a small, well-scoped, self-contained component" into something closer to a hand-rolled grid library, which contradicts the build-approach decision below. Build the Phase A list first, validate it with Taradasi, then add these once the core entry flow is proven — at that point also revisit whether a grid library is actually a better foundation for them than continuing to hand-roll.

**Build approach:** custom lightweight Vue 3 component (`TransactionGrid.vue`) with cell components as `<input>` elements wired to keydown handlers — not a full grid library. This is a small, well-scoped, self-contained component for the Phase A feature list above, and is the right place to hand-build rather than adopt AG Grid/TanStack.

**Validation UX:**
- **Per-line validation fires on blur** of the last cell in a row (before inserting the next row). Checks: non-empty account, non-zero amount, valid account type for debit/credit.
- **Batch-level validation fires continuously** — the running totals bar shows `DEBITS: X / CREDITS: X / DIFFERENCE: Y` in real time. The difference turns red if non-zero.
- **Post button** is never disabled (Principle 3: zero perceived latency). Clicking Post with an unbalanced batch shows an inline banner at the top of the grid — "Batch does not balance (difference: Z)" — but does not block the action. The server rejects it if still unbalanced on submit, returning per-line errors. The UI never pre-emptively blocks the user.
- **Row-level indicator:** each row shows a small green check or red dot on the left margin to indicate local validation status (valid, invalid, pending). No popups or tooltips on validation — the user sees the status at a glance.

**Error-to-cell mapping on server failure:**
- Every line in the Pinia store has a `clientRowId` (UUID, generated on `created` before any server interaction). The server returns errors keyed to `clientRowId` + field name: `{ errors: [{ clientRowId: "abc-123", field: "amount", message: "Exceeds invoice total" }] }`.
- The store matches `clientRowId` to the corresponding row in the draft state and sets `rowErrors[field]` on that row. The cell component reads `rowErrors` from the store and renders a red border + inline message below the cell.
- `clientRowId` is never sent to the database — it is stripped before the server persists. It exists only for client-server error correlation.

---

## 5. Optimistic UI & State

- **Pinia store per workspace domain** (e.g., `useJournalStore`, `useInvoiceStore`) holding in-progress draft state, computed totals, and validation status — not a single global "workspace" store trying to hold every open screen.
- **File convention:** stores live at `resources/js/stores/growfinance/use{Name}Store.js` (e.g., `resources/js/stores/growfinance/useJournalStore.js`, `resources/js/stores/growfinance/useInvoiceStore.js`). One store file per transactional screen. Shared lookup data (accounts list, customers list) is loaded once by the page and passed to the store as initialisation options, not fetched independently by each store.
- **Optimistic posting pattern (with legal safeguards):**
  1. User presses `F5` / clicks Post.
  2. UI shows a non-blocking inline indicator: "Posting..." with a spinner — the user can continue scrolling or reviewing lines. No modal, no full-page freeze.
  3. Background request fires to the posting engine, including a client-generated idempotency key for the batch (see §12.4 — double-submission protection).
  4. On success: indicator changes to "Posted ✓" with the server-assigned transaction number. The store transitions to the confirmed state.
  5. On failure: indicator changes to "Failed — see errors below". The store rolls back the optimistic state, highlights the offending line with the server error message, and keeps all user input intact — never force a full-page reload or discard what they typed.
  - **Important:** The UI never displays a transaction as "Posted" before the server confirms it. Accounting entries are legally significant — showing a false "Posted" state could lead to incorrect financial decisions. The intermediate state is always "Posting..." or "Pending confirmation."
- No confirmation modals on routine, reversible actions (adding a line, saving a draft). Reserve confirmation dialogs for destructive/irreversible actions (deleting a posted transaction, voiding a batch).

---

## 6. Keyboard Shortcut Spec (v1)

| Key | Action | Discoverable fallback |
|---|---|---|
| `Ctrl/Cmd + K` | Open command palette | Visible search bar in top nav |
| `F2` | Inline lookup (account/customer) in active cell | Dropdown/autocomplete arrow visible in cell |
| `F5` | Post/submit current batch (with `e.preventDefault()` to suppress browser refresh) | Visible "Post" button, always present |
| `Tab` / `Enter` | Grid navigation | Standard tab order works without it |
| `Esc` | Close modal / cancel row edit | Visible "Cancel" / X button |

Keep this list short in v1. Resist the urge to bind every Pastel hotkey immediately — each one adds a discoverability debt (users who don't know it exists) and a maintenance surface. Expand only once the core five are validated with real users.

---

## 6.5 Mobile & Responsive Strategy

v1 is **desktop-first**. The Transactional Grid requires a keyboard and a screen width ≥ 1024px. No mobile variant of the grid is built in Phase A.

**What happens on smaller screens:**
- The primary navigation collapses to a hamburger menu (standard responsive pattern).
- The command palette remains available and is the primary navigation tool on mobile.
- All list views (Customers, Suppliers, Reports) use the same responsive table pattern as existing GrowFinance Inertia pages — horizontal scroll on narrow screens, no hidden columns.
- Transactional screens (Journal Entry, Invoice create/edit) show a **full-screen notice** below 1024px: "Open this page on a desktop computer to use the transaction entry grid." The notice links to the read-only view of the same data.
- Tablet (768-1024px): the grid renders but with a tooltip warning that keyboard navigation is recommended. Touch-based cell editing is not supported in v1.

**Deferred to Phase C** (only if requested by mobile users): a simplified mobile entry form with single-line-at-a-time input, no grid.

---

## 7. Density / Assist Mode

- Stored as a **global MyGrowNet user preference**, not a GrowFinance-specific setting. A user who prefers compact mode in GrowFinance should see the same density in StockFlow, BMS, and GrowBuilder without configuring it again. The reference implementation uses the `app_settings` table with key `platform.ui.density` — scoped to the user, shared across every app on the platform. This is the single source of truth for this setting; no per-module density key exists (see §11.3, which now matches this decision).
- **Comfortable (default):** standard Tailwind spacing, larger touch targets, tooltips visible by default.
- **Compact (opt-in):** tighter padding (`p-1`/`p-2`), smaller text (`text-xs`/`text-sm`), tooltips collapsed to on-hover only.
- Toggle lives in Settings and is a simple CSS class applied at the layout root — not two separate component trees.

---

## 8. Phased Rollout

**Phase A (v1 — build now):**
- Transactional grid for Journal Entry + Invoice line entry, Phase A feature list only (§4)
- Optimistic totals + optimistic posting, with idempotency protection (§5, §12.4)
- Command palette (§2.2)
- Density toggle (§7)

**Phase B (after Taradasi pilot feedback):**
- Contextual sub-navigation under modules (light version of "Action Explorer")
- Expand keyboard shortcut set based on observed usage patterns
- Transaction Grid: row duplication, paste-from-Excel, drag-fill (see §4 deferred list)
- Revisit grid library decision if reporting screens need grouping/pivoting

**Phase C (deferred, only if requested):**
- Persistent multi-tab/workspace manager (keeping multiple screens "alive" at once)
- Full 3-column desktop shell with top menu bar
- Electron/Tauri packaging for a native desktop build

---

## 9. Design System

GrowFinance implements the **MyGrowNet Design System** — a shared visual and interaction language across all platform applications (GrowFinance, StockFlow, BMS, GrowBuilder, GrowBackup). Users should immediately recognize they belong to the same platform.

The design system defines:

- **Typography** — type scale, font families (headings vs body), line heights
- **Color palette** — semantic colors (primary, success, danger, warning, info), neutral palette, support for accessibility contrast
- **Iconography** — single icon set across all apps, consistent metaphor for common actions (post, delete, search, filter)
- **Button hierarchy** — primary, secondary, ghost, danger — consistent placement and labeling
- **Form controls** — inputs, selects, date pickers, currency fields — same interaction model everywhere
- **Tables** — sortable columns, loading skeletons, empty states, pagination
- **Dialogs and modals** — size classes, dismissal rules, focus trapping
- **Notifications** — toast placement, severity colors, auto-dismiss timing
- **Validation states** — field-level error styling, inline messages, banner-level summaries
- **Keyboard interactions** — common shortcuts (`Ctrl+K`, `Enter`, `Esc`), tab order conventions
- **Accessibility** — focus indicators, aria labels, screen reader support for transactional actions
- **Responsive behavior** — breakpoint conventions, navigation collapse patterns

Application-specific components extend rather than replace the shared design system. A GrowFinance-specific component like `TransactionGrid.vue` uses the same buttons, inputs, and validation styles defined by the design system — it adds domain-specific behavior (keyboard navigation, live totals) without reinventing visual fundamentals.

---

## 10. Component Architecture

The UI is organized into three layers, mirroring the domain-driven layering used in the backend:

```
UI Layers (conceptual — not a strict directory hierarchy)

Application Shell
├── Global Bar              — workspace switcher, command palette trigger, user menu
├── Primary Navigation      — module list, contextual sub-navigation
├── Command Palette         — universal search across actions, records, reports, help
├── Notifications           — toast system, in-app alerts
└── User Context            — profile, preferences, logout

Business Components
├── TransactionGrid         — line-item entry with keyboard nav, live totals, validation
├── CustomerSelector        — searchable dropdown with contact history
├── AccountSelector         — searchable dropdown with balance tooltip
├── CurrencyInput           — formatted input with decimal handling
├── TaxSummary              — read-only tax breakdown per line and total
├── BalanceIndicator        — running debit/credit display with balance status
├── JournalHeader           — date, reference, description fields for journal batches
└── InvoiceSummary          — subtotal, tax, total, amount due

Shared Components (from the MyGrowNet Design System)
├── Button                  — primary, secondary, ghost, danger variants
├── Table / SimpleTable     — sortable, filterable data tables (read-only)
├── FormControls            — input, select, date picker, textarea
├── Modal / Dialog          — confirmation dialogs, slide-over panels
├── DatePicker              — single date, date range
├── SearchField             — debounced search with results dropdown
├── LoadingState            — skeleton loaders, spinners, progress indicators
├── EmptyState              — contextual illustration + action button
└── ValidationErrors        — field-level and summary-level error display
```

This layering aligns the UI with the domain-driven backend structure. Business components are specific to GrowFinance's accounting domain. Shared components are framework-level and reusable across all MyGrowNet applications. The Application Shell is configured once per app but follows the same pattern.

---

## 11. Open Decisions Resolved

### 11.1 Target audience — answered

v1 targets **both** new users and Pastel/Sage switchers equally. Onboarding budget (tooltips, empty states, guided first-use flows from §1.2) is allocated in Phase A. The density/assist mode toggle (§7) serves switchers who want compact familiarity. This is a deliberate scope decision, not a free one: building onboarding affordances and full keyboard-speed paths in the same phase is genuinely more Phase A effort than shipping for one audience first and adding the other later — budget and timeline estimates for Phase A should reflect that, not treat it as a costless "both, please."

### 11.2 Reports grouping — answered

All v1 reports (Trial Balance, Profit/Loss, Balance Sheet, Cash Flow, General Ledger) produce **tabular output with no pivot requirement**. Filtering + sorting is sufficient. The grid-library decision is safely deferred to Phase B. Confirm this with Taradasi pilot feedback before revisiting.

### 11.3 Density setting storage — answered

Use the `app_settings` table with key `platform.ui.density`, scoped to the user and shared across every MyGrowNet application (matches §7 — this is the single decision on density storage; there is no separate GrowFinance-only key). Cross-app consistency is achieved by one shared key, not a per-module key with a naming convention — a per-module key would require users to set density separately in each app, which defeats the purpose described in §7.

---

## 12. Inertia.js Integration — Hybrid Pattern

### 12.1 The tension

The UI architecture's optimistic posting and client-side state management (§5) conflict with Inertia.js's page-based rendering model, where every navigation triggers a full server round-trip. A transactional Journal Entry screen cannot rely on Inertia form submissions if the goal is zero-latency posting and per-cell error mapping.

### 12.2 Decision: Hybrid — Inertia for navigation, local state + API for transactional screens

| Concern | Pattern |
|---|---|
| List views, reports, settings | Pure Inertia — full page renders, no Pinia |
| Transactional entry (Journal, Invoice) | Inertia loads initial props → hydrate Pinia store → all subsequent interaction is local + API calls |

### 12.3 How it works

1. The Inertia page renders the grid with initial data passed as props (accounts, customers, any draft lines).
2. The page component hydrates a Pinia store from those props on mount.
3. All `Tab`, `Enter`, live totals, line additions are 100% client-side — no server round-trips.
4. On "Post" (`F5`), the component sends a plain `fetch()` or Axios POST to the posting endpoint (not an Inertia visit). No full-page submission.
5. On success, the store transitions to the "confirmed" state. Optionally, `Inertia.reload({ only: ['stats', 'balances'] })` refreshes sidebar figures without touching the grid.
6. On validation failure, the server returns per-line errors; the store maps them to the offending cells by stable row ID.
7. Navigation away from the screen fires a normal Inertia visit. If there's unsaved draft state, the page component warns before unmounting.

### 12.4 What this means for backend controllers

Transaction screens need a thin API controller for posting/submitting — separate from the existing Inertia controllers that return `RedirectResponse`. These API endpoints reuse the same domain services but return JSON with per-line error detail.

| Existing | New |
|---|---|
| `InvoiceController@store()` returns `RedirectResponse` | `Api\JournalController@post()` returns JSON `{ success, errors: [{line, field, message}] }` |
| `InvoiceController@recordPayment()` returns `RedirectResponse` | No change — payment recording is not a transactional grid |

**Tenant scoping (required, not optional):** The new `Api\*` controller namespace must sit inside the same tenant-resolution and auth middleware group as the existing Inertia controllers. A new namespace is exactly where it's easy to accidentally omit middleware that the rest of the app takes for granted — this needs an explicit test asserting the API routes are unreachable without a resolved tenant context, not just a manual check.

**Idempotency (required, not optional):** Because posting is optimistic and keyboard-driven, a double `F5` press under latency is a realistic double-post risk on financial data, not an edge case to wave away. Each batch generates a client-side idempotency key (UUID) on first render of the grid. The posting endpoint deduplicates on this key: a repeat request with the same key returns the original result rather than creating a second transaction. This key is distinct from `clientRowId` (§4) — the idempotency key identifies the batch/submission, `clientRowId` identifies a line within it.

### 12.5 Rejected alternatives

- **Inertia modal pages / `preserveState`:** Works for simple forms but breaks down when per-cell error mapping is needed, because Inertia treats the entire page response as a single unit. Would require fighting the framework on every validation response.

- **Full SPA with Vue Router:** Too heavy for Phase A. Would need a separate API layer for every screen, client-side routing, auth tokens outside Inertia. The benefit over the hybrid pattern is marginal for two transactional screens.

### 12.6 Migration path from existing code

The existing GrowFinance Inertia pages remain as-is for Phase A. Only two screens are rebuilt using the hybrid pattern:

| Rebuilt (new) | Retains (old) |
|---|---|
| `resources/js/Pages/GrowFinance/Journal/Entry.vue` | Remains until new hybrid version covers all journal workflows |
| `resources/js/Pages/GrowFinance/Invoices/Create.vue` + `Edit.vue` | Remains until new hybrid version covers create + edit |

**What stays unchanged:**
- `resources/js/Pages/GrowFinance/Invoices/Index.vue` — list view, uses `SimpleTable.vue` in Phase B, no change in Phase A.
- `resources/js/Pages/GrowFinance/Reports/*.vue` — all pure Inertia.
- `resources/js/Pages/GrowFinance/Customers/*.vue` — all pure Inertia.
- All existing `app/Http/Controllers/GrowFinance/*.php` — they continue to serve the old pages. New API controllers for the hybrid screens live at `app/Http/Controllers/GrowFinance/Api/`.
- `app/Domain/GrowFinance/Services/*` — domain services are reused as-is by both old Inertia controllers and new API controllers.

**Transition rule:** The old Inertia versions are deleted only when the new hybrid screens cover every workflow the old ones handled (including edge cases like recurring invoices, template selection, and PDF preview). No big-bang rewrite. Feature-flag the new screens behind a query parameter (`?v=2`) for internal testing.

---

## 13. Accessibility

Enterprise procurement increasingly requires WCAG AA compliance. Accessibility is engineered into the component architecture, not bolted on.

**Target level:** WCAG 2.2 AA for all transactional screens. Reports and list views target WCAG 2.2 A initially, with AA as a Phase B goal.

**Keyboard navigation (WCAG 2.1.1):** All functionality operable through a keyboard alone. The Transaction Grid already specifies keyboard-first interaction (§4). This extends to every dialog, dropdown, and date picker — no functionality requires a mouse.

**Focus management (WCAG 2.4.3):** Visible focus indicators on all interactive elements. The grid manages focus programmatically during `Tab`/`Enter` navigation. Modal dialogs trap focus within the dialog; restoring focus on close.

**Color contrast (WCAG 1.4.3):** Minimum 4.5:1 ratio for body text, 3:1 for large text and UI components. The design system color palette enforces this; automated contrast checking in CI prevents regressions.

**Screen reader support (WCAG 4.1.2):**
- `aria-label` on all icon-only buttons and controls.
- `role="grid"` on TransactionGrid with `aria-rowindex`/`aria-colindex` on cells.
- Live region (`aria-live="polite"`) for the posting status indicator and batch validation banner.
- Announcements for state changes (row added, row validated, batch posted).
- Error-to-cell mappings use `aria-describedby` to associate inline error text with the offending input.

**High contrast mode (WCAG 1.4.6, AAA recommended):** The design system palette includes a high-contrast variant. All components tested with forced colors mode (`forced-colors: active`).

**Reduced motion (WCAG 2.3.3):** All animations and transitions respect `prefers-reduced-motion`. The posting spinner is a CSS-only animation (no JavaScript timer); the grid row-insert animation is a brief fade, not a slide.

**Testing requirement:** Each Phase A screen includes a full keyboard-only walkthrough as an acceptance test. Automated axe-core scanning runs in CI for every PR affecting UI components.

---

## 14. Performance Targets

These are engineering targets, not SLAs. They establish a shared understanding of "fast" across the team.

| Metric | Target | Measurement |
|---|---|---|
| Initial page load (transactional screen) | < 2 seconds | Lighthouse on 3G throttling — measured from navigation start to Inertia first paint |
| Command palette open | < 100 ms | First keystroke to results visible — requires preloaded index |
| Grid cell navigation (`Tab`/`Enter`) | < 16 ms (60 fps) | No jank between focus changes — requestAnimationFrame timing |
| Runtime totals update | < 8 ms | Value change in a cell to totals bar update — synchronous computed property |
| Post button → "Posting..." indicator | Immediate (same frame) | Optimistic — no server round-trip before showing indicator |
| Server post response (p95) | < 800 ms | Post endpoint response time for a 20-line journal batch |
| Search results | < 200 ms | Keystroke to filtered results — debounced at 150ms, preloaded index |
| Density toggle apply | < 50 ms | Toggle → CSS class swap → layout recompute — no re-fetch |

**Non-goals (explicitly):** First Input Delay (FID), Cumulative Layout Shift (CLS), and other Core Web Vitals are measured but not primary targets — this is a web application with a logged-in, desktop-first user base where performance is about perceived responsiveness, not search ranking.

**Monitoring:** Pinia store actions are instrumented with `performance.mark()` in development mode. Slow actions (exceeding 2x the target) log a warning to the browser console. Production RUM (Real User Monitoring) is deferred to Phase B.

---

## 15. System-Level Error Handling

The validation UX (§4) and optimistic posting (§5) cover business-logic errors. This section covers infrastructure failures — network drops, expired sessions, server crashes — that the business-logic paths cannot handle because the request never reaches the application code.

### 15.1 Connection loss

The Transaction Grid detects network loss via `navigator.onLine` and a periodic health-check ping.

- **While offline:** The grid remains fully editable. All draft state is held in the Pinia store and persisted to `localStorage` on every line change (debounced 1s). The posting button shows "Offline — saved locally" and is non-functional. A non-blocking banner at the top of the grid reads: "You appear to be offline. Your work is saved locally and will be posted when you reconnect."
- **On reconnect:** `window.addEventListener('online', ...)` triggers an automatic attempt to post any pending draft. On success, the localStorage draft is cleared. On failure, the banner changes to "Connection restored — submission failed. See errors below."
- **Draft recovery:** If the user navigates away while offline, the draft is retained in localStorage. On next visit to the same screen, the page checks for a pending draft and offers to restore it: "You have unsaved work from [timestamp]. Would you like to restore it?"

### 15.2 Session expiry

Because transactional screens use API calls (§12.3) rather than Inertia form posts, a 401 response from the posting endpoint must be handled client-side without losing the draft.

- The Axios/fetch interceptor catches 401 responses.
- If there is unsaved draft state, it is serialized to `sessionStorage` (not localStorage — cleared on tab close).
- The user is redirected to the login page with a `?returnTo=` parameter encoding the current route.
- After re-authentication, the page restores the draft from `sessionStorage` and re-hydrates the Pinia store.
- The user never sees a blank page or loses input.

### 15.3 Server unavailable (5xx)

- The posting endpoint returns a 503. The "Posting..." indicator transitions to "Server unavailable — retrying in 15s." The request is retried automatically with exponential backoff (15s, 30s, 60s, then give up).
- After the final retry fails, the indicator shows "Post failed — server unavailable. Your draft is saved." The user can retry manually.
- The draft is preserved. No data loss.

### 15.4 Permission changed while editing

A 403 response from the posting endpoint means the user's permissions were revoked mid-session.

- The posting fails with "You no longer have permission to post journal entries. Contact your administrator."
- The draft is preserved in read-only mode (the grid switches to `SimpleTable` rendering — all cells become non-editable, but the data is visible).
- On next screen navigation, the draft is discarded.

### 15.5 Optimistic update recovery

If the UI shows "Posting..." and the browser crashes or is closed before the response arrives:

- On next load, the Pinia store hydrates from props (fresh server state) — the optimistic state is not present because it was never confirmed.
- However, if the server *did* process the post before the crash, the idempotency key (§12.4) ensures a retry does not create a duplicate.
- The user checks the journal/ledger to confirm the post. The command palette includes an action "Check last post status" that displays the result of the most recent idempotency key.
- This is an acknowledged limitation — the server is the source of truth for posted state, and the UI never claims otherwise.

---

## 16. Internationalization

MyGrowNet targets multiple African countries. GrowFinance must support localized financial formats from the start, even if only one locale is active in Phase A.

**Phase A (built, single locale active):**
- **Number formatting:** Configurable decimal and thousands separators (e.g., `1,234.56` vs `1 234,56`). Stored as a user preference alongside density.
- **Date formatting:** Locale-aware date display (`DD/MM/YYYY`, `MM/DD/YYYY`, `YYYY-MM-DD`). The date picker component accepts a locale prop; default reads from the user preference. Phase A ships with `en-ZM` (English Zambia) and `en` fallback.
- **Currency display:** Symbol placement (prefix vs suffix), symbol vs ISO code display (ZMW, K). The existing multi-currency infrastructure handles the data layer; the UI adds formatted display via `CurrencyInput` and `CurrencyDisplay` components that respect locale rules.
- **Time zone:** All displayed timestamps convert from UTC to the user's preferred time zone (stored as a user preference, `IANA` time zone ID like `Africa/Lusaka`). Journal entry dates remain date-only (no time zone) — consistent with accounting convention.

**Phase B (multi-locale):**
- Full translation infrastructure (Vue I18n or equivalent). Right-to-left readiness is documented as a constraint but not implemented — interface components avoid hardcoded left/right assumptions (use logical CSS properties: `margin-inline-start` instead of `margin-left`).
- Number formatting switches automatically based on the active locale.
- Date picker locale switching.

**Phase C (deferred, only if requested):**
- RTL layout switching for Arabic, Somali, and other RTL script languages.
- Hijri and Ethiopian calendar support for date entry.
- Multi-currency display (showing a secondary currency alongside the primary).

**Architecture note:** All user-facing strings in business components (`TransactionGrid`, `CustomerSelector`, `AccountSelector`) use string keys (`journal.post.success`, `grid.row.invalid`) rather than hardcoded English text. Shared components (the Design System layer) receive labels as props and do not contain hardcoded strings. This ensures the Phase B translation pass does not require touching component logic.

---

## Appendix A: Version History

| Version | Date | Author | Changes |
|---|---|---|---|
| 1.0 | 2026-07-27 | System | Initial UI architecture document. Sections 1-8, open decisions as questions. |
| 2.0 | 2026-07-27 | System + Review | Resolved density-key contradiction (§7/§11.3). Trimmed Transaction Grid Phase A scope. Added idempotency and tenant-scoping to §12.4. Fixed stale section numbering. Added effort-cost note to §11.1. Added Design System (§9), Component Architecture (§10), Inertia hybrid pattern (§12). Accessibility expanded to own section (§13). Added Performance Targets (§14), System-Level Error Handling (§15), Internationalization (§16). Renamed to "GrowFinance UI Architecture." |

# ResQLink Visual System

A shared warm, high-contrast visual language for ResQLink incident reporting and dispatch coordination. This specification preserves the current content and workflows; it changes presentation, not product behavior.

## Scope and status

- The Laravel app currently has web screens for Admin, Dispatcher, and Volunteer. Stylesheets load in this order from `backend/resources/views/layouts/app.blade.php`:
  1. `resqlink-tokens.css` — every colour, status, severity, chart, spacing, radius, and shadow token (single source of truth).
  2. `resqlink-theme.css` — base elements: typography, buttons, forms, alerts, tables.
  3. `resqlink-badges.css` — account, document, status, and severity badges.
  4. `resqlink-dashboard.css`, `resqlink-widgets.css`, `resqlink-map.css` — shell, data widgets, and map UI.
  5. `resqlink-design-system.css` — cross-component refinements and the responsive layer.
- `resources/js/dispatcher-map.js` reads the `--status-*` tokens at runtime, so map markers always match badges and legends.
- The Flutter volunteer app is planned, not present in this repository. The mobile frames linked below are static design handoff mockups, not implemented or interactive app screens.
- Dispatcher/Admin mockups illustrate the requested screen coverage. The live Blade pages remain authoritative for available data and actions.

## Color tokens

| Token | Value | Use |
| --- | --- | --- |
| Rosewood | `#5A0006` | Sidebar, table headers, headings, primary text, Critical severity |
| Sangria | `#920004` | Active/hover sidebar, pressed actions, strong borders |
| Maximum Red | `#D92200` | Error, destructive action, High severity, required indicator |
| Tangelo | `#F3500A` | Focus ring, small interactive accents, charts, key metrics, marker accent |
| Philippine Orange | `#F37100` | Primary CTA fill, selected controls, Moderate severity |
| Cream | `#FBF3EA` | Warm page canvas |
| White | `#FFFFFF` | Cards and input surfaces |
| Warm border | `#E8DCD0` | Borders, separators |
| Warm control border | `#8A766A` | Input/control outlines; 4.30:1 against white |
| Muted text | `#6B5B55` | Secondary copy |
| Functional success | `#476B45` | Completed/success only; pair with a check and text |
| On-dark muted / subtle | `#D7BFC0` / `#B8999B` | Sidebar secondary text and section labels on Rosewood |

CSS semantic tokens: `--surface`, `--surface-alt`, `--border`, `--text-primary`, `--text-muted`, `--primary` (Sangria), `--primary-hover` (Rosewood), `--accent` (Tangelo), `--accent-light`, `--danger`, `--success`, and `--focus` map to the palette above. Legacy names (`--red`, `--blue`, `--navy`, `--amber`, …) remain aliases to prevent existing views from drifting; use the semantic tokens in new UI.

### Meaning colours

Red and orange carry urgency, so they are reserved for severity, errors, and the brand accent. Status uses its own scale, and categorical charts use distinct hues.

| Token | Value | On white | Use |
| --- | --- | ---: | --- |
| `--status-reported` | `#C81E1E` | 5.74:1 | Reported (new, unhandled) |
| `--status-received` | `#B45309` | 5.02:1 | Received |
| `--status-dispatched` | `#0F766E` | 5.47:1 | Dispatched (teal, separable from red for red–green colour blindness) |
| `--status-completed` | `#4B5563` | 7.56:1 | Completed / archived |
| `--status-all` | `#2563EB` | — | "All" map filter; matches emergency-facility points shown with All |
| `--severity-low` | `#4B5563` | 7.56:1 | Low: outline and text |
| `--severity-moderate-bg` / `-text` | `#FDE7B0` / `#7A4A00` | 6.14:1 | Moderate: pale amber fill |
| `--severity-high` | `#C2410C` | 5.18:1 (white text) | High: burnt-orange fill |
| `--severity-critical` | `#B91C1C` | 6.47:1 (white text) | Critical: red fill plus a double ring |
| `--chart-1` … `--chart-5` | `#0F766E`, `#B45309`, `#4338CA`, `#BE123C`, `#4B5563` | ≥ 5.02:1 | Categorical chart series, in order |

Critical deliberately does not reuse Rosewood: the structural colour of the sidebar and headers must never be the colour of the most urgent incidents.

### Spacing tokens

Use the 4px-based scale for every gap, padding, and margin: `--space-1` 4px, `--space-2` 8px, `--space-3` 12px, `--space-4` 16px, `--space-5` 24px, `--space-6` 32px.

- Page padding: 32px desktop, 24px tablet, 24/16px mobile.
- Page rhythm: every top-level section in `.dashboard-main` is separated by 24px (32px after the page title row), set once in `resqlink-dashboard.css`; do not add per-component outer margins.
- Cards and chart cards: 24px padding (16px on mobile). Grids, stat rows, and detail layouts: 16px gaps. Filter bars: 12px gaps.
- Do not use inline `style="margin…"`; use `.actions.inline`, `.records-pagination`, or another named class.

### Contrast and safe pairings

Ratios are WCAG relative-luminance calculations, rounded to two decimals. Normal text needs at least 4.5:1; large text needs 3:1; controls and meaningful graphical boundaries need 3:1 against adjacent colors.

| Foreground / background | Ratio | Guidance |
| --- | ---: | --- |
| White / Rosewood | 14.61:1 | AA/AAA text; sidebar and table header labels |
| White / Sangria | 9.44:1 | AA/AAA text; active navigation |
| White / Maximum Red | 5.02:1 | AA text; High severity and error actions |
| Rosewood / Philippine Orange | 4.99:1 | AA text; brand logo mark and small orange accents (no longer the primary button) |
| Rosewood / Tangelo | 4.15:1 | Use only for large bold text or non-text marks; not normal-size labels |
| Rosewood / White | 14.61:1 | AA/AAA text |
| Rosewood / Cream | 13.30:1 | AA/AAA text |
| Warm control border / White | 4.30:1 | Distinguishable input and secondary-control boundary |
| Tangelo / White | 3.52:1 | Suitable for large text or control accents, not body links |
| Philippine Orange / White | 2.93:1 | Do not use as a white-text foreground or small control boundary |

Do not put white text on Tangelo or Philippine Orange. Use Rosewood text on Philippine Orange. For ordinary links, use Rosewood/Sangria text with Tangelo underline, icon, or focus ring; Tangelo-only text on white does not meet normal-text AA. Do not place Maximum Red text on Rosewood. Use only warm neutral tints for decorative fills. Keep the muted green limited to functional success.

## Severity and status

Severity uses filled emphasis that escalates, plus a written label and shape: Low = white with gray outline and `○`; Moderate = pale amber fill and `■`; High = burnt-orange fill with white text and `!`; Critical = red fill, white text, double ring, and `◆`. Never convey severity by color alone.

Status uses white outline pills in the `--status-*` colours with written labels and glyphs (`●` Reported, `■` Received, `◆` Dispatched, `✓` Completed), never the filled severity treatment. The map filter indicators, map markers, legend dots, and report status bars use the same tokens: All = blue, Reported = red, Received = amber, Dispatched = teal, Completed = gray. Keep severity and status in separate labeled table columns.

## Typography, spacing, and shape

- Family: Inter with system sans-serif fallbacks. Keep emergency content compact and highly scannable.
- Desktop scale: H1 24–28px/700–800; H2 18px/700; H3 16px/700; body 14–16px; labels 14px/650; captions 12px; table text 13–14px.
- Mobile body: minimum 16px; labels/captions may be 14px. Inputs and touch controls must be at least 48 CSS px high.
- Use the spacing tokens above (4px base: 4, 8, 12, 16, 24, 32px). Cards use 16px radius; fields 8–10px; pills fully rounded. Shadows are soft Rosewood-tinted elevation, not decoration.

## Component guide

- **Application shell:** Rosewood sidebar, grouped uppercase section labels, Sangria hover/active row with Tangelo leading marker, logo and role label. Keep “Logged in as” and Logout anchored at the bottom. Collapse sidebar labels on tablet.
- **Page title:** one H1 and muted explanatory subtitle; primary action aligned to the right on desktop and full-width below title on narrow screens.
- **Buttons:** primary Sangria with white text (9.44:1), hover/pressed Rosewood; secondary white with warm border and Rosewood text; ghost text-only; danger Maximum Red with white text. Orange is not a button fill, so actions never look like Moderate/High severity. Provide hover, visible Tangelo focus, pressed, and disabled states.
- **Inputs:** white surface, accessible warm-gray outline, explicit label, 44px desktop/48px mobile target. Focus ring Tangelo; errors Maximum Red with inline message and icon. Keep read-only map coordinates visibly read-only.
- **Cards/stat cards:** white, warm border, 16px radius, subtle shadow. Stats have concise labels and large figures; use Tangelo for large numbers.
- **Tables:** Rosewood header with white labels, striped cream rows, hover tint, text labels for severity/status, clear Open/View action. On mobile, reflow as cards or use an explicitly scrollable table without shrinking text.
- **Badges:** severity filled by scale; statuses outlined with glyphs. Account active/inactive uses text and distinct shape.
- **Tabs and pagination:** Tangelo underline/outline for selection; keyboard-focusable with visible focus.
- **Map controls:** neutral basemap, white controls, warm borders, light neutral status filters with the `--status-*` indicators (blue All, red Reported, amber Received, teal Dispatched, gray Completed), and one oval search surface with its focus ring on the oval wrapper. Incident markers use the same `--status-*` colour with a clear white outline; local landmark points use a medium yellow-orange distinct from Received, and blue emergency-facility points are visible with All and hidden for individual incident-status filters. Radius circles use transparent fills plus a strong outline.
- **Charts:** categorical series use `--chart-1` … `--chart-5` (teal, amber, indigo, rose, gray) so adjacent bars stay distinguishable; status charts use the `--status-*` tokens. Print the value beside every bar; never depend on color alone.
- **Dialogs/toasts/empty/loading:** title + action for confirmation; error/success icon + message; empty state explains why and preserves the next action; skeletons use warm neutral blocks and reduced-motion-safe animation.

## Flutter Material 3 handoff

The mobile app is not in this Laravel repository. When implemented, define a Flutter `ColorScheme` and `ThemeData` from the same tokens. Suggested starting point:

```dart
const rosewood = Color(0xFF5A0006);
const sangria = Color(0xFF920004);
const maximumRed = Color(0xFFD92200);
const tangelo = Color(0xFFF3500A);
const philippineOrange = Color(0xFFF37100);
const cream = Color(0xFFFBF3EA);
const warmBorder = Color(0xFFE8DCD0);

final resqlinkScheme = ColorScheme.fromSeed(
  seedColor: rosewood,
  brightness: Brightness.light,
  primary: rosewood,
  secondary: philippineOrange,
  onSecondary: rosewood,
  tertiary: tangelo,
  error: maximumRed,
  surface: Colors.white,
  onSurface: rosewood,
  outline: warmBorder,
);

final resqlinkTheme = ThemeData(
  useMaterial3: true,
  colorScheme: resqlinkScheme,
  scaffoldBackgroundColor: cream,
  appBarTheme: const AppBarTheme(backgroundColor: rosewood, foregroundColor: Colors.white),
  filledButtonTheme: FilledButtonThemeData(
    style: FilledButton.styleFrom(backgroundColor: sangria, foregroundColor: Colors.white, minimumSize: const Size(48, 48)),
  ),
);
```

Do not use seeded defaults for severity/status; assign the named `--status-*` and `--severity-*` tokens explicitly and keep labels/icons. The primary CTA is Sangria with white text, matching the web app; keep orange for accents, not action fills.

## Screen coverage and mockups

Static mockups for design review:

- [Volunteer mobile flow](mockups/volunteer-mobile.html): login, home/recent reports, incident form, map picker, review, submission success, GPS-denied and offline states.
- [Dispatcher web screens](mockups/dispatcher-web.html): overview, incident queue, incident detail, map with selected-incident panel.
- [Admin web screens](mockups/admin-web.html): dashboard, volunteer accounts/documents, user management, incident records/archive, reports.
- [Shared states](mockups/shared-states.html): web login, 404, empty/loading, confirmation dialog, alert/toast.

These are static mockups and introduce no new system features. Existing web pages are styled with the shared tokens; Flutter mockups are handoff only.

## Application checklist

- [ ] Use the semantic tokens; remove one-off colors from new UI.
- [ ] Maintain cream/white surfaces, dark Rosewood/Sangria structure, and restrained orange/red accents.
- [ ] Check text, icon, and control contrast; never use color as the only status/severity cue.
- [ ] Use the same sidebar, title, cards, buttons, fields, badges, and table rules across Admin and Dispatcher.
- [ ] Keep all incident fields and existing actions; do not add behavior in a visual-only change.
- [ ] Check responsive layout, 48px mobile controls, keyboard focus, errors, disabled, empty, loading, and success states.
- [ ] Recheck actual ratios when changing token values or overlays.
- [ ] For Flutter, mirror tokens in Material 3 and validate on 360–412px widths.

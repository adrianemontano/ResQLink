# ResQLink Visual System

A shared warm, high-contrast visual language for ResQLink incident reporting and dispatch coordination. This specification preserves the current content and workflows; it changes presentation, not product behavior.

## Scope and status

- The Laravel app currently has web screens for Admin, Dispatcher, and Volunteer. Stylesheets load in this order from `backend/resources/views/layouts/app.blade.php`:
  1. `resqlink-tokens.css` — every colour, status, severity, chart, spacing, radius, and shadow token (single source of truth).
  2. `resqlink-theme.css` — base elements: typography, buttons, forms, alerts, tables.
  3. `resqlink-badges.css` — account, document, status, and severity badges.
  4. `resqlink-dashboard.css`, `resqlink-widgets.css`, `resqlink-map.css` — shell, data widgets, and map UI.
  5. `resqlink-design-system.css` — cross-component refinements and the responsive layer.
  6. `resqlink-refresh.css` — formal presentation layer: Poppins typography, ink text colours, solid buttons, stat cards, tables, badges.
  7. `resqlink-refresh-shell.css` — formal shell: solid Rosewood sidebar, initials chip, page padding, and sign-in screen.
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
| Cream | `#FAF5F0` | Warm page canvas (lightened from `#FBF3EA` in the visual refresh) |
| Sunken cream | `#F6EEE6` | Disabled inputs and recessed surfaces (`--surface-sunken`) |
| White | `#FFFFFF` | Cards and input surfaces |
| Warm border | `#EDE2D8` | Borders, separators (softened from `#E8DCD0`) |
| Warm control border | `#8A766A` | Input/control outlines; 4.30:1 against white |
| Ink | `#2B1D1B` | Body, table, and input text (`--text-primary`); 16.2:1 on white |
| Label | `#493832` | Form labels and table headers (`--text-label`); 11.1:1 on white |
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
| `--severity-low` / `-low-text` | `#FDC500` / `#3A2E00` | 8.3:1 | Low: yellow fill, dark text |
| `--severity-moderate-bg` / `-text` | `#FD8C00` / `#3A1F00` | 6.4:1 | Moderate: orange fill, dark text (white on orange fails contrast) |
| `--severity-high` | `#DC0000` | 5.2:1 (white text) | High: red fill |
| `--severity-critical` | `#780000` | 11.7:1 (white text) | Critical: deep maroon fill, semibold label |
| `--chart-1` … `--chart-5` | `#0F766E`, `#B45309`, `#4338CA`, `#BE123C`, `#4B5563` | ≥ 5.02:1 | Categorical chart series, in order |

Severity follows the team palette yellow `#FDC500` → orange `#FD8C00` → red `#DC0000` → maroon `#780000`. Critical maroon sits close to Rosewood, so severity is always shown as a filled, labelled badge, never as plain coloured text or structure.

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

Severity uses filled emphasis that escalates, plus a written label and shape: Low = yellow fill with dark text; Moderate = orange fill with dark text; High = red fill with white text; Critical = maroon fill with white semibold text. Never convey severity by color alone.

Status uses white outline pills in the `--status-*` colours with written labels and glyphs (`●` Reported, `■` Received, `◆` Dispatched, `✓` Completed), never the filled severity treatment. The map filter indicators, map markers, legend dots, and report status bars use the same tokens: All = blue, Reported = red, Received = amber, Dispatched = teal, Completed = gray. Keep severity and status in separate labeled table columns.

## Typography, spacing, and shape

- Family: Poppins (400/500/600/700, loaded from Google Fonts in `layouts/app.blade.php`) through the `--font-body` token, with system sans-serif fallbacks if the font cannot load. Keep emergency content compact and highly scannable.
- Desktop scale: H1 22px/600 Rosewood; H2 16px/600 Rosewood; H3 15px/600; body and table text 14px in ink (`--text-primary` `#2B1D1B`, 16.2:1); labels 13px/500 in `--text-label` `#493832` (11.1:1); secondary copy in `--text-muted`; table headers and stat labels 11.5–12px/500–600 uppercase; stat figures 28px/600 Rosewood (24px on phones); badges 20px tall. Nothing heavier than 600; input focus borders are 1.5px. Reading text is near-black ink rather than Rosewood: Rosewood marks titles, figures, and links, which keeps long records formal and easy to read.
- Mobile body: minimum 16px; labels/captions may be 14px. Inputs and buttons share `--control-height`, which rises from 40px to 44px on phones.
- Use the spacing tokens above (4px base: 4, 8, 12, 16, 24, 32px). Shapes are deliberately restrained: fields and buttons 6px (`--radius-sm`), cards 10px (`--radius-lg`), sign-in panel 8px (`--radius-md`), badges 4px. Borders are 1px everywhere. Shadows are minimal neutral elevation (`--shadow-sm` on cards), never decoration.

## Formal presentation layer

ResQLink is intended for use by the Cebu City Disaster Risk Reduction and Management Office (CCDRRMO), so the interface follows a formal, government-office register. `resqlink-refresh.css` and `resqlink-refresh-shell.css` load after `resqlink-design-system.css` and hold this layer: Poppins typography, ink-coloured reading text, solid controls, ruled page titles, squared badges, and the plain sign-in screen. Rules for the layer:

- No gradients, glows, hover lifts, or press motion. Surfaces are solid; colour is reserved for meaning (severity, status, errors) and the primary action.
- Orange appears only in the logo mark, the active-navigation marker, and severity; it is never a text or button colour.
- Keyboard focus uses `--focus-ring` (a 2px white gap plus a 2px `--focus` ring) on buttons and links.
- It reads only tokens and changes presentation only.

## Component guide

- **Application shell:** full-height solid Rosewood sidebar (232px, sticky) with a dark hairline edge; solid Philippine Orange logo mark; grouped uppercase section labels; 40px nav rows. The active row uses a translucent white fill and the short Philippine Orange marker. The footer shows an outlined initials chip (`.sidebar-avatar`) beside “Logged in as”, with an outlined 36px Logout anchored at the bottom. Collapse sidebar labels on tablet; the chip stays visible when collapsed.
- **Page title:** one Rosewood H1 and muted explanatory subtitle, separated from the page content by a 1px rule; primary action aligned to the right on desktop and full-width below the title on narrow screens.
- **Buttons:** one height (`--control-height`, 40px) and 14px/500 text; buttons inside table rows use the compact 32px size (`.sm`, automatic in `td`). Primary is solid Sangria with white text (9.44:1) and flat Rosewood on hover; secondary is white with a neutral outline and ink text, turning Rosewood on hover; danger is outlined Maximum Red that fills only on hover, so destructive row actions stay calm. No gradients or shadows. Orange is not a button fill, so actions never look like Moderate/High severity. Provide hover, visible focus, and disabled states.
- **Inputs:** white surface, 1px accessible warm-gray outline, ink text, explicit label 8px above, same height as buttons so filter bars align on one baseline (labels in filter bars carry no margin). Use `.sr-only` for labels that are visually implied. On focus the 1.5px outline darkens to the warm neutral `--focus` brown (#5A4A45) with a soft 3px neutral halo, so focus never reads as an error; only invalid fields use a Maximum Red outline and halo. Checkboxes and radios get a 2px `--focus` outline; errors Maximum Red with inline message. Keep read-only map coordinates visibly read-only.
- **Cards/stat cards:** flat white panels, warm hairline border, 10px radius, minimal shadow. Stat cards (dashboard and reports) share one anatomy: muted 12px uppercase label on top, 28px Rosewood figure below. Dashboard stat cards carry a 3px full-height left rule from their `stat-*` class (`stat-red` Sangria, `stat-amber` Philippine Orange, `stat-blue` Rosewood, `stat-green` success). The figure stays Rosewood, so colour never carries the number's meaning. No hover motion and no coloured top strips.
- **Sign-in screen:** solid Rosewood brand bar (60px), plain cream canvas, and a white panel with an 8px radius, a 3px Rosewood top rule, and a full-width Login button.
- **Tables:** formal register look: light warm header (11.5px uppercase, label colour, ruled bottom), ink cell text, quiet zebra rows, hover tint, text labels for severity/status. Row actions such as Open/View are underlined Sangria links that turn Rosewood on hover. On mobile, reflow as labelled cards; badges and buttons keep their natural width.
- **Badges:** squared 4px labels. Severity filled by scale; statuses outlined with glyphs. Account active/inactive uses text and distinct shape.
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

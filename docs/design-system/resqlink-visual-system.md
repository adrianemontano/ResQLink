# ResQLink Visual System

## Scope and status

The existing Laravel web application uses the shared visual language. The
volunteer mobile frames are static handoff mockups; the Expo React Native app is
planned and no interactive mobile source currently exists.

## Core tokens

| Role | Guidance |
| --- | --- |
| Primary | Use the established ResQLink brand blue for primary actions |
| Surface | Use high-contrast neutral surfaces and readable text |
| Critical | Reserve red for destructive/critical states |
| Warning | Use amber/orange with text or icons, not color alone |
| Success | Use green for confirmed successful outcomes |
| Information | Use blue with an explicit label or icon |

Keep token values synchronized with the existing web styles rather than
inventing a separate mobile palette. Meet accessible contrast and do not encode
severity or workflow status using color alone.

## Severity and workflow status

Severity and incident status are distinct concepts. Severity indicates assessed
impact; status tracks the Notify–Receive–Dispatch workflow. Display both with
clear text labels. Laravel remains authoritative for severity and status.

## Typography, spacing, and shape

<<<<<<< HEAD
- Prefer platform-readable typography and scalable text.
- Use a consistent spacing scale and minimum touch targets.
- Keep cards, inputs, dialogs, and buttons visually consistent.
- Preserve keyboard, focus, loading, disabled, validation, empty, and error
  states in both web and mobile designs.
- Validate mobile layouts at 360–412px widths and with larger text settings.
=======
- Family: Poppins (400/500/600/700, loaded from Google Fonts in `layouts/app.blade.php`) through the `--font-body` token, with system sans-serif fallbacks if the font cannot load. Keep emergency content compact and highly scannable.
- Desktop scale: H1 22px/600 Rosewood; H2 16px/600 Rosewood; H3 15px/600; body and table text 14px in ink (`--text-primary` `#2B1D1B`, 16.2:1); labels 13px/500 in `--text-label` `#493832` (11.1:1); secondary copy in `--text-muted`; table headers and stat labels 11.5–12px/500–600 uppercase; stat figures 28px/600 Rosewood (24px on phones); badges 20px tall. Nothing heavier than 600; input focus borders are 1.5px. Reading text is near-black ink rather than Rosewood: Rosewood marks titles, figures, and links, which keeps long records formal and easy to read.
- Mobile body: minimum 16px; labels/captions may be 14px. Inputs and buttons share `--control-height`, which rises from 40px to 44px on phones.
- Responsive QA: the shared `resqlink-responsive.css` layer is loaded last so shell, filters, map controls, notifications, report cards, and forms adapt at 1180px, 760px, 480px, and 360px. On phones the sidebar becomes a compact two-row top navigation with horizontal scrolling; on narrow tables the existing accessible table helper shows labelled cards, and explicitly dense report tables scroll within their container. Landscape-height and reduced-motion states are included.
- Use the spacing tokens above (4px base: 4, 8, 12, 16, 24, 32px). Shapes are deliberately restrained: fields and buttons 6px (`--radius-sm`), cards 10px (`--radius-lg`), sign-in panel 8px (`--radius-md`), badges 4px. Borders are 1px everywhere. Shadows are minimal neutral elevation (`--shadow-sm` on cards), never decoration.
>>>>>>> origin/jassy

## Expo React Native handoff

When the Expo application is implemented, define small typed TypeScript theme
modules and reusable React Native components for colors, typography, spacing,
shape, severity badges, status badges, form controls, and feedback states.
Expo Router route files should compose those components rather than duplicating
visual constants.

Native MapLibre screens must provide readable attribution, selected-coordinate
feedback, marker state, geographic impact-radius styling, tile failure state,
and a manual-location path when GPS permission is denied. Expo Go is not a valid
visual-verification runtime for the map; use the installed development build.

## Screen coverage and mockups

- `docs/design-system/mockups/admin-web.html`
- `docs/design-system/mockups/dispatcher-web.html`
- `docs/design-system/mockups/volunteer-mobile.html`
- `docs/design-system/mockups/shared-states.html`
- `docs/design-system/mockups/resqlink-mockups.css`

<<<<<<< HEAD
These static artifacts introduce no new functional requirements. The volunteer
mockup remains a design reference for the future Expo implementation.
=======
- [Volunteer mobile flow](mockups/volunteer-mobile.html): login, home/recent reports, incident form, map picker, review, submission success, GPS-denied and offline states.
- [Dispatcher web screens](mockups/dispatcher-web.html): overview, incident queue, incident detail, map with selected-incident panel.
- [Dispatcher enhancements](mockups/dispatcher-enhancements.html): reporter/timestamp queue/detail, Received action, notifications, forced-password, self-account, handling reports, and photo states.
- [Admin web screens](mockups/admin-web.html): dashboard, volunteer accounts/documents, user management, incident records/archive, reports.
- [Shared states](mockups/shared-states.html): web login, 404, empty/loading, confirmation dialog, alert/toast.

These are static mockups and introduce no new system features. Existing web pages are styled with the shared tokens; Flutter mockups are handoff only.
>>>>>>> origin/jassy

## Dashboard analytics

Admin and volunteer dashboards use responsive summary cards, compact incident charts, and recent-activity panels while retaining the shared Poppins type and Rosewood, Sangria, cream, and chart-token palette. Admin analytics summarize persisted incident and account data, including the six-month incident trend, category mix, status, severity, and recent records. Volunteer analytics remain scoped to the signed-in volunteer's reports and show their six-month trend, category mix, and recent reports. Empty datasets use explicit empty states rather than sample values. Chart bars and trend columns are scaled to the 100% total of their dataset, so a value is colored only in proportion to its measurement; no minimum fill is applied.

## Application checklist

<<<<<<< HEAD
- [ ] Shared colors and semantic roles match the current web system.
- [ ] Text/icon labels accompany severity and status colors.
- [ ] Form controls cover focus, error, disabled, loading, and success states.
- [ ] Volunteer layouts work at supported Android widths and larger text sizes.
- [ ] Map states cover GPS denial, manual selection, loading, and unavailable
  TileServer data.
- [ ] Mobile implementation uses typed reusable components, not copied constants.
=======
- [ ] Use the semantic tokens; remove one-off colors from new UI.
- [ ] Maintain cream/white surfaces, dark Rosewood/Sangria structure, and restrained orange/red accents.
- [ ] Check text, icon, and control contrast; never use color as the only status/severity cue.
- [ ] Use the same sidebar, title, cards, buttons, fields, badges, and table rules across Admin and Dispatcher.
- [ ] Keep all incident fields and existing actions; do not add behavior in a visual-only change.
- [ ] Check at 320px, 360px, 390px, 412px, 768px, 1024px, and desktop widths; include short landscape, 48px mobile controls, keyboard focus, errors, disabled, empty, loading, and success states.
- [ ] Recheck actual ratios when changing token values or overlays.
- [ ] For Flutter, mirror tokens in Material 3 and validate on 360–412px widths.
>>>>>>> origin/jassy

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

On web dashboard pages, status and severity badges are soft pills whose
background, border, and text are computed from the `--status-*` and
`--severity-*` tokens (`resqlink-badges.css`), so a badge always matches the
map marker, legend, and report bar for the same value. Completed is grey
(`--status-completed`) everywhere.

## Typography, spacing, and shape

- Prefer platform-readable typography and scalable text.
- Use a consistent spacing scale and minimum touch targets.
- Keep cards, inputs, dialogs, and buttons visually consistent.
- Preserve keyboard, focus, loading, disabled, validation, empty, and error
  states in both web and mobile designs.
- Validate mobile layouts at 360–412px widths and with larger text settings.

### Web implementation rules (2026-11-27 consistency pass)

- **One font.** Every stylesheet uses `var(--font-body)` (Poppins). Stylesheets
  that can load without `resqlink-tokens.css` (the login page and toasts) use
  `var(--font-body, 'Poppins', sans-serif)`.
- **Colours come from tokens.** Hard-coded hex values that equal a brand,
  text, border, success, or status token were replaced with the token. New CSS
  should use tokens rather than hex values.
- **No one-off inline styles.** Static `style="…"` attributes in Blade views
  were replaced with classes in `public/css/resqlink-utilities.css`
  (`.text-muted`, `.card-title`, `.stack-gap`, `.mt-section`, `.empty-panel`,
  `.password-field`, error-page and photo-lightbox classes). Inline styles
  remain only for data-driven values such as bar widths and chart colours, and
  for elements a script shows or hides.
- **Shared components.** Error pages use `<x-error-page>`; incident photos use
  `<x-incident-photo-card>` (Dispatcher and Admin) with a native `<dialog>`
  lightbox; dispatched teams use `<x-incident-dispatches>`.
- `public/css/resqlink-login.css` is not loaded by any page; the login page
  uses `login.css`. Confirm with its owner before removing it.

## Brand Identity and Sidebar Assets

- **Official Brand Logo (public/images/resqlink-logo.png)**: Full horizontal brand logo with Rosewood pin and Philippine Orange text, displayed in a high-contrast white card on the Rosewood sidebar when open across all user roles (Admin, Dispatcher, Volunteer).
- **Brand Pin Mark (public/images/resqlink-mark.png)**: Standalone pin icon and radar rings, centered in a 42x42px white badge when the sidebar is collapsed (72px).
- **Login and Auth Screens**: Standardized to use the official brand logo.

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

These static artifacts introduce no new functional requirements. The volunteer
mockup remains a design reference for the future Expo implementation.

## Application checklist

- [x] Shared colors and semantic roles match the current web system (web
  consistency pass, 2026-11-27).
- [ ] Text/icon labels accompany severity and status colors.
- [ ] Form controls cover focus, error, disabled, loading, and success states.
- [ ] Volunteer layouts work at supported Android widths and larger text sizes.
- [ ] Map states cover GPS denial, manual selection, loading, and unavailable
  TileServer data.
- [ ] Mobile implementation uses typed reusable components, not copied constants.

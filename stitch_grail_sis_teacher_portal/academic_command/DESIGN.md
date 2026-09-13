---
name: Academic Command
colors:
  surface: '#f8f9fa'
  surface-dim: '#d8dadb'
  surface-bright: '#f8f9fa'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f5'
  surface-container: '#eceeef'
  surface-container-high: '#e7e8e9'
  surface-container-highest: '#e1e3e4'
  on-surface: '#191c1d'
  on-surface-variant: '#43474e'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#eff1f2'
  outline: '#74777e'
  outline-variant: '#c4c6ce'
  surface-tint: '#476081'
  primary: '#000511'
  on-primary: '#ffffff'
  primary-container: '#001f3d'
  on-primary-container: '#6f88ab'
  inverse-primary: '#afc8ee'
  secondary: '#085bbd'
  on-secondary: '#ffffff'
  secondary-container: '#619aff'
  on-secondary-container: '#00316d'
  tertiary: '#000513'
  on-tertiary: '#ffffff'
  tertiary-container: '#001d45'
  on-tertiary-container: '#4884e8'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d3e4ff'
  primary-fixed-dim: '#afc8ee'
  on-primary-fixed: '#001c38'
  on-primary-fixed-variant: '#2f4868'
  secondary-fixed: '#d8e2ff'
  secondary-fixed-dim: '#acc7ff'
  on-secondary-fixed: '#001a40'
  on-secondary-fixed-variant: '#004492'
  tertiary-fixed: '#d7e2ff'
  tertiary-fixed-dim: '#acc7ff'
  on-tertiary-fixed: '#001a40'
  on-tertiary-fixed-variant: '#004492'
  background: '#f8f9fa'
  on-background: '#191c1d'
  surface-variant: '#e1e3e4'
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 26px
    fontWeight: '700'
    lineHeight: 34px
    letterSpacing: -0.015em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  title-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
  title-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
  label-md:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '500'
    lineHeight: 18px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.03em
  data-lg:
    fontFamily: JetBrains Mono
    fontSize: 18px
    fontWeight: '500'
    lineHeight: 24px
  data-md:
    fontFamily: JetBrains Mono
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  data-sm:
    fontFamily: JetBrains Mono
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1.5rem
  gutter-mobile: 0.75rem
  margin: 2rem
  margin-mobile: 1rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style

The design system delivers an authoritative, focused, and ergonomically efficient environment tailored for educators managing complex daily workflows: grading, attendance, parent communication, and curriculum tracking. 

Drawing from **Corporate / Modern** principles with high-density utility, the interface prioritizes immediate legibility, low cognitive fatigue, and absolute trust. Visual calm is achieved through structured spatial grouping, high contrast ratios for critical data points, and distinct surface layering rather than decorative ornamentation. The design balances enterprise reliability with human approachable touches, giving teachers instantaneous command over their classroom data.

## Colors

The color palette is built around deep administrative navies and focused cobalt blues, structured to support high-density workflows while minimizing visual fatigue during extended grading sessions.

- **Primary (`#001f3d`) & Primary Container (`#003461`)**: Anchor structural navigation (sidebar, top application header, primary badges).
- **Secondary / CTA (`#075bbd`) & Secondary Container (`#609aff`)**: Command attention for forward actions (saving grades, taking attendance, submitting reports).
- **Surfaces**:
  - Main Background: `#f8f9fa`
  - Canvas / Card Surface: `#ffffff`
  - Surface Container Low: `#f2f4f5` (used for subtle section grouping and table header rows)
  - Surface Container High: `#e7e8e9` (used for hover states and modal backdrops)
- **Typography & Boundaries**:
  - Text on Surface: `#191c1d` (primary body and metric readouts)
  - Text on Surface Variant: `#43474f` (metadata, secondary labels, disabled copy)
  - Outline: `#737780` (active inputs, structural rules)
  - Outline Variant: `#c3c6d0` (subtle divider borders, card strokes)
- **Status & Alerts**:
  - Error: `#ba1a1a` (absent students, missing assignments, systemic errors)
  - Success: `#166534` (submitted grades, present status, saved sync)
  - Warning: `#92400e` (tardy markers, pending IEP reviews, unsaved changes)

## Typography

The typographic hierarchy prioritizes structural clarity and rapid data scanning.

- **Primary Interface (Inter)**: Handles all navigation, body text, form elements, and hierarchy labels. Its neutral geometry remains legible across dense dashboards.
- **Data & Numerical Metrics (JetBrains Mono)**: Applied to student IDs, GPA readouts, grading sheets, attendance codes, and timestamp logs to ensure tabular number alignment and immediate differentiation between prose and quantitative values.
- **Iconography**: Rendered exclusively via Material Symbols Outlined (20px standard, 24px primary navigation) set with a 1.5px stroke weight to match the letterforms of Inter.

## Layout & Spacing

The portal adopts a persistent, productivity-focused layout:
- **Left Navigation Rail / Sidebar**: Fixed width (260px desktop, collapsed to 72px icon rail on small desktops/tablets, drawer on mobile). Filled with `#001f3d`.
- **Top Utility Header**: Fixed height (64px) using `#ffffff` with a bottom border of `1px solid #c3c6d0`. Holds breadcrumbs, active term selectors, class switchers, and teacher profiles.
- **Main Canvas**: Fluid responsive workspace wrapped in `#f8f9fa` with `margin` gutters of `2rem` (desktop) and `1rem` (mobile).

Responsive breakpoints:
- **Desktop (1200px+)**: 12-column grid, persistent 260px sidebar, full data tables.
- **Tablet (768px - 1199px)**: 8-column grid, 72px condensed sidebar, horizontally scrollable data grids.
- **Mobile (<768px)**: 4-column layout, bottom navigation or modal drawer, responsive card conversion for grade rosters.

## Elevation & Depth

Depth is established via a hybrid model of soft ambient shadows and low-contrast borders:

- **Level 0 (Flat Canvas)**: `#f8f9fa` base background with `#ffffff` nested panels bound by `1px solid #c3c6d0`.
- **Level 1 (Card & Content Blocks)**: Surface `#ffffff` elevated by `box-shadow: 0 1px 3px rgba(0, 31, 61, 0.05), 0 1px 2px rgba(0, 31, 61, 0.08)` and bounded by a `1px solid #c3c6d0` stroke.
- **Level 2 (Hover & Interactive Containers)**: Raised state for actionable grade cards and student drawers: `box-shadow: 0 4px 6px -1px rgba(0, 31, 61, 0.08), 0 2px 4px -2px rgba(0, 31, 61, 0.06)`.
- **Level 3 (Modals, Overlays, Menus)**: Surface `#ffffff` elevated by `box-shadow: 0 10px 15px -3px rgba(0, 31, 61, 0.12), 0 4px 6px -4px rgba(0, 31, 61, 0.08)`.

## Shapes

The interface balances friendly modern accessibility with authoritative enterprise structure:
- **Cards & Primary Modules**: Styled using `rounded-xl` (`1.5rem` / 24px) for distinct visual grouping and soft perimeter demarcation against the `#f8f9fa` backdrop.
- **Buttons, Form Inputs, & Dropdowns**: Set to default rounded (`0.5rem` / 8px) to retain sharp interaction zones.
- **Badges, Tags, & Status Pills**: Set to full pill curvature (`9999px`) for classification tags.

## Components

### Buttons
- **Primary / Action**: Solid `#075bbd` background with `#ffffff` label, `8px` radius, `0.5rem 1rem` padding. Hover: `#003461`.
- **Secondary**: Ghost surface with `1px solid #c3c6d0` border, `#001f3d` label. Hover: `#f2f4f5`.
- **Destructive**: Outline or fill using `#ba1a1a`.

### Cards
- Constructed with `#ffffff` fill, `rounded-xl` corners (24px), `1px solid #c3c6d0`, and Level 1 ambient shadow.
- Header zones within cards use a bottom divider of `1px solid #f2f4f5` with `1rem` vertical padding.

### Data Tables & Gradebooks
- **Header Cells**: Surface container low `#f2f4f5`, text styled with `label-sm` in uppercase `#43474f`.
- **Row Cells**: Border bottom `1px solid #f2f4f5`, text styled with `body-md` (Inter) or `data-md` (JetBrains Mono).
- **Inline Editable Cells**: Transition to `1px solid #075bbd` with `#ffffff` fill on focus.

### Input Fields & Selects
- Height `40px`, background `#ffffff`, border `1px solid #737780`, `rounded` (8px). 
- Active focus state: `2px solid #075bbd` ring with no offset.

### Chips & Badges
- **Present / Passed**: Background `#dcfce7`, text `#166534` (`label-sm`).
- **Tardy / Warning**: Background `#fef3c7`, text `#92400e` (`label-sm`).
- **Absent / Critical**: Background `#fee2e2`, text `#ba1a1a` (`label-sm`).

### Checkboxes & Radios
- `18px x 18px`, `4px` radius for checkboxes, full circle for radios.
- Unselected: `1.5px solid #737780`. Selected: `#075bbd` fill with `#ffffff` checkmark.
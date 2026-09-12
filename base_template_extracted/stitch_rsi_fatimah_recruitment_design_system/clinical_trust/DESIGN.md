---
name: Clinical Trust
colors:
  surface: '#f6faff'
  surface-dim: '#d2dbe4'
  surface-bright: '#f6faff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#ecf5fe'
  surface-container: '#e6eff8'
  surface-container-high: '#e0e9f2'
  surface-container-highest: '#dbe4ed'
  on-surface: '#141d23'
  on-surface-variant: '#404850'
  inverse-surface: '#293138'
  inverse-on-surface: '#e9f2fb'
  outline: '#707881'
  outline-variant: '#bfc7d1'
  surface-tint: '#006496'
  primary: '#005f90'
  on-primary: '#ffffff'
  primary-container: '#0179b5'
  on-primary-container: '#f7faff'
  inverse-primary: '#91cdff'
  secondary: '#066495'
  on-secondary: '#ffffff'
  secondary-container: '#84c8fe'
  on-secondary-container: '#00537e'
  tertiary: '#515c61'
  on-tertiary: '#ffffff'
  tertiary-container: '#69757a'
  on-tertiary-container: '#f4fbff'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#cce5ff'
  primary-fixed-dim: '#91cdff'
  on-primary-fixed: '#001e31'
  on-primary-fixed-variant: '#004b72'
  secondary-fixed: '#cbe6ff'
  secondary-fixed-dim: '#90cdff'
  on-secondary-fixed: '#001e31'
  on-secondary-fixed-variant: '#004b72'
  tertiary-fixed: '#d8e4ea'
  tertiary-fixed-dim: '#bcc8ce'
  on-tertiary-fixed: '#121d21'
  on-tertiary-fixed-variant: '#3d484d'
  background: '#f6faff'
  on-background: '#141d23'
  surface-variant: '#dbe4ed'
typography:
  headline-xl:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
  headline-xl-mobile:
    fontFamily: Inter
    fontSize: 26px
    fontWeight: '700'
    lineHeight: 34px
  headline-lg:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-md:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  title-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
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
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '600'
    lineHeight: 14px
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  space-2xs: 0.25rem
  space-xs: 0.5rem
  space-sm: 0.75rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
  space-2xl: 3rem
  container-padding-mobile: 1rem
  container-padding-desktop: 2rem
  gutter-default: 1.5rem
---

## Brand & Style
The design system embodies a modern, institution-grade healthcare employment platform. It balances the strict authority and clinical precision of a premier hospital institution with the approachable, transparent experience expected by modern medical professionals and staff candidates.

The aesthetic philosophy centers on **Corporate / Modern** enterprise discipline, aligned natively with Bootstrap 5 conventions while maintaining refined visual polish. Surfaces remain clear, uncluttered, and functional to reduce cognitive fatigue during multi-step application processes, credential validation, and HR administrative reviews.

Target users include medical specialists, nurses, clinical support personnel, and hospital HR committees. The UI evokes clinical competence, reliability, absolute security, and organizational warmth through crisp typography, soft structural dividers, and purposeful blue-tinted visual anchors.

## Colors
The palette is engineered around healthcare stability and clinical clarity, maintaining WCAG AA contrast compliance across all text and interactive states.

- **Primary (`#0179B5`)**: The core brand anchor, signaling trust, medical clarity, and institutional longevity.
- **Primary States**: `#016293` serves as the high-contrast hover state, while `#014C72` is reserved for active presses and focused states.
- **Surface & Backgrounds**: Base layout canvas utilizes `#F5F7F9` (cool hospital stone) to eliminate glare, paired with `#FFFFFF` for elevated data cards and modal surfaces. `#E6F2F8` acts as an interactive tint for selections, pill badges, and active navigation links.
- **Typography & Neutral**: High-legibility text relies on `#212529` for primary content and data tables. `#6C757D` provides subdued contrast for secondary metadata, helper descriptions, and inactive states. Structural outlines utilize `#DEE2E6`.
- **System Diagnostics**: Built on Bootstrap 5 standard semantics: Success (`#198754`), Danger (`#DC3545`), Warning (`#FFC107`), and Info (`#0DCAF0`), consistently paired with 10% tinted backgrounds for dismissible status alerts.

## Typography
The typographic hierarchy relies solely on **Inter** to ensure maximum legibility across complex medical records, verification checklists, and candidate data tables. The hierarchy focuses on compact structural density rather than decorative scale.

- **Headlines (`headline-xl` down to `headline-md`)**: Used for page banners, recruitment stage indicators, and card headers. Letter-spacing is tightened slightly (`-0.015em`) for weights of 600 and above.
- **Body (`body-lg`, `body-md`, `body-sm`)**: Tuned for reading application forms, contract agreements, and candidate CV abstracts. `body-md` (14px) serves as the default operational size across table views and descriptive paragraphs.
- **Labels & Microcopy (`label-md`, `label-sm`)**: Enforces clarity in form labels, field indicators, status tags, and tabular headers. `label-sm` utilizes an uppercase transform with `+0.03em` tracking when used for column sorting labels and step trackers.

## Layout & Spacing
The layout architecture adheres to a 12-column responsive fluid grid built upon an 8px base rhythm (with 4px sub-increments for micro-alignments). 

- **Breakpoints**: Bootstrap 5 responsive targets: Mobile (`xs` to `<768px`), Tablet (`md` 768px - 991px), and Desktop (`lg` ≥992px, `xl` ≥1200px, `xxl` ≥1400px).
- **Application Views**: Two-column layout pattern on desktop: 260px fixed collapsible sidebar for HR navigation alongside a fluid content workspace capped at a max-width of 1440px. Candidate self-service portals utilize a focused single-column layout with a 960px container cap.
- **Reflow Logic**: On mobile viewports, complex administrative data tables collapse into stacked cards showing key status fields. Dual-column form groupings stack to a single column beneath the `md` threshold.
- **Vertical Spacing**: Form field gaps maintain a strict `space-md` (16px), section containers maintain `space-xl` (32px), and major page module headers use `space-lg` (24px).

## Elevation & Depth
Depth in the system is restrained and clinical, prioritizing clean planar boundaries over dramatic elevation. Visual hierarchy relies predominantly on tonal separation between `#F5F7F9` (canvas) and `#FFFFFF` (surfaces), framed by `#DEE2E6` borders.

- **Flat Level (Ground)**: Applied to the base canvas (`#F5F7F9`).
- **Level 1 (Cards & Data Sections)**: `0 2px 8px rgba(0, 0, 0, 0.05)` coupled with a continuous `1px solid #DEE2E6` stroke. This ambient shadow removes harsh line cutoffs while keeping content neatly partitioned.
- **Level 2 (Hover & Focus States)**: `0 4px 12px rgba(1, 121, 181, 0.08)`, introducing a subtle brand tint to signify interactivity on clickable candidate rows and button interactions.
- **Level 3 (Overlays & Dropdowns)**: `0 8px 24px rgba(0, 0, 0, 0.12)`, designated strictly for datepickers, filter menus, and candidate assessment modal dialogues.

## Shapes
The shape language conveys stability, institutional trust, and modern enterprise efficiency through disciplined, moderate geometric radiuses.

- **Inputs, Buttons, and Selects**: Defined at `6px` to `8px` (`0.375rem` to `0.5rem`). This prevents UI components from appearing overly toy-like while avoiding severe, archaic sharp edges.
- **Containers, Cards, and Modals**: Styled between `10px` and `12px` (`0.625rem` to `0.75rem`) to create soft windowing for complex clinical documents, interview logs, and candidate scoring boards.
- **Status Badges & Chips**: Fully pill-shaped (`50rem` / `rounded-pill`) exclusively to distinguish categorical tags, active status indicators (e.g., "Terverifikasi", "Lolos Berkas"), and count indicators from interactive rectangular controls.

## Components

### Buttons
- **Primary**: Background `#0179B5`, text `#FFFFFF`, border-radius 6px. Hover shifts to `#016293`; active/focus triggers `#014C72` with an outer focus-ring `0 0 0 0.25rem rgba(1, 121, 181, 0.25)`.
- **Secondary / Ghost**: Outline `1px solid #DEE2E6`, text `#212529`, background `#FFFFFF`. Hover uses `#F5F7F9` background and `#0179B5` text color.
- **Danger / Action**: Outline and filled variants utilizing `#DC3545` strictly for permanent destructive acts (e.g., "Tolak Berkas", "Hapus Lamaran").

### Form Fields & Inputs
- **Inputs & Selects**: Height 40px, border `1px solid #DEE2E6`, border-radius 6px, background `#FFFFFF`, font size 14px.
- **Focus State**: Border color turns `#0179B5` accompanied by a soft glow ring: `box-shadow: 0 0 0 3px rgba(1, 121, 181, 0.15)`.
- **Labels**: Size 14px, weight 500, color `#212529`. Mandatory fields feature an unspaced red asterisk (`#DC3545`).

### Cards & Data Panels
- Background `#FFFFFF`, border `1px solid #DEE2E6`, border-radius 10px, elevation shadow `0 2px 8px rgba(0, 0, 0, 0.05)`. Card headers feature an explicit bottom border divider `1px solid #DEE2E6` with a padding of 16px 20px.

### Badges & Status Chips
- Badges strictly leverage the `rounded-pill` class.
- **Recruitment Verified / Pass**: Background `#D1E7DD`, text `#0F5132`.
- **Under Review / In Progress**: Background `#E6F2F8`, text `#016293`.
- **Pending Action**: Background `#FFF3CD`, text `#664D03`.
- **Rejected**: Background `#F8D7DA`, text `#842029`.

### Checkboxes & Radio Buttons
- Custom Bootstrap controls sized at 18px by 18px. Active checked state background is `#0179B5` with white check/dot iconography. Checked focus ring mirrors primary focus tokens.

### Data Tables
- Clean tabular view with subtle zebra striping optional. Table header background is `#F5F7F9` with 11px uppercase bold labels (`#6C757D`). Row hover activates a soft `#E6F2F8` tint.

### Iconography
- Standardized on **Bootstrap Icons (Outline style)**. Sized at 16px for inline text elements, 18px within buttons/inputs, and 24px for navigational section icons.
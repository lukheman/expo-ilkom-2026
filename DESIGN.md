# Design System Inspired by GoFood

> Auto-extracted from `https://gofood.co.id/en` on 2026-10-07

## 1. Visual Theme & Atmosphere

Friendly, approachable design with rounded shapes and generous whitespace.

The hero section leads with "Looking for inspo? Start here".

**Key Characteristics:**
- ui-sans-serif as the heading font
- ui-sans-serif as the body font for all running text
- Light/white background (#ffffff) as the primary canvas
- Primary accent `#ffc41c` used for CTAs and brand highlights
- 3 shadow level(s) detected — tinted shadows
- Rounded corners (16px+) creating a friendly, approachable feel
- Tags: light, rounded, colorful, sans-serif

## 2. Color Palette & Roles

### Primary
- **Primary Accent** (`#ffc41c`) · `--color-primary`: Brand color, CTA backgrounds, link text, interactive highlights.
- **Secondary Accent** (`#e52535`) · `--color-secondary`: Secondary brand, hover states, complementary highlights.
- **Background** (`#ffffff`) · `--color-bg`: Page background, primary canvas.
- **Background Secondary** (`#ebf3ff`) · `--color-bg-secondary`: Cards, surfaces, alternating sections.

### Text
- **Text Primary** (`#000000`) · `--color-text`: Headings and body text.
- **Text Secondary** (`#666666`) · `--color-text-secondary`: Muted text, captions, placeholders.

### Borders & Surfaces
- **Border** (`#fafafa`) · `--color-border`: Dividers, outlines, input borders.

### Full Extracted Palette

| # | Hex | CSS Variable | Role | Area | Contrast |
|---|---|---|---|---|---|
| 1 | `#ffffff` | `--palette-1` | button | large | text-dark |
| 2 | `#ebf3ff` | `--palette-2` | block | medium | text-dark |
| 3 | `#eaffea` | `--palette-3` | button | medium | text-dark |
| 4 | `#e52535` | `--palette-4` | text-accent | small | text-light |
| 5 | `#00880d` | `--palette-5` | text-accent | small | text-light |
| 6 | `#ffc41c` | `--palette-6` | text-accent | small | text-dark |
| 7 | `#9d59c0` | `--palette-7` | badge | small | text-light |
| 8 | `#b42c3f` | `--palette-8` | text-accent | small | text-light |
| 9 | `#055e45` | `--palette-9` | text-accent | small | text-light |

## 3. Typography Rules

- **Heading Font:** `ui-sans-serif`, sans-serif
- **Body Font:** `ui-sans-serif`, sans-serif

### Type Hierarchy

| Role | Font | Size | Weight | Line Height | Letter Spacing |
|---|---|---|---|---|---|
| H2 | ui-sans-serif | 32px | 600 | 44px | normal |
| H3 | ui-sans-serif | 16px | 600 | 22px | normal |
| Body | ui-sans-serif | 18px | 400 | 25.008px | normal |

### Type Scale

| Token | Size | Suggested Usage |
|---|---|---|
| Display | `32px` | headings |
| H1 | `28px` | headings |
| H2 | `24px` | headings |
| H3 | `20px` | headings |
| H4 | `18px` | headings |
| Body L | `16px` | body / supporting text |
| Body | `14px` | body / supporting text |
| Small | `13px` | body / supporting text |
| XS | `12px` | body / supporting text |

## 4. Component Stylings

### Primary Button

```css
.btn-primary {
  background: #fafafa;
  color: #000000;
  border-radius: 0px;
  padding: 0px 0px;
  font-size: 16px;
  font-weight: 400;
  border: none;
  cursor: pointer;
}
```

### Ghost Button

```css
.btn-ghost {
  background: transparent;
  color: #1c1d1d;
  border-radius: 0px;
  padding: 16px 16px;
  font-size: 16px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}
```

### Ghost Button 2

```css
.btn-ghost-2 {
  background: transparent;
  color: #727272;
  border-radius: 0px;
  padding: 16px 16px;
  font-size: 16px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}
```

### Ghost Button 3

```css
.btn-ghost-3 {
  background: transparent;
  color: #000000;
  border-radius: 0px;
  padding: 7px 40px;
  font-size: 16px;
  font-weight: 400;
  border: none;
  cursor: pointer;
}
```

### Outline Button

```css
.btn-outline {
  background: transparent;
  color: #000000;
  border-radius: 16px;
  padding: 0px 0px;
  font-size: 16px;
  font-weight: 400;
  border: 0.833333px solid rgb(232, 232, 232);
  cursor: pointer;
}
```

### Pill Button

```css
.btn-pill {
  background: #eaffea;
  color: #00880d;
  border-radius: 9999px;
  padding: 8px 16px;
  font-size: 16px;
  font-weight: 600;
  border: 0.833333px solid rgba(0, 0, 0, 0);
  cursor: pointer;
}
```

## 5. Layout Principles

- **Base spacing unit:** `8px` — use multiples (16px, 24px, 32px, etc.)

### Spacing Scale (extracted from real elements)

| Token | Value | Role |
|---|---|---|
| spacing-1 | `8px` | element |
| spacing-2 | `12px` | element |
| spacing-3 | `4px` | element |
| spacing-4 | `16px` | element |
| spacing-5 | `24px` | card |
| spacing-6 | `9px` | element |
| spacing-7 | `7px` | element |
| spacing-8 | `64px` | section |

### Border Radius Scale

| Token | Value | Element |
|---|---|---|
| radius-card | `16px` | card |
| radius-button | `12px` | button |
| radius-card | `18px` | card |
| radius-subtle | `4px` | subtle |
| radius-card | `24px` | card |

## 6. Depth & Elevation

| Level | Shadow | Usage |
|---|---|---|
| Mid | `rgba(0, 0, 0, 0.08) 4px 4px 8px 0px` | Dropdowns, popovers |
| Mid | `rgba(0, 0, 0, 0.12) 0px 2px 8px 0px` | Dropdowns, popovers |
| Low | `rgba(179, 190, 232, 0.61) 0px 0.7px 12px 0px` | Cards, subtle elevation |

> **Note:** This site uses chromatic (color-tinted) shadows rather than pure black — this is a deliberate brand choice that adds warmth to elevation.

## 7. Do's and Don'ts

### Do
- Use `#ffffff` as the primary background color
- Use `ui-sans-serif` for all headings and `ui-sans-serif` for body text
- Use `#ffc41c` as the single dominant accent/CTA color
- Maintain `8px` as the base spacing unit — all gaps should be multiples
- Use rounded corners (`16px`+) consistently for all interactive elements
- Embrace bold color combinations — playful energy is the point
- Apply the shadow system for elevation — use the extracted shadow values

### Don't
- Don't use colors outside the extracted palette without justification
- Don't substitute ui-sans-serif/ui-sans-serif with generic alternatives
- Don't use irregular spacing — stick to 8px grid
- Don't use dark/black backgrounds — this is a light-themed design
- Don't use sharp corners — they feel hostile in this rounded design language
- Don't use pure black (#000000) for text — use `#000000` instead
- Don't add decorative elements not present in the original design — no badges, ribbons, banners, or ornaments unless the source site uses them
- Don't invent UI patterns the source site doesn't have — if the original has no NEW badge, don't add one just because a red is in the palette

## 8. Responsive Behavior

| Breakpoint | Width | Notes |
|---|---|---|
| Mobile | < 640px | Single column, stack sections, reduce font sizes ~80% |
| Tablet | 640–1024px | 2-column where appropriate, maintain spacing ratios |
| Desktop | 1024–1440px | Full layout as designed |
| Wide | > 1440px | Max-width container, center content |

- Touch targets: minimum 44×44px on mobile
- Maintain 8px base unit across breakpoints — only scale multipliers

## 9. Agent Prompt Guide

### Quick Color Reference

```
Background:  #ffffff
Text:        #000000
Accent:      #ffc41c
Secondary:   #e52535
Border:      #fafafa
```

### Example Prompts

1. "Build a hero section with a `#ffffff` background, `ui-sans-serif` heading in `#000000`, and a `#ffc41c` CTA button with 0px radius."
2. "Create a pricing card using background `#ebf3ff`, border `#fafafa`, `ui-sans-serif` for text, and 24px padding."
3. "Design a navigation bar — `#ffffff` background, `#000000` links, `#ffc41c` for active state."
4. "Build a feature grid with 3 columns, 24px gap, each card using the card component style."
5. "Create a footer with `#000000` background, `#ffffff` text, and 16px padding."

### Iteration Guide

1. Start with layout structure (sections, grid, spacing)
2. Apply colors from the palette — background first, then text, then accents
3. Set typography — font families, sizes from the type scale, weights
4. Add components — buttons, cards, inputs using the specs above
5. Apply border-radius consistently across all elements
6. Add shadows for depth — use the extracted shadow values, not defaults
7. Check responsive behavior — test mobile and tablet layouts
8. Final pass — verify all colors match, spacing is consistent, fonts are correct

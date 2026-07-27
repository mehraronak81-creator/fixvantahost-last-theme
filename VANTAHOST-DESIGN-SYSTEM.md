# VantaHost locked interface system

## Palette

| Role                 | Value   |
| -------------------- | ------- |
| Background           | #0B0D10 |
| Surface              | #14171B |
| Elevated / hover     | #1B1F24 |
| Border / divider     | #262B31 |
| Primary text         | #E7E9EC |
| Muted text           | #8A93A0 |
| Accent               | #4F7CFF |
| Online / success     | #3ECF8E |
| Warning / installing | #E8A33D |
| Offline / neutral    | #5B6570 |
| Error / suspended    | #F0575D |

Cards and panels always use a one-pixel #262B31 border. They never use a
colored edge, rainbow border, accent glow, or accent shadow.

## Accent constraints

The blue accent is limited to:

1. Primary button backgrounds.
2. The active sidebar indicator.
3. Keyboard/input focus rings.
4. Active chart data and its area fill.

## Button roles

-   Primary: blue background and primary text. One leading action per screen.
-   Secondary: elevated neutral background with a one-pixel neutral border.
-   Destructive: error background, reserved for destructive operations.

## Runtime identity

Runtime identity uses local Simple Icons SVG paths as 18px monochrome icons
directly beside the server name. There is no label, pill, or colored backdrop.

## Charts

Charts use smoothed lines, a blue-to-transparent area fill, timestamped hover
tooltips, exact unit formatting, and a flat zero baseline with a quiet empty
label. Network outbound data uses muted gray so the blue chart signal remains
the only accent.

## Accessibility

-   IBM Plex Sans is the UI typeface; technical values use system monospace.
-   Focus-visible uses the locked blue focus ring.
-   Status always includes text in addition to semantic color.
-   Reduced-motion preference disables nonessential animation.

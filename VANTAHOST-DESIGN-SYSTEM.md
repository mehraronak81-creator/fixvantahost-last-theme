# VantaHost interface system

## Direction

VantaHost uses a **Graphite Operations Ledger** visual language: dense enough for
infrastructure work, calm enough for long sessions, and recognisable through a
thin chartreuse telemetry rail. The previous navy/cyan galaxy gradients, large
glows, and interchangeable rounded cards were removed because they looked like a
generic gaming dashboard rather than a dependable hosting control surface.

## Core tokens

| Role | Token | Value |
| --- | --- | --- |
| Canvas | \`--vh-canvas\` | \`#141517\` |
| Surface | \`--vh-surface\` | \`#1d1f22\` |
| Raised surface | \`--vh-surface-raised\` | \`#272a2e\` |
| Chalk text | \`--vh-ink\` | \`#f3f1ea\` |
| Muted text | \`--vh-ink-muted\` | \`#9b9c99\` |
| Action accent | \`--vh-accent\` | \`#d6ff3f\` |

Semantic status colors are separate from the brand palette:
\`--vh-online\`, \`--vh-warning\`, and \`--vh-danger\`.

## Type, spacing, and elevation

- UI and display: IBM Plex Sans, 400–700.
- Technical values, paths, addresses, and timestamps: system monospace.
- Spacing: 4px base rhythm (\`4, 8, 12, 16, 24, 32\`).
- Corners: 4px controls, 8px cards, 12px large shells.
- Elevation: surface-tone layering, a one-pixel keyline, and shallow shadows.
  No neon glow is used to fake hierarchy.

## Signature element

The telemetry rail is a 2–3px chartreuse rule used for active navigation,
focused controls, and live-data cards. It connects navigation, server identity,
tables, terminal controls, and charts without adding decorative noise.

## Component rules

- Primary actions are chartreuse with graphite text.
- Secondary actions use a raised surface and visible keyline.
- Destructive actions are red only at the point of risk.
- Status is always communicated with text as well as color.
- Tables use stable columns, monospace metadata, and row-level hover contrast.
- Reduced-motion mode removes transforms and nonessential animation.
- Focus-visible rings must remain obvious on every interactive control.

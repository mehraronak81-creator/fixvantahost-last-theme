# VantaHost Galaxy for Blueprint

This is the Blueprint-compatible visual edition of VantaHost. It uses only
Blueprint's dashboard and admin CSS injection points: it does not overwrite
Pterodactyl controllers, routes, React source, or compiled assets.

## Compatibility

- Target: Blueprint `beta-2026-05` on a matching supported Pterodactyl Panel.
- Install it on a clean Blueprint-enabled panel, not over this repository's
  direct-fork presentation files.
- It is a visual theme. The direct-fork-only Recovery Centre, Security Centre,
  and Panel-side rate-limit changes are intentionally not included here.

## Install

1. Install Blueprint and confirm it is working on the panel.
2. Download `vantahost-galaxy.blueprint` from this repository's `dist` folder.
3. Install the archive through Blueprint's extension manager or CLI.
4. Let Blueprint rebuild assets and clear caches when prompted.

To build the archive yourself, zip the *contents* of this folder so `conf.yml`
is at the archive root, then name it `vantahost-galaxy.blueprint`.

## Safe removal

Remove the extension using Blueprint's extension manager. Because this package
does not replace Panel files, removal returns the presentation to the base
Blueprint panel without touching servers, users, backups, or Panel settings.

# VantaHost Galaxy for Blueprint

This is the Blueprint-compatible VantaHost edition. It uses Blueprint's
dashboard/admin CSS injection and its supported extension controller route;
it does not overwrite Pterodactyl controllers, routes, React source, or
compiled assets.

## Compatibility

- Target: Blueprint `beta-2026-05` on a matching supported Pterodactyl Panel.
- Install it on a clean Blueprint-enabled panel, not over this repository's
  direct-fork presentation files.
- Includes the galaxy UI plus a Blueprint-native Admin Operations page with
  backup recovery and a security posture overview.
- Backup recovery restores a completed backup over current server files. It
  is deliberately non-destructive and requires an idle, fully installed server.
- Stock Blueprint extensions cannot safely attach middleware to Pterodactyl's
  existing power, command, and file routes. The Operations page records
  recommended abuse-limit values only; enforce matching limits at Cloudflare,
  Nginx, Wings, or the host firewall. This repository's direct-fork build
  contains the Panel-side route throttles.

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
Blueprint panel without touching servers, users, or backups. Its saved
Operations policy values can be removed separately from Blueprint settings.

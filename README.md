# VantaHost

VantaHost is a premium-styled Pterodactyl Panel build by VantaBlack. It keeps
Pterodactyl's operational model while adding a refined dark interface, user
appearance controls, an admin Recovery Centre, and configurable control-plane
abuse safeguards.

## Included capabilities

- Obsidian-and-arctic-blue responsive dashboard, sidebar, authentication flow, and console.
- User-controlled theme, accent, density, font scale, contrast, and sidebar layout.
- Admin Recovery Centre to restore a completed backup over accidentally deleted files.
- Per-server throttles for console commands, power operations, and file mutations.
- Existing client and application API rate limits remain configurable in `config/http.php`.

## Installation / update

Deploy this repository using the standard Pterodactyl Panel installation steps.
After installing PHP and JavaScript dependencies, build the client assets and clear Panel caches:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan view:clear
php artisan config:clear
php artisan queue:restart
```

Review [SECURITY-HARDENING.md](./SECURITY-HARDENING.md) before exposing a node to the internet.

## Licence and brand

The upstream Pterodactyl source remains available under its [MIT licence](./LICENSE.md).
The historical GPL notice for inherited theme work is retained in
[`NookLicense.md`](./NookLicense.md); it cannot be removed or replaced by a
private licence. The proprietary VantaHost/VantaBlack brand licence applies
only to the name, marks, and brand assets: see
[VANTAHOST-BRAND-LICENSE.md](./VANTAHOST-BRAND-LICENSE.md).

VantaHost is an independent build and is not affiliated with Pterodactyl.

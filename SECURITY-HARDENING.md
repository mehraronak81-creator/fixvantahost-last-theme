# VantaHost security hardening

VantaHost adds per-server rate limits to the Panel control plane. Configure
them in `.env`, then run `php artisan config:clear`:

```dotenv
APP_API_CLIENT_RATELIMIT=256
VANTAHOST_COMMANDS_PER_MINUTE=30
VANTAHOST_POWER_ACTIONS_PER_MINUTE=10
VANTAHOST_FILE_MUTATIONS_PER_MINUTE=60
```

These controls slow control-panel abuse; they do not inspect or absorb traffic
sent directly to a game server allocation. To protect nodes from bandwidth
exhaustion or DDoS attacks, enforce protection at the network edge:

- Put the Panel behind a reverse proxy/WAF with request rate limits.
- Restrict the Wings API to the Panel's IP address with a host firewall.
- Use provider DDoS mitigation or a protected TCP/UDP proxy for every public allocation.
- Set firewall connection limits and per-customer bandwidth caps at the node/network layer.
- Monitor node network saturation and suspend abusive servers from the admin panel.

## Recovery Centre

The admin-only **Recovery Centre** restores a completed backup over a server's
current files. This is intended for an accidentally deleted folder: choose a
backup made before the deletion. The process does not request a directory wipe,
but it can overwrite files contained in the backup, so verify the selected
backup and wait for the server to be idle before starting recovery.

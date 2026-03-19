# MC Plugins – Mamba Coding

A pair of WordPress plugins for remote user management and WooCommerce automation, developed by [Mamba Coding](https://mambacoding.com).

---

## Plugins

### [MC Remote API](mc-remote-api/)

Exposes secure REST API endpoints on a WordPress site for remote user creation and role assignment.

**Endpoints:**
- `POST /wp-json/mc/v1/create-user` – Create a WordPress user
- `POST /wp-json/mc/v1/assign-role` – Assign a role to a user
- `GET  /wp-json/mc/v1/ping` – Test connection

📖 [Full API Documentation](mc-remote-api/docs/API.md) | [Setup Guide](mc-remote-api/docs/SETUP.md)

---

### [MC-Woo Remote Automations](mc-woo-remote-automations/)

Automates remote user creation and role assignment triggered by WooCommerce order status changes.

**Features:**
- Configure connections to remote MC Remote API sites
- Map WooCommerce products + order statuses to remote actions
- Detailed execution logs
- Configurable timeouts per automation

📖 [Setup Guide](mc-woo-remote-automations/docs/SETUP.md) | [Examples](mc-woo-remote-automations/docs/EXAMPLES.md)

---

## Quick Start

1. **Install MC Remote API** on your target WordPress site.
2. **Copy the API secret** from Settings → MC Remote API.
3. **Install MC-Woo Remote Automations** on your WooCommerce site.
4. **Create a Connection** pointing to the target site with the copied secret.
5. **Create an Automation** mapping your products and order status.
6. **Test** by placing a WooCommerce order and checking the Logs.

---

## Requirements

| | Minimum | Recommended |
|-|---------|-------------|
| WordPress | 5.0 | 6.4+ |
| PHP | 7.4 | 8.1+ |
| WooCommerce | 3.0 | 9.0+ |

---

## Features Comparison

| Feature | MC Remote API | MC-Woo Remote Automations |
|---------|:---:|:---:|
| REST API endpoints | ✅ | – |
| WooCommerce order hooks | – | ✅ |
| Remote user creation | ✅ (receives) | ✅ (sends) |
| Remote role assignment | ✅ (receives) | ✅ (sends) |
| Execution logs | – | ✅ |
| Admin settings page | ✅ | ✅ |

---

## Documentation

| Document | Description |
|----------|-------------|
| [mc-remote-api/docs/API.md](mc-remote-api/docs/API.md) | REST API reference |
| [mc-remote-api/docs/SETUP.md](mc-remote-api/docs/SETUP.md) | Installation and configuration |
| [mc-remote-api/docs/EXAMPLES.md](mc-remote-api/docs/EXAMPLES.md) | Integration examples |
| [mc-remote-api/docs/SECURITY.md](mc-remote-api/docs/SECURITY.md) | Security best practices |
| [mc-remote-api/docs/TROUBLESHOOTING.md](mc-remote-api/docs/TROUBLESHOOTING.md) | Common issues |
| [mc-woo-remote-automations/docs/SETUP.md](mc-woo-remote-automations/docs/SETUP.md) | Installation and configuration |
| [mc-woo-remote-automations/docs/EXAMPLES.md](mc-woo-remote-automations/docs/EXAMPLES.md) | Integration examples |
| [mc-woo-remote-automations/docs/SECURITY.md](mc-woo-remote-automations/docs/SECURITY.md) | Security best practices |
| [COMPATIBILITY.md](COMPATIBILITY.md) | Version compatibility matrix |
| [TESTING.md](TESTING.md) | Testing guide |
| [VALIDATION.md](VALIDATION.md) | Pre-submission checklist |

---

## Support

- **GitHub Issues:** [https://github.com/Inamo87100/mc-woo-remote-automations/issues](https://github.com/Inamo87100/mc-woo-remote-automations/issues)
- **Website:** [https://mambacoding.com](https://mambacoding.com)
- **Security:** security@mambacoding.com

---

## License

GPL v2 or later. See [LICENSE](mc-remote-api/LICENSE) for details.

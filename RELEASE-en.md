# Tools for FluentCart 0.9.1

- **Improved:** Updated shared Library and updater integration; protected paused rule settings and improved admin notices, save confirmations and changelog dialog reliability.

Upload the installable ZIP under Plugins → Add Plugin → Upload Plugin. Open FluentCart → Tools for FluentCart, choose a preset, review the values, save and enable rules. Both older single-item add-ons must be deactivated. Rules are disabled on a fresh installation. Without FluentCart, Cart Rules stays paused and a notice appears inside the admin. Tools settings are then under Settings → Tools for FluentCart. Installing and activating FluentCart restores the shop-menu location. While the module is paused, saved rules remain visible and read-only; direct save requests cannot change them. The independent uninstall preference remains available.

**Known upstream limitation:** Known upstream limitation in FluentCart 1.7.0: When creating a new website in a Multisite network, the FluentCart scheduler can report database errors because the required tables are not yet available. This issue originates in the parent plugin FluentCart 1.7.0. Tools for FluentCart documents the limitation and does not modify FluentCart to address it.

=== Tools for FluentCart ===
Contributors: deckerweb
Tags: fluentcart, cart, order, quantity
Requires at least: 7.1.2
Tested up to: 7.1.2
Requires PHP: 8.2
Stable tag: 0.9.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Practical tools for your FluentCart shop. The Cart Rules module provides single-item carts, minimum quantities and merchandise value, maximum quantities, and quantity steps.

== Description ==

* Cart Rules is the first module in the Tools series.
* Five presets with adjustable values.
* Combine total and per-product quantity rules in custom mode.
* Single-item mode replaces the previous product and hides quantity controls.
* Checkout validation uses server-side cart data.
* Site-specific settings, including Multisite.
* English source, German informal and formal translations.

The embedded deckerweb Updater checks public GitHub releases in the WordPress update flow. Requests reveal the installation’s network address and ordinary HTTP metadata to GitHub; no credentials or customer cart contents are sent. The optional shared Library online catalog is initially off. Enabling it retrieves approved public metadata from raw.githubusercontent.com. Local styling and translations require no external assets.

== Installation ==

Upload the installable ZIP under Plugins → Add Plugin → Upload Plugin. Open FluentCart → Tools for FluentCart, choose a preset, review the values, save and enable rules. Both older single-item add-ons must be deactivated. Rules are disabled on a fresh installation. Without FluentCart, Cart Rules stays paused and a notice appears inside the admin. Tools settings are then under Settings → Tools for FluentCart. Installing and activating FluentCart restores the shop-menu location.

== Frequently Asked Questions ==

= When are the rules enforced? =
Single-item mode adjusts editable carts automatically. All other rules are checked at checkout. Rule guidance appears in the standard cart and checkout views.

= Can I combine presets? =
Presets fill the fields as a starting point. Use custom rules to combine limits. Single-item mode is exclusive and clears the other limits on save.

= What counts as a product? =
Each product variation counts separately. A bundle counts as one cart row; its components are not individually limited.

= Does this limit orders per customer? =
No. Rules apply to each cart, without customer history or account limits.

= Does it work in Multisite? =
Settings belong to each website. Network activation introduces no shared order rule. New websites start with rules disabled.

= Which versions and integrations are supported? =
This release requires WordPress 7.1.2+, PHP 8.2+ and FluentCart 1.7.x. Other FluentCart minor versions leave the adapter inactive. Test subscriptions, order bumps, bundles, custom storefronts and other cart-changing add-ons before production use. Tools can be activated without FluentCart; the module stays paused until a supported version is active.

= What happens on uninstall? =
Settings are retained by default. Each website can opt to delete only its Cart Rules settings. Temporary updater and final-host Library caches are cleaned according to their scopes. FluentCart products, orders and carts are never deleted.

Full FAQ by topic: docs/FAQ.md

== Changelog ==

= 0.9.0 · 2026-10-07 =
* New: Cart Rules with single-item carts, minimum quantities and order value, quantity limits and steps.

= 0.1.0–0.8.0 =
* Misc: Development and test versions; not publicly released.

== License ==

Copyright © 2026 David Decker – DECKERWEB. GPL v2 or later; SPDX: GPL-2.0-or-later. Embedded deckerweb Plugin Library 0.7.0 and deckerweb Updater 2.1.0 are by the same author, GPL-2.0-or-later. Their stable runtime sources are copied unchanged; host adapters and translation catalogs are separate. No FluentCart implementation or premium code is distributed in this plugin.

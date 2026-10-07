# Tools for FluentCart

[Deutsch](README-de.md)

![Tools for FluentCart](https://raw.githubusercontent.com/deckerweb/tools-for-fluentcart/main/assets-github/banner-github-en.png)

<a id="about"></a>

## About

Practical tools for your FluentCart shop. The Cart Rules module provides single-item carts, minimum quantities and merchandise value, maximum quantities, and quantity steps.

Version **0.9.0** · WordPress **7.1.2+** · PHP **8.2+** · FluentCart **1.7.x**

[Documentation](docs/DOCUMENTATION.md) · [FAQ](docs/FAQ.md)

**Contents**

[About](#about) · [At a Glance](#glance) · [Installation](#installation) · [Features](#features) · [FAQ](#faq) · [Changelog](#changelog) · [Project](#project) · [Security and support](#security) · [License](#license)

<a id="glance"></a>

## At a Glance

- Cart Rules is the first module in the Tools series.
- Five presets with adjustable values.
- Combine total and per-product quantity rules in custom mode.
- Single-item mode replaces the previous product and hides quantity controls.
- Checkout validation uses server-side cart data.
- Site-specific settings, including Multisite.
- English source, German informal and formal translations.

<a id="installation"></a>

## Installation

Upload the installable ZIP under Plugins → Add Plugin → Upload Plugin. Activate FluentCart first. Open Settings → Tools for FluentCart, choose a preset, review the values, save and enable rules. Both older single-item add-ons must be deactivated. Rules are disabled on a fresh installation.

<a id="features"></a>

## Features

### One item only

Exactly one product variation with quantity one. A newly added item replaces the previous item. Existing editable carts are reduced to their last row when next loaded. Removing the item remains possible.

### Minimum quantities

Choose a minimum total number of units and/or a minimum per product variation. Different products may contribute to the total minimum.

### Minimum order value

Use the shop currency. Count merchandise after discounts, excluding shipping and fees. Tax inclusion follows FluentCart product pricing. A free product does not satisfy a positive minimum value.

### Quantity limits

Limit total units and/or units per product variation. Customers can adjust their cart freely; invalid carts cannot be ordered.

### Quantity steps

Each selected product variation must have a quantity divisible by the configured step. A step of 6 accepts 6, 12 or 18, rather than 1, 7 or 13. Limits are checked together so contradictory settings cannot be saved.

<a id="faq"></a>

## FAQ

### When are the rules enforced?

Single-item mode adjusts editable carts automatically. All other rules are checked at checkout. Rule guidance appears in the standard cart and checkout views.

### Can I combine presets?

Presets fill the fields as a starting point. Use custom rules to combine limits. Single-item mode is exclusive and clears the other limits on save.

### What counts as a product?

Each product variation counts separately. A bundle counts as one cart row; its components are not individually limited.

### Does this limit orders per customer?

No. Rules apply to each cart, without customer history or account limits.

### Does it work in Multisite?

Settings belong to each website. Network activation introduces no shared order rule. New websites start with rules disabled.

### Which versions and integrations are supported?

This development build targets WordPress 7.1.2+, PHP 8.2+ and FluentCart 1.7.x. Other FluentCart minor versions leave the adapter inactive. Test subscriptions, order bumps, bundles, custom storefronts and other cart-changing add-ons before production use.

### What happens on uninstall?

Settings are retained by default. Each website can opt to delete only its Cart Rules settings. Temporary updater and final-host Library caches are cleaned according to their scopes. FluentCart products, orders and carts are never deleted.

[FAQ by topic](docs/FAQ.md)

<a id="changelog"></a>

## Changelog

### 0.9.0 · 2026-10-07

- **New:** Cart Rules with single-item carts, minimum quantities and order value, quantity limits and steps.

<a id="project"></a>

## Project

Developed by David Decker – DECKERWEB. Part of the Tools series. Cart Rules is the first module.

<a id="security"></a>

## Security and support

Report vulnerabilities privately through the plugin repository’s Security → Report a vulnerability workflow. Include plugin, FluentCart, WordPress and PHP versions, reproduction steps and impact, without passwords or customer data. Ordinary issues may be reported in Issues. Do not publish security details in public issues.

Questions and ordinary bugs: repository Issues. Confidential reports: Security. Support: https://ko-fi.com/deckerweb, https://buymeacoffee.com/daveshine, https://paypal.me/deckerweb.

<a id="license"></a>

## License

Copyright © 2026 David Decker – DECKERWEB. GPL v2 or later; SPDX: GPL-2.0-or-later. Embedded deckerweb Plugin Library 0.7.0 and deckerweb Updater 2.1.0 are by the same author, GPL-2.0-or-later. Their stable runtime sources are copied unchanged; host adapters and translation catalogs are separate. No FluentCart implementation or premium code is distributed in this plugin.


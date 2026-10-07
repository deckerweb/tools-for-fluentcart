# FAQ by topic

[Deutsch](Fragen-nach-Themen)

## Setup and everyday use

### When are the rules enforced?

Single-item mode adjusts editable carts automatically. All other rules are checked at checkout. Rule guidance appears in the standard cart and checkout views.

### Can I combine presets?

Presets fill the fields as a starting point. Use custom rules to combine limits. Single-item mode is exclusive and clears the other limits on save.

## Products and limits

### What counts as a product?

Each product variation counts separately. A bundle counts as one cart row; its components are not individually limited.

### Does this limit orders per customer?

No. Rules apply to each cart, without customer history or account limits.

## Administration and compatibility

### Does it work in Multisite?

Settings belong to each website. Network activation introduces no shared order rule. New websites start with rules disabled.

### Which versions and integrations are supported?

This release requires WordPress 7.1.2+, PHP 8.2+ and FluentCart 1.7.x. Other FluentCart minor versions leave the adapter inactive. Test subscriptions, order bumps, bundles, custom storefronts and other cart-changing add-ons before production use. Tools can be activated without FluentCart; the module stays paused until a supported version is active.

### What happens on uninstall?

Settings are retained by default. Each website can opt to delete only its Cart Rules settings. Temporary updater and final-host Library caches are cleaned according to their scopes. FluentCart products, orders and carts are never deleted.


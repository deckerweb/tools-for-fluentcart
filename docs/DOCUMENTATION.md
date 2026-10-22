# Documentation

Upload the installable ZIP under Plugins → Add Plugin → Upload Plugin. Open FluentCart → Tools for FluentCart, choose a preset, review the values, save and enable rules. Both older single-item add-ons must be deactivated. Rules are disabled on a fresh installation. Without FluentCart, Cart Rules stays paused and a notice appears inside the admin. Tools settings are then under Settings → Tools for FluentCart. Installing and activating FluentCart restores the shop-menu location. While the module is paused, saved rules remain visible and read-only; direct save requests cannot change them. The independent uninstall preference remains available.

## One item only

Exactly one product variation with quantity one. A newly added item replaces the previous item. Existing editable carts are reduced to their last row when next loaded. Removing the item remains possible.

## Minimum quantities

Choose a minimum total number of units and/or a minimum per product variation. Different products may contribute to the total minimum.

## Minimum order value

Use the shop currency. Count merchandise after discounts, excluding shipping and fees. Tax inclusion follows FluentCart product pricing. A free product does not satisfy a positive minimum value.

## Quantity limits

Limit total units and/or units per product variation. Customers can adjust their cart freely; invalid carts cannot be ordered.

## Quantity steps

Each selected product variation must have a quantity divisible by the configured step. A step of 6 accepts 6, 12 or 18, rather than 1, 7 or 13. Limits are checked together so contradictory settings cannot be saved.

No custom public hooks or REST endpoints are introduced. This build uses FluentCart hooks and model events and the WordPress Settings API. The FluentCart adapter is deliberately limited to the audited 1.7.x interface.

Stores one site option: tffc_cart_rules. No customer data, extra database tables, analytics or tracking. No Cart Rules background tasks. Shared Library settings and caches belong to their documented website/network scope. Deactivation retains settings. Automatic single-item replacements cannot be undone by deactivation.

The embedded deckerweb Updater checks public GitHub releases in the WordPress update flow. Requests reveal the installation’s network address and ordinary HTTP metadata to GitHub; no credentials or customer cart contents are sent. The optional shared Library online catalog is initially off. Enabling it retrieves approved public metadata from raw.githubusercontent.com. Local styling and translations require no external assets.

## Known upstream limitation

Known upstream limitation in FluentCart 1.7.0: When creating a new website in a Multisite network, the FluentCart scheduler can report database errors because the required tables are not yet available. This issue originates in the parent plugin FluentCart 1.7.0. Tools for FluentCart documents the limitation and does not modify FluentCart to address it.

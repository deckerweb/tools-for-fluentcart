# FAQ by topic

[Deutsch](FAQ-de.md)

## Setup and everyday use

### Does this replace FluentCart?

No. This is an add-on for an active FluentCart installation. FluentCart owns products, carts, customers, orders and payments. Tools adds optional shop helpers.

### Which module should I start with?

For purchase limits use Cart Rules. For explanatory product text use Product Notes. For checking page assignments use Store Checks. Use Catalog Mode when products should remain visible but new online purchases should stop.

### Can I restrict rules to some products?

Yes. Custom quantity and value limits can count selected parent products and all their variations. Other cart rows do not contribute. One-item mode is always cart-wide. Product IDs are specific to each website.

### Will my current rules change after updating?

Existing Cart Rules settings are retained. New modules start disabled. An empty product selection retains the previous cart-wide scope. Imports deliberately switch imported modules off until reviewed.

## Storefront and limits

### What does Catalog Mode block?

It blocks native storefront cart purchases and checkout, including direct checkout links. Native purchase regions are hidden when the renderer supports this; older views have a scoped CSS fallback. Data is retained. Check custom layouts, caches and external payment flows separately.

### What happens without FluentCart?

The plugin remains active in the admin, with its settings under WordPress Settings. Dependent modules pause and preserve saved settings. Rule and module changes are blocked on the server; export and uninstall preferences remain available.

### Does it work in Multisite?

Settings are separate per website. FluentCart 1.7.0, 2.2.0 and 2.3.0 have an upstream scheduler issue during new website creation. Tools documents that issue and does not change FluentCart code.

### Are these legal checkout notices?

No. Product Notes is for ordinary product information. Tools does not provide legal texts, compliance certification or a replacement for specialist checkout-compliance software.

### Can I enter HTML or shortcodes into a note?

No. Notes are plain text, with line breaks. Use the dedicated product-note shortcode in a custom layout if you need to choose the position there.

### Why does a store check say Review for a working builder page?

Store Checks can recognize native shortcodes and FluentCart blocks in page content. A builder or template may produce the page elsewhere. Open the page and verify that flow manually.

### Does a sample test verify payments?

No. It checks one sample row against saved rules only, without a real cart, order, stock check or payment.

## Data and administration

### Are there per-customer purchase limits?

No. Cart Rules evaluates the current cart, not previous orders or customer history.

### What counts as one product?

Each variation is a separate cart row. A bundle counts as one row; nested components are not independently limited.

### What leaves my website?

The new modules make no external requests. The GitHub updater checks this repository; the shared Library has its own optional online catalog and installation behavior. These connections are described in Data and privacy. No order or customer data is exported by Tools.


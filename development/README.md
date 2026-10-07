# Development

Regenerate synchronized public documentation with `python3 development/build-docs.py`, then translations with `python3 development/build-languages.py`. Run from the repository root.

Integration tests require a disposable WordPress 7.1.2+ installation with FluentCart 1.7.0 and this plugin active, a local MySQL database, and administrator user ID 1. Run with `wp eval-file /path/to/tests/integration.php`; similarly run checkout.php, products.php and update.php. multisite.php requires a disposable network on 127.0.0.1:8894 and creates/deletes a fixture site. Tests change options and remove their fixture data. Never run against a production shop.

The installable plugin ZIP contains runtime code, languages and public documentation only. Keep repository tooling, Wiki sources and Funding/Pages configuration outside that ZIP.

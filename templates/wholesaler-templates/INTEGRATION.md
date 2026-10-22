# WholeMarket integration

This pack is designed for the existing WholeMarket Laravel template registry. It is already registered in this working copy with key `wholesaler-templates` and label `Wholesale Atelier`.

## Runtime files

Copy the package files into the corresponding application paths:

```text
resources/views/shop/wholesaler-templates.blade.php
resources/views/shop/wholesaler-templates-product.blade.php
public/templates/wholesaler-templates/style.css
public/templates/wholesaler-templates/placeholder-hero.svg
public/templates/wholesaler-templates/placeholder-product.svg
```

## Registration steps

1. Add the `WHOLESALE_ATELIER_TEMPLATE` constant, label, detail metadata, and view mappings to `app/Models/Shop.php`.
2. Add `wholesaler-templates` to the intended plan allowlists in `config/wholemarket.php`. WholeMarket uses the existing `ShopAccessService` to enforce those plan restrictions.
3. Add a migration that appends the key only to the intended persisted `plan_versions.template_keys` records and creates a shared (`shop_type = null`) `templates` record.
4. Add the safe Design Settings endpoint under the authenticated seller dashboard. It must validate allowlisted colour/font values, fixed section-order choices, bounded hero copy, and booleans; do not accept custom CSS, scripts, uploadable templates, or arbitrary view names.
5. Register the template metadata in `DatabaseSeeder` for newly seeded databases.
6. Run `php artisan migrate` in each target environment. Do not use `migrate:fresh` on a populated installation.

## Required existing data

- `Shop`: name, type, logo/banner paths, business description, address, contact details, and social URLs.
- `ShopSetting`: `template_key`, `colour_key`, `font_key`, `sections`, and `show_prices`.
- Catalogue data supplied by the existing `StorefrontController`: products, categories, fabrics, and colours.
- Existing `enquiries.store` endpoint for the contact form.

## PWA and privacy

This pack intentionally adds no manifest or service worker. WholeMarket's existing shared PWA configuration must remain responsible for caching only static assets and excluding authenticated and mutable routes.

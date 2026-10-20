# WholeMarket implementation brief
Use approved-storefront/ as the exact visual reference. These are static frontend demo files, not a completed backend. Preserve design while integrating Laravel Blade and a database. Do not replace the design with a new template.

Build one PWA supporting 100 saree wholesale shops. Public paths: /shops/{slug}, with permanent product pages. Market homepage lists approved shops. Roles: admin and seller; no customer login initially. Admin creates seller accounts, approves/deactivates shops, and sets limits. Each seller manages only their own profile, contact details, categories, and products. Defaults: 5 categories, 20 products, 2 images per product, one logo and banner. Enforce limits server-side.

Products: name, shop-unique code, category, fabric, colour, description, optional INR wholesale price, minimum quantity, optional pack size, blouse inclusion, availability, images. Contact-for-price when price is absent. Search and category/fabric/price filters must use actual data. Favourites are browser-local and namespaced by shop. Floating WhatsApp and contact icons use the current shop's validated contact details; omit unset links. No invented contacts.

Use Laravel, Blade, Tailwind CSS, SQLite locally and MySQL-compatible migrations. Inspect available tools before installing. Create backend in the project root without deleting approved-storefront. Store uploads safely, validate JPEG/PNG/WebP contents and 5MB limit, resize images. Enforce role policies and ownership for every operation. CSRF, hashed passwords, login throttling, environment secrets. No cross-shop access through guessed IDs.

PWA: market-level identity, HTTPS production, public static cache only, offline fallback; no private/auth response caching, no offline writes, no stale catalogue prices. Audit the supplied demo service worker and adapt its scope/cache handling rather than copying unchanged.

Exclude cart, payments, delivery, paid messaging APIs, and subscription billing in v1. Demo prices/photos/specifications are illustrative only. No production credentials in seeders. Three fictional shops for development.

Build stages: inspect/template integration; migrations/auth/policies; admin; seller CRUD/uploads; dynamic public pages; PWA; tests/docs. Test tenant isolation, permissions, limits, invalid uploads, inactive visibility, search/filter and WhatsApp routing. Document setup, local URL, test commands, database/image backups, and production requirements. Run locally; do not publish without instruction.

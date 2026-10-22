# WholeMarket implementation requirements

This file records the configurable defaults used for the local implementation of the
WholeMarket build brief. It is not a statement of live commercial terms.

## Phased delivery

1. **Foundation** — separate wholesaler and retailer shops, memberships, route redirects,
   approval/suspension state and audit records.
2. **Storefronts** — retain the existing five shared-data templates, add public route
   separation, entitlement-aware visibility and secure catalogue boundaries.
3. **Plans and access** — seed unpriced Basic, Normal, Pro and Retailer Basic plans,
   manual subscriptions, payments and billing-event idempotency.
4. **Retailer sponsorship** — invitation, atomic acceptance, active-slot checks, expiry,
   grace and independent paid-retailer conversion.
5. **Billing adapter** — manual/test adapter by default; no live provider or live charging
   is enabled without credentials and owner approval.
6. **Release readiness** — PWA cache safety, scheduler/reminder support, setup guidance
   and acceptance coverage.

## Configurable defaults

- Public canonical routes are `/wholesaler/{shopSlug}` and `/retailer/{shopSlug}`. Existing
  root and `/shops/...` addresses redirect to the correct canonical path.
- Local plan prices are deliberately unset. Admins record manual payments and renewals only.
- Basic / Normal / Pro product limits are 30 / 200 / 1,000; category limits are 5 / 15 / 50;
  image limits are 2 / 4 / 6. Retailer Basic is 20 products, 5 categories and 2 images.
- A Pro wholesaler can sponsor 20 active retailer shops by default. Sponsorship terms are
  30, 60 or 90 days, invitations expire after 7 days, and a former Pro sponsor allows a
  7-day retailer grace period.
- Retailer Basic uses the Classic template unless an administrator changes the allowed
  template selection.
- Local mail uses Laravel's `log` mailer. Invitation and reminder delivery records are
  retained even when no real mail provider is configured.
- SQLite is supported for local development. The 20-slot concurrency guarantee must be
  re-verified on the intended production database (MySQL or PostgreSQL) before launch.

## Known deliberate boundaries

- No cart, checkout, shipping, order payment collection, marketplace commission, stock
  synchronization, custom-domain automation or arbitrary page builder is included.
- Product prices use `price_paise` as the authoritative integer value. The existing
  `wholesale_price` field remains temporarily for backwards-compatible rendering.
- The existing local-only demo accounts remain excluded from production seeding.

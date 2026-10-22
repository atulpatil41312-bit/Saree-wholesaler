# Wholesale Atelier

Wholesale Atelier is an original WholeMarket storefront template for saree wholesalers and retailers. It uses the current shop, catalogue, product, contact, social, plan, and enquiry data already available in the Laravel application.

## Design

The design uses a warm editorial palette, a compact sourcing-oriented header, an image-led hero, practical catalogue filters, collection panels, detailed product specifications, and direct buyer actions. It is responsive at small mobile, tablet, and wide desktop layouts without depending on remote fonts, JavaScript libraries, or a second PWA worker.

The template does not add checkout, payment, shipping, tracking, or any customer data collection beyond WholeMarket's existing enquiry form.

## Included files

- `template.json` — pack metadata and safe setting allowlists.
- `resources/views/shop/` — catalogue and product-detail Blade views.
- `public/templates/wholesaler-templates/` — scoped CSS and original SVG fallback artwork.
- `INTEGRATION.md` — exact Laravel registration and activation steps.
- `ASSET-SOURCES.md` — image and typography licensing notes.
- `sample-data.md` — clearly-labelled demonstration strings used in documentation.

## Seller activation

1. Sign in as an eligible seller.
2. Open **Dashboard → Design**.
3. Preview or apply **Wholesale Atelier**.
4. In the same page, use **Wholesale Atelier Settings** to choose an approved colour palette, font pairing, section order/visibility, price visibility, and hero copy.
5. Update the logo, banner, contact details, and social URLs under **Dashboard → Shop profile**.

Template choice changes only the public presentation. It does not modify products, categories, enquiries, plan settings, or subscriptions.

## Preview note

No desktop/mobile screenshots are included in this pack because the local browser automation surface was unavailable during packaging. The layout has responsive CSS breakpoints for approximately 360px, 768px, and 1440px and is covered by server-side feature tests.

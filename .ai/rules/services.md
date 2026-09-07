---
paths:
  - 'app/Services/**'
---

# Services

## Checkout is WhatsApp inquiry only
Cart is session-based (`App\Services\Cart\Cart`). Checkout creates an `Inquiry` + items and opens wa.me — no payment gateway. Prices are whole currency units (PKR integers), not cents.

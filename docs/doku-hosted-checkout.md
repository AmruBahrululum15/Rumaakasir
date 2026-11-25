# DOKU Hosted Checkout

The existing DOKU merchant integration serves Rumaakasir Premium subscriptions.
Store sales continue using Cash, manual QRIS, or tenant Midtrans credentials.
Do not route tenant sales to the platform's DOKU merchant without a separate
merchant/settlement design.

## Flow

`POST /subscription/checkout` obtains the price and business from server data,
creates an invoice and one Request-Id, signs one raw JSON body, sends that exact
body to `/checkout/v1/payment`, and redirects to `response.payment.url`.
QRIS/VA selection is on DOKU's own page, subject to merchant channel availability.
The return page reads stored status only. Only a validated notification can
activate Premium. Duplicate SUCCESS callbacks do not extend subscriptions twice.

For Checkout, a FAILED notification describes an unsuccessful payment attempt;
it does not close the entire invoice while another payment method can be used.
The attempt is recorded in `last_payment_status`. The payment page expiry uses
the provider's `expired_date`. Unknown notification statuses never activate a plan.

## Sandbox verification

Use a separate test deployment/database and DOKU Checkout Sandbox credentials:

```dotenv
DOKU_ENV=sandbox
DOKU_BASE_URL=https://api-sandbox.doku.com
DOKU_CLIENT_ID=
DOKU_SECRET_KEY=
DOKU_NOTIFICATION_URL=https://YOUR-PUBLIC-TEST-HOST/api/doku/subscription/notification
```

Set APP_URL to that deployment's reachable public URL. Never paste credentials
into source files or chat, and never use production credentials on the sandbox
host. Keep existing production transactions on their original configuration.
Run `php artisan optimize:clear` and `php artisan config:clear` after configuration.

Create a subscription from an eligible test store, open the returned official
checkout URL, and simulate payment via DOKU Sandbox. Verify that the signed
webhook arrives and settles the matching order exactly once. A successful unit
or HTTP-fake test is not proof of a successful live sandbox payment.

Production uses `DOKU_ENV=production` and `https://api.doku.com`, with its own
Checkout credential pair. A previous production attempt returned
`invalid_client_id`; do not treat local credential presence as merchant validation.

Official references:
- https://developers.doku.com/accept-payments/doku-checkout/integration-guide/backend-integration
- https://developers.doku.com/get-started-with-doku-api/notification/best-practice
- https://developers.doku.com/accept-payments/doku-checkout/integration-guide/simulate-payment-and-notification

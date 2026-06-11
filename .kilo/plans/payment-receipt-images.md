# Add Receipt Images to Payments

## Overview

Add a `payment_receipts` table tied to `payments` (FK `payment_id`), allowing image uploads per payment during rental creation/editing, and display in the detail view.

---

## Steps

### 1. Database Migration

Create `database/migrations/XXXX_XX_XX_create_payment_receipts_table.php` following the `product_images` pattern.

Schema: `payment_receipts` table with `id`, `foreignId('payment_id')->constrained()->cascade`, `file_path` (string), `notes` (nullable text), timestamps.

### 2. Model — `app/Models/PaymentReceipt.php`

- `$fillable`: `payment_id`, `file_path`, `notes`
- `BelongsTo` relationship: `payment()`

### 3. Update `app/Models/Payment.php`

- Add `HasMany` relationship: `receipts()` -> `PaymentReceipt::class`

### 4. Update `app/Http/Requests/StoreRentalRequest.php`

Add validation:
- `payments.*.receipts` => nullable array
- `payments.*.receipts.*.file` => nullable image, max 10MB
- `payments.*.receipts.*.notes` => nullable string

### 5. Update `app/Http/Controllers/RentalController.php`

- In both `store` and `update`: after creating each Payment, loop `payment['receipts']`, access the file via `$request->file("payments.{$pIndex}.receipts.{$rIndex}.file")`, store to `payment-receipts` disk, create PaymentReceipt record
- Add `payments.receipts` to the eager load in `index()` and DashboardController

### 6. Frontend — RentalForm.vue

**Form type** — add `receipts: Array<{id, file, previewUrl}>` to each payment entry

**addPayment** — init with `receipts: []`

**removePayment** — clean up receipt preview URLs

**Template** — per payment card, below reference # field, add receipt upload area (same multi-image pattern as items section):
- Hidden `<input ref="receiptFileInput" type="file" accept="image/*" multiple>`
- Upload Receipt button targeting `paymentIndex`
- Thumbnail grid with remove overlay
- Track via `receiptFileInput` ref + `receiptTargetIndex` ref + handlers (`selectPaymentReceipt`, `onReceiptFileSelect`, `removePaymentReceipt`)

**submit** — also check `form.payments.some(p => p.receipts.length > 0)` for forceFormData

### 7. Frontend — RentalDetails.vue

**Type** — add `receipts?: Array<{id, file_path, notes}>` to payment
**Template** — show receipt thumbnails per payment, clickable to open ImagePreview carousel

### 8. Frontend — RentalEdit.vue

Same as RentalForm for uploads, plus display existing receipts from `payments.receipts`

### 9. Types — Index.vue, RentalList.vue

Add `receipts` to payment type in paginated rental data

---

## Files Changed

| File | Action |
|------|--------|
| `database/migrations/XXXX_create_payment_receipts_table.php` | **New** |
| `app/Models/PaymentReceipt.php` | **New** |
| `app/Models/Payment.php` | Edit — add receipts() HasMany |
| `app/Http/Requests/StoreRentalRequest.php` | Edit — receipts validation |
| `app/Http/Controllers/RentalController.php` | Edit — store receipts, eager load |
| `resources/js/Components/sidebar/Rental/RentalForm.vue` | Edit — receipt upload UI |
| `resources/js/Components/sidebar/Rental/RentalDetails.vue` | Edit — display receipts |
| `resources/js/Components/sidebar/Rental/RentalEdit.vue` | Edit — receipt upload + display |
| `resources/js/Pages/Rentals/Index.vue` | Edit — receipts type |
| `resources/js/Components/sidebar/Rental/RentalList.vue` | Edit — receipts type |

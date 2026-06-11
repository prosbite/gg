# Add Camera Capture for Payment Receipts

## Problem

The receipt upload section in both RentalForm.vue and RentalEdit.vue only supports file-picker upload ("Upload Receipt" button). Unlike item images which have both "Take Photo" (camera) and "Upload Photos" buttons, receipts lack a camera capture option.

## Solution

Extend the existing shared camera overlay to support both item-image and receipt modes, then add a "Take Photo" button next to "Upload Receipt" in the payment section.

## Changes per file

### 1. RentalForm.vue (script)

- Add `cameraMode` ref with type `'"'"'item'"'"' | '"'"'receipt'"'"'`
- Modify `startCamera(index, mode)` — accepts `mode` param, sets `cameraMode`
- Modify `capturePhoto()` — check `cameraMode`:
  - `'"'"'item'"'"'` → call existing `addImageToItem`
  - `'"'"'receipt'"'"'` → call new `addReceiptToPayment(paymentIndex, file)`
- Add `addReceiptToPayment(index, file)` helper (same pattern as `addImageToItem`)
- Add `startReceiptCamera(index)` wrapper (calls `startCamera(index, '"'"'receipt'"'"')`)

### 2. RentalForm.vue (template)

- In the receipt section, add a "Take Photo" button (camera icon) before "Upload Receipt":
  - `@click="startReceiptCamera(index)"`
  - Same styling as the item "Take Photo" button but text-xs
- The existing camera overlay works unchanged — `capturePhoto` routes based on `cameraMode`

### 3. RentalEdit.vue (script + template)

- Same script changes as RentalForm.vue
- Same template addition for the "Take Photo" button in the receipt section

## Files changed

| File | Action |
|------|--------|
| resources/js/Components/sidebar/Rental/RentalForm.vue | Edit |
| resources/js/Components/sidebar/Rental/RentalEdit.vue | Edit |

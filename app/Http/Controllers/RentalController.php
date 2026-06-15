<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRentalRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Rental;
use App\Models\RentalItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class RentalController extends Controller
{
    public function index(): Response
    {
        $tab = request('tab', 'all');

        $rentals = Rental::with(['customer', 'items.product.images', 'payments.receipts'])
            ->when($tab !== 'all', fn($q) => $q->where('status', $tab))
            ->when(request('search'), fn($q, $search) => $q->whereHas('customer', fn($q) => $q->whereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"])->orWhere('contact_number', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $products = Product::with(['category'])
            ->where('is_active', true)
            ->whereIn('status', ['available', 'rented'])
            ->orderBy('name')
            ->get(['id', 'item_code', 'name', 'custom_rental_fee', 'custom_security_deposit', 'status']);

        return Inertia::render('Rentals/Index', [
            'rentals' => $rentals,
            'products' => $products,
            'activeTab' => $tab,
        ]);
    }

    public function store(StoreRentalRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $rental = DB::transaction(function () use ($data, $request) {
            $customer = $this->resolveCustomer($data['customer'] ?? [], isset($data['customer_id']) ? (int) $data['customer_id'] : null);

            $rental = Rental::create([
                'customer_id' => $customer->id,
                'pickup_date' => $data['pickup_date'],
                'return_date' => $data['return_date'],
                'status' => 'reserved',
                'internal_notes' => $data['internal_notes'] ?? null,
            ]);

            foreach ($data['items'] as $index => $item) {
                $productId = $item['product_id'];

                if (!$productId && !empty($item['description'])) {
                    $product = Product::create([
                        'item_code' => $this->generateRentalItemCode(),
                        'name' => 'Draft: ' . \Illuminate\Support\Str::limit($item['description'], 50),
                        'description' => $item['description'],
                        'category_id' => Category::firstOrCreate(['name' => 'Quick Add', 'prefix' => 'QA'])->id,
                        'status' => 'rented',
                        'is_active' => true,
                        'is_draft' => true,
                    ]);

                    $imageEntries = $request->input("items.{$index}.images", []);
                    foreach ($imageEntries as $imgIndex => $imageData) {
                        $file = $request->file("items.{$index}.images.{$imgIndex}.file");
                        if (!$file) continue;
                        $path = $file->store('products', 'public');
                        $product->images()->create([
                            'file_path' => $path,
                            'label' => null,
                            'sort_order' => $imgIndex,
                            'is_primary' => $imgIndex === 0,
                        ]);
                    }

                    $productId = $product->id;
                }

                if ($productId) {
                    RentalItem::create([
                        'rental_id' => $rental->id,
                        'product_id' => $productId,
                        'rental_fee' => $item['rental_fee'] ?? 0,
                        'security_deposit' => $item['security_deposit'] ?? 0,
                    ]);

                    Product::where('id', $productId)->where('status', '!=', 'rented')->update(['status' => 'rented']);
                }
            }

            if (!empty($data['payments'])) {
                foreach ($data['payments'] as $pIndex => $payment) {
                    $receipts = $payment['receipts'] ?? [];
                    unset($payment['receipts']);
                    $paymentModel = $rental->payments()->create($payment);

                    foreach ($receipts as $rIndex => $receipt) {
                        $file = $request->file("payments.{$pIndex}.receipts.{$rIndex}.file");
                        if (!$file) continue;
                        $path = $file->store('payment-receipts', 'public');
                        $paymentModel->receipts()->create([
                            'file_path' => $path,
                            'notes' => $receipt['notes'] ?? null,
                        ]);
                    }
                }
            }

            return $rental;
        });

        return redirect()->route('dashboard')
            ->with('success', 'Rental created successfully.');
    }

    public function update(StoreRentalRequest $request, Rental $rental): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $rental, $request) {
            $customer = $this->resolveCustomer($data['customer'] ?? [], isset($data['customer_id']) ? (int) $data['customer_id'] : null);

            $rental->update([
                'customer_id' => $customer->id,
                'pickup_date' => $data['pickup_date'],
                'return_date' => $data['return_date'],
                'internal_notes' => $data['internal_notes'] ?? null,
            ]);

            $existingItemIds = $rental->items()->pluck('id')->toArray();
            $submittedItemIds = [];

            foreach ($data['items'] as $index => $item) {
                $productId = $item['product_id'];

                if (!$productId && !empty($item['description'])) {
                    $product = Product::create([
                        'item_code' => $this->generateRentalItemCode(),
                        'name' => 'Draft: ' . \Illuminate\Support\Str::limit($item['description'], 50),
                        'description' => $item['description'],
                        'category_id' => Category::firstOrCreate(['name' => 'Quick Add', 'prefix' => 'QA'])->id,
                        'status' => 'rented',
                        'is_active' => true,
                        'is_draft' => true,
                    ]);

                    $imageEntries = $request->input("items.{$index}.images", []);
                    foreach ($imageEntries as $imgIndex => $imageData) {
                        $file = $request->file("items.{$index}.images.{$imgIndex}.file");
                        if (!$file) continue;
                        $path = $file->store('products', 'public');
                        $product->images()->create([
                            'file_path' => $path,
                            'label' => null,
                            'sort_order' => $imgIndex,
                            'is_primary' => $imgIndex === 0,
                        ]);
                    }

                    $productId = $product->id;
                }

                if ($productId && isset($item['id']) && in_array($item['id'], $existingItemIds)) {
                    $rentalItem = RentalItem::find($item['id']);
                    $oldProductId = $rentalItem->product_id;
                    $rentalItem->update([
                        'product_id' => $productId,
                        'rental_fee' => $item['rental_fee'] ?? 0,
                        'security_deposit' => $item['security_deposit'] ?? 0,
                    ]);

                    if ($oldProductId !== $productId) {
                        Product::where('id', $oldProductId)->where('status', 'rented')->update(['status' => 'available']);
                        Product::where('id', $productId)->where('status', '!=', 'rented')->update(['status' => 'rented']);
                    }

                    $submittedItemIds[] = $item['id'];
                } elseif ($productId) {
                    $rentalItem = $rental->items()->create([
                        'product_id' => $productId,
                        'rental_fee' => $item['rental_fee'] ?? 0,
                        'security_deposit' => $item['security_deposit'] ?? 0,
                    ]);
                    Product::where('id', $productId)->where('status', '!=', 'rented')->update(['status' => 'rented']);
                    $submittedItemIds[] = $rentalItem->id;
                }
            }

            $toDelete = array_diff($existingItemIds, $submittedItemIds);
            if (!empty($toDelete)) {
                $deletedProducts = RentalItem::whereIn('id', $toDelete)->pluck('product_id');
                RentalItem::whereIn('id', $toDelete)->delete();
                Product::whereIn('id', $deletedProducts)->where('status', 'rented')->update(['status' => 'available']);
            }

            if (!empty($data['payments'])) {
                foreach ($data['payments'] as $pIndex => $payment) {
                    $receipts = $payment['receipts'] ?? [];
                    unset($payment['receipts']);
                    $paymentModel = $rental->payments()->create($payment);

                    foreach ($receipts as $rIndex => $receipt) {
                        $file = $request->file("payments.{$pIndex}.receipts.{$rIndex}.file");
                        if (!$file) continue;
                        $path = $file->store('payment-receipts', 'public');
                        $paymentModel->receipts()->create([
                            'file_path' => $path,
                            'notes' => $receipt['notes'] ?? null,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('rentals.index')
            ->with('success', 'Rental updated successfully.');
    }

    public function destroy(Rental $rental): RedirectResponse
    {
        DB::transaction(function () use ($rental) {
            $productIds = $rental->items()->pluck('product_id');
            $rental->items()->delete();
            $rental->payments()->delete();
            $rental->delete();
            Product::whereIn('id', $productIds)->where('status', 'rented')->update(['status' => 'available']);
        });

        return redirect()->route('rentals.index')
            ->with('success', 'Rental deleted successfully.');
    }

    public function pickup(Rental $rental): RedirectResponse
    {
        $rental->update(['status' => 'picked_up']);
        return redirect()->route('rentals.index')
            ->with('success', 'Rental marked as picked up.');
    }

    public function return(Rental $rental): RedirectResponse
    {
        $rental->update([
            'status' => 'returned',
            'actual_returned_at' => now(),
        ]);
        return redirect()->route('rentals.index')
            ->with('success', 'Rental marked as returned.');
    }

    public function complete(Rental $rental): RedirectResponse
    {
        DB::transaction(function () use ($rental) {
            $rental->update(['status' => 'completed']);
            $productIds = $rental->items()->pluck('product_id');
            Product::whereIn('id', $productIds)->where('status', 'rented')->update(['status' => 'available']);
        });
        return redirect()->route('rentals.index')
            ->with('success', 'Rental completed successfully.');
    }

    public function cancel(Rental $rental): RedirectResponse
    {
        DB::transaction(function () use ($rental) {
            $rental->update(['status' => 'cancelled']);
            $productIds = $rental->items()->pluck('product_id');
            Product::whereIn('id', $productIds)->where('status', 'rented')->update(['status' => 'available']);
        });
        return redirect()->route('rentals.index')
            ->with('success', 'Rental cancelled.');
    }

    private function resolveCustomer(array $customerData, ?int $customerId): Customer
    {
        if ($customerId) {
            return Customer::findOrFail($customerId);
        }

        return Customer::create($customerData);
    }

    private function generateRentalItemCode(): string
    {
        $prefix = 'RENT-' . now()->format('Ymd');
        $last = Product::where('item_code', 'like', "{$prefix}-%")
            ->orderBy('item_code', 'desc')
            ->first();

        $next = $last ? ((int) substr($last->item_code, -4)) + 1 : 1;

        return "{$prefix}-" . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}

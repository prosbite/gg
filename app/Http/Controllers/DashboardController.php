<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rental;

class DashboardController extends Controller
{
    public function index()
    {
        $tab = request('tab', 'reserved');
        $search = request('search');

        $statusMap = [
            'reserved' => ['reserved'],
            'picked_up' => ['reserved', 'picked_up'],
            'returned' => ['returned'],
        ];

        $statuses = $statusMap[$tab] ?? ['reserved'];

        $rentals = Rental::with(['customer', 'items.product.images', 'payments.receipts'])
            ->whereIn('status', $statuses)
            ->when($search, fn($q, $s) => $q->whereHas('customer', fn($q) => $q->whereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$s}%"])->orWhere('contact_number', 'like', "%{$s}%")))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $products = Product::with(['category'])
            ->where('is_active', true)
            ->whereIn('status', ['available', 'rented'])
            ->orderBy('name')
            ->get(['id', 'item_code', 'name', 'custom_rental_fee', 'custom_security_deposit', 'status']);

        return inertia('Dashboard', [
            'rentals' => $rentals,
            'products' => $products,
            'activeTab' => $tab,
        ]);
    }
}




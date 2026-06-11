<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
{
    $customers = Customer::query()
        ->when(request('search'), fn($q, $search) => $q->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('contact_number', 'like', "%{$search}%");
        }))
        ->orderBy('first_name')
        ->paginate(15)
        ->withQueryString();

    return Inertia::render('Customers/Index', [
        'customers' => $customers,
    ]);
}

    public function create(): Response
    {
        return Inertia::render('Customers/Create');
    }

   public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('identification')) {
            $data['identification'] = $request->file('identification')
                ->store('customers/identification', 'public');
        }

        Customer::create($data);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): Response
    {
        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        $customers = Customer::where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$query}%"]);
        })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'contact_number', 'address', 'affiliation', 'social_media_link']);

        return response()->json($customers);
    }
}

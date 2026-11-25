<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search'));

        $businessId = $request->user()->business_id;
        $summary = [
            'total' => Customer::where('business_id', $businessId)->count(),
            'transacting' => Customer::where('business_id', $businessId)->whereHas('sales')->count(),
            'with_contact' => Customer::where('business_id', $businessId)->whereNotNull('phone')->where('phone', '!=', '')->count(),
        ];
        $customers = Customer::query()->where('business_id', $businessId)
            ->withCount(['sales', 'bookings',
                'sales as cashier_count' => fn($q) => $q->where(fn($q)=>$q->where('order_source','cashier')->orWhere(fn($q)=>$q->whereNull('order_source')->where(fn($q)=>$q->whereNull('notes')->orWhere('notes','not like','%ORDER_TYPE=QR_TABLE%')))),
                'sales as qr_count' => fn($q) => $q->where(fn($q)=>$q->where('order_source','table_qr')->orWhere(fn($q)=>$q->whereNull('order_source')->where('notes','like','%ORDER_TYPE=QR_TABLE%'))),
                'sales as portal_count' => fn($q) => $q->where('order_source','customer_portal'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($customerQuery) use ($search) {
                    $customerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search', 'summary'));
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:30', Rule::unique('customers', 'phone')->ignore($customer->id)],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['phone'] = trim($validated['phone']);

        $customer->update($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}

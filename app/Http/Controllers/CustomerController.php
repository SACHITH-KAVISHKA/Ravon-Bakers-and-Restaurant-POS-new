<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Admin සහ Director ට පමණක් අවසර ලබා දීම.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $role = strtolower(auth()->user()->role ?? '');
            if (!in_array($role, ['admin', 'director'])) {
                abort(403, 'Unauthorized access to Customer Management.');
            }
            return $next($request);
        });
    }

    /**
     * පාරිභෝගික ලැයිස්තුව පෙන්වීම
     */
    public function index(Request $request)
    {
        $query = Customer::orderBy('name', 'asc');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%$search%")
                  ->orWhere('vat_no', 'LIKE', "%$search%");
        }

        $customers = $query->paginate(50);
        return view('customers.index', compact('customers'));
    }

    /**
     * අලුත් පාරිභෝගිකයෙකු ඇතුළත් කිරීමේ View එක
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * පාරිභෝගික දත්ත Database එකට සේව් කිරීම
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:customers,name',
            'vat_no' => 'nullable|string|max:50',
            'telephone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        Customer::create($request->all());

        return redirect()->route('customers.index')->with('success', 'Customer registered successfully.');
    }

    /**
     * පාරිභෝගික දත්ත සංස්කරණය කිරීම
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * පාරිභෝගික දත්ත Update කිරීම
     */
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:customers,name,' . $customer->id,
            'vat_no' => 'nullable|string|max:50',
            'telephone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $customer->update($request->all());

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * POS එකේදී හෝ Edit Sale වලදී AJAX හරහා පාරිභෝගිකයන් සෙවීමට (Autocomplete)
     */
    public function search(Request $request)
    {
        $search = $request->get('q');
        $customers = Customer::where('name', 'LIKE', "%$search%")
            ->orWhere('vat_no', 'LIKE', "%$search%")
            ->select('id', 'name', 'vat_no')
            ->limit(10)
            ->get();

        return response()->json($customers);
    }
}

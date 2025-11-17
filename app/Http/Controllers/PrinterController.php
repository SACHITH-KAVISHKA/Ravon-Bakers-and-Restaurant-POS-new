<?php

namespace App\Http\Controllers;

use App\Models\Printer;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrinterController extends Controller
{

      public function signQzRequest(Request $request)
    {
        $requestData = $request->input('data');
        // storage/app/keys/private-key.pem
        $absolutePath = storage_path('app/keys/private-key.pem');

        // 1. File existence check using 'file_exists'
        if (!file_exists($absolutePath)) {
            // 'file_exists'
            return response()->json([
                'error' => 'PHP file_exists() check failed. Key not found.',
                'checked_path' => $absolutePath
            ], 500);
        }

        // 2. File readability check using 'is_readable'
        $privateKey = file_get_contents($absolutePath);

        // Check if file_get_contents returned false
        if ($privateKey === false) {
            return response()->json([
                'error' => 'PHP file_get_contents() failed. Key found but UNREADABLE.',
                'checked_path' => $absolutePath
            ], 500);
        }

        $signature = null;

        // 3. Attempt to sign the data
        if (!openssl_sign($requestData, $signature, $privateKey, 'sha1')) {
             return response()->json(['error' => 'Failed to sign data. Check OpenSSL.'], 500);
        }

        if ($signature) {
            return response()->json(['signature' => base64_encode($signature)]);
        }

        return response()->json(['error' => 'Signing failed'], 500);
    }



    /**
     * Display a listing of printers
     */
    public function index()
    {
        $printers = Printer::with('branch')->orderBy('printer_type')->orderBy('name')->get();
        $branches = Branch::where('status', 1)->get();

        return view('printers.index', compact('printers', 'branches'));
    }

    /**
     * Show the form for creating a new printer
     */
    public function create()
    {
        $branches = Branch::where('status', 1)->get();
        return view('printers.create', compact('branches'));
    }

    /**
     * Store a newly created printer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'printer_type' => 'required|in:kot,bot,pos',
            'connection_type' => 'required|in:network,usb,windows,bluetooth',
            'connector' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'timeout' => 'nullable|integer|min:1|max:30',
            'notes' => 'nullable|string',
        ]);

        // If setting as default, unset other defaults for this type
        if ($request->is_default) {
            Printer::where('printer_type', $request->printer_type)
                ->where('branch_id', $request->branch_id)
                ->update(['is_default' => false]);
        }

        $printer = Printer::create($validated);

        return redirect()->route('printers.index')
            ->with('success', 'Printer added successfully!');
    }

    /**
     * Show the form for editing a printer
     */
    public function edit(Printer $printer)
    {
        $branches = Branch::where('status', 1)->get();
        return view('printers.edit', compact('printer', 'branches'));
    }

    /**
     * Update the specified printer
     */
    public function update(Request $request, Printer $printer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'printer_type' => 'required|in:kot,bot,pos',
            'connection_type' => 'required|in:network,usb,windows,bluetooth',
            'connector' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'timeout' => 'nullable|integer|min:1|max:30',
            'notes' => 'nullable|string',
        ]);

        // If setting as default, unset other defaults for this type
        if ($request->is_default) {
            Printer::where('printer_type', $request->printer_type)
                ->where('branch_id', $request->branch_id)
                ->where('id', '!=', $printer->id)
                ->update(['is_default' => false]);
        }

        $printer->update($validated);

        return redirect()->route('printers.index')
            ->with('success', 'Printer updated successfully!');
    }

    /**
     * Remove the specified printer
     */
    public function destroy(Printer $printer)
    {
        $printer->delete();

        return redirect()->route('printers.index')
            ->with('success', 'Printer deleted successfully!');
    }

    /**
     * Test printer connection
     */
    public function test(Printer $printer)
    {
        try {
            $connector = $printer->getConnector();
            $escposPrinter = new \Mike42\Escpos\Printer($connector);

            // Print test page
            $escposPrinter->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
            $escposPrinter->setEmphasis(true);
            $escposPrinter->text("TEST PRINT\n");
            $escposPrinter->setEmphasis(false);
            $escposPrinter->text(str_repeat("=", 32) . "\n\n");

            $escposPrinter->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);
            $escposPrinter->text("Printer: {$printer->name}\n");
            $escposPrinter->text("Type: " . strtoupper($printer->printer_type) . "\n");
            $escposPrinter->text("Connection: {$printer->connection_type}\n");
            $escposPrinter->text("Connector: {$printer->connector}\n");
            if ($printer->branch) {
                $escposPrinter->text("Branch: {$printer->branch->name}\n");
            }
            $escposPrinter->text("\n");
            $escposPrinter->text("Date: " . now()->format('Y-m-d H:i:s') . "\n");
            $escposPrinter->text(str_repeat("=", 32) . "\n");
            $escposPrinter->text("Status: SUCCESS\n");

            $escposPrinter->cut();
            $escposPrinter->close();

            Log::info("Printer test successful: {$printer->name}");

            return response()->json([
                'success' => true,
                'message' => 'Test print sent successfully! Check your printer.'
            ]);

        } catch (\Exception $e) {
            Log::error("Printer test failed: {$printer->name} - " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Printer test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle printer active status
     */
    public function toggleActive(Printer $printer)
    {
        $printer->is_active = !$printer->is_active;
        $printer->save();

        return response()->json([
            'success' => true,
            'is_active' => $printer->is_active,
            'message' => 'Printer status updated successfully!'
        ]);
    }
}

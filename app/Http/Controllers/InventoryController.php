<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel; // Requires lib
// Using native PHP CSV parsing for MVP if lib not installed, but let's assume service handles it.

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::where('branch_id', Auth::user()->branch_id)->simplePaginate(50);
        return view('inventory.index', compact('products'));
    }

    public function importExcel(Request $request, StockImportService $importer)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt',
        ]);

        // For MVP without installing heavy "maatwebsite/excel", we can use a simple CSV parser
        // or assume the user installs the package. Guide mentions "maatwebsite".
        // Here we'll wrap it in a try-catch assuming the Service uses the package logic 
        // OR we implement a simple CSV fallback in the controller for robustness.
        
        // Let's rely on the service we built (it expected a Collection).
        // To bridge the gap without the lib, let's parse CSV manually here if it's a CSV.
        
        $path = $request->file('file')->getRealPath();
        $data = array_map('str_getcsv', file($path));
        $header = array_shift($data); // Assume first row is header
        
        // Convert to Collection of arrays keyed by header
        $collection = collect($data)->map(function ($row) use ($header) {
            // Simple mapping to associate keys
            // This is fragile if CSV columns usually have specific names.
            // We'll rely on index 0=barcode, 1=name, 2=price, 3=stock
            return [
                'barcode' => $row[0] ?? null,
                'name'    => $row[1] ?? null,
                'price'   => $row[2] ?? 0,
                'stock'   => $row[3] ?? 0,
            ];
        });

        $result = $importer->import(Auth::user()->branch_id, $collection);

        return back()->with('success', "Importación completada. Creados: {$result['created']}, Actualizados: {$result['updated']}");
    }
}

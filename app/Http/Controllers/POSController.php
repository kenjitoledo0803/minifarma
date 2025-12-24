<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{
    public function index()
    {
        // Load products for the current user's branch
        $products = Product::where('branch_id', Auth::user()->branch_id)->get();
        return view('pos.index', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array',
            'cart.*.id' => 'required', // Product ID
            'cart.*.qty' => 'required|integer|min:1',
            'payment_method' => 'required|string',
        ]);

        return DB::transaction(function () use ($request) {
            $user = Auth::user();
            
            // 1. Create Sale Header
            $sale = Sale::create([
                'branch_id' => $user->branch_id,
                'user_id' => $user->id,
                'total' => 0, // Calculate below
                'payment_method' => $request->payment_method,
                'status' => 'completed',
            ]);

            $total = 0;

            // 2. Process Items
            foreach ($request->cart as $itemData) {
                // Find product (ensure it belongs to branch)
                $product = Product::where('id', $itemData['id'])
                            ->where('branch_id', $user->branch_id)
                            ->first();

                // If manual concept (ID is string generic or similar), handle differently. 
                // For MVP, assuming all are mapped products or handled via generic ID.
                
                if ($product) {
                    // Check Stock
                    if ($product->stock_quantity < $itemData['qty']) {
                        // In strict mode throw error, in MVP allow negative? User said "descontándolos".
                        // We will allow negative for speed, but ideally warn.
                    }

                    $product->decrement('stock_quantity', $itemData['qty']);
                    
                    $subtotal = $product->price * $itemData['qty'];
                    $name = $product->name;
                    $price = $product->price;
                    $prodId = $product->id;
                } else {
                    // Manual Item Logic (if passed as separate array, or mixed)
                    // Simplified for this controller:
                    continue; 
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $prodId,
                    'product_name_snapshot' => $name,
                    'quantity' => $itemData['qty'],
                    'price_at_moment' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return response()->json([
                'status' => 'success',
                'sale_id' => $sale->id,
                'print_url' => route('pos.print', $sale),
            ]);
        });
    }

    public function printTicket(Sale $sale, TicketService $ticketService)
    {
        // Security: Ensure sale belongs to user's branch
        if ($sale->branch_id !== Auth::user()->branch_id) {
            abort(403);
        }

        return $ticketService->renderTicket($sale);
    }
}

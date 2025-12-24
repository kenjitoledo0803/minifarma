<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StockImportService
{
    /**
     * Process bulk import of products for a specific branch.
     * Expects a Collection of rows (e.g., from Maatwebsite\Excel).
     * 
     * Isolation Rule: Products are identified by 'barcode' UNIQUE TO THIS BRANCH.
     * 
     * @param int $branchId
     * @param Collection $rows
     * @return array Summary of operations
     */
    public function import(int $branchId, Collection $rows): array
    {
        $created = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                // 1. Sanitize keys (lowercase, trim)
                // Assuming keys are: barcode, name, price, stock, cost
                
                // 2. Validate Row
                $validator = Validator::make($row->toArray(), [
                    'barcode' => 'required|string|max:50',
                    'name'    => 'required|string|max:255',
                    'price'   => 'required|numeric|min:0',
                    'stock'   => 'integer|min:0', // Optional, defaults to 0 if null
                    'cost'    => 'numeric|min:0', // Optional
                ]);

                if ($validator->fails()) {
                    $errors[] = "Fila " . ($index + 1) . ": " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // 3. Update or Create (Isolated by Branch)
                $product = Product::updateOrCreate(
                    [
                        'branch_id' => $branchId,  // SCOPED TO BRANCH
                        'barcode'   => (string)$row['barcode']
                    ],
                    [
                        'name'           => $row['name'],
                        'price'          => $row['price'],
                        'cost'           => $row['cost'] ?? 0,
                        'stock_quantity' => $row['stock'] ?? 0, // In this logic, we SET the stock.
                                                                // If requirement was "ADD" to stock, we'd use fresh instance logic.
                                                                // For "Carga Masiva" (Setup), SET is standard.
                        'is_manual_concept' => false,
                    ]
                );

                if ($product->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            DB::commit();

            return [
                'status' => 'success',
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors // Non-fatal row errors
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Exports\StockMovementExport;
use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StockMovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockMovement::with('product');

        // Filter berdasarkan product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter berdasarkan type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search berdasarkan field yang tersedia
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting berdasarkan field yang valid
        $allowedSortFields = [
            'product_id',
            'type',
            'quantity',
            'previous_stock',
            'current_stock',
            'reference',
            'created_at',
            'updated_at',
        ];
        $sortBy = $request->get('sort_by', 'created_at');
        $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'created_at';

        $sortOrder = $request->get('sort_order', 'asc');
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';

        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $perPage = min($perPage, 100); // Batasi maksimal 100 per halaman

        $stockMovements = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $stockMovements,
            'meta' => [
                'current_page' => $stockMovements->currentPage(),
                'last_page' => $stockMovements->lastPage(),
                'per_page' => $stockMovements->perPage(),
                'total' => $stockMovements->total(),
            ],
        ]);
    }

    public function generatePdf()
    {
        $stockMove = StockMovement::with('product')
            ->latest()
            ->get();

        $pdf = Pdf::loadView('exports.stock-movement', [
            'stockMove' => $stockMove,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('stock-movement-'.now()->format('Y-m-d').'.pdf');
    }

    public function generateExcel()
    {
        return Excel::download(new StockMovementExport, 'stock-movement-'.now()->format('Y-m-d').'.xlsx');
    }
}

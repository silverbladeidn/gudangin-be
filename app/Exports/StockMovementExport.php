<?php

namespace App\Exports;

use App\Models\StockMovement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockMovementExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function collection()
    {
        return StockMovement::with('product')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Produk',
            'Tipe',
            'Kuantitas',
            'Stok Awal',
            'Stok Saat Ini',
            'Referensi',
            'Catatan',
            'Tanggal',
        ];
    }

    public function map($stockMovement): array
    {
        return [
            $stockMovement->id,
            $stockMovement->product->name ?? 'Produk tidak ditemukan',
            $stockMovement->type,
            $stockMovement->quantity,
            $stockMovement->previous_stock,
            $stockMovement->current_stock,
            $stockMovement->reference,
            $stockMovement->notes,
            $stockMovement->created_at->format('Y-m-d H:i:s'),
        ];
    }
}

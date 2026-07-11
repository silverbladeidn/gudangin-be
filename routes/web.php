<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Models\ItemRequest;
use Barryvdh\DomPDF\Facade\Pdf;

// === Web Routes ===
// These routes are for web-based interactions, such as login via web form.
// They can also include routes for testing purposes.   

Route::post('/web-login', [AuthController::class, 'login']);
Route::get('/api/test-cors', function () {
    return response()->json(['message' => 'CORS OK']);
});
Route::get('/test-pdf/{id}', function ($id) {
    $itemRequest = ItemRequest::with('user', 'details.product')->find($id);
    $pdf = Pdf::loadView('pdf.item_request', ['itemRequest' => $itemRequest]);
    return $pdf->download('test.pdf');
});

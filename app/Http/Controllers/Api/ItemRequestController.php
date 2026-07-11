<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ItemRequest;
use App\Models\ItemRequestDetail;
use App\Models\ItemRequestLog;
use App\Mail\ItemRequestCreated;
use App\Mail\ItemRequestApproved;
use App\Mail\ItemRequestPartialApproved;
use App\Mail\ItemRequestRejected;
use App\Models\Product;
use App\Models\EmailSettings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ItemRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ItemRequest::with(['user', 'details.product.category', 'approvedBy'])
                ->orderBy('created_at', 'desc');

            /** @var User $user */
            $user = Auth::user();

            // Filter untuk user biasa saja
            if ($user->hasRole('user')) {
                $query->byUser($user->id);
            }


            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Search by request number or user name
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('request_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $requests = $query->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $requests,
                'message' => 'Requests retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approvalList(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Hanya admin / superadmin yang boleh akses
            if (!$user->hasRole('Admin') && !$user->hasRole('Superadmin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $query = ItemRequest::with(['user', 'details.product.category', 'approvedBy'])
                ->orderBy('created_at', 'desc');

            // Filter hanya yang masih pending
            $query->where('status', 'pending');

            // Optional: bisa filter tambahan
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('request_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $requests = $query->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $requests,
                'message' => 'Approval requests retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve approval requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // --- Validasi dasar ---
        $validator = Validator::make($request->all(), [
            'note'    => 'nullable|string',
            'action'  => 'nullable|in:draft,submit',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.qty'        => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $action = $request->input('action', 'submit'); // default ke submit
        DB::beginTransaction();

        try {
            // Jika submit, cek stok dulu
            if ($action === 'submit') {
                foreach ($request->details as $detail) {
                    $product = Product::findOrFail($detail['product_id']);
                    if ($product->stock_quantity < $detail['qty']) {
                        return response()->json([
                            'success' => false,
                            'message' => "Stok tidak mencukupi untuk {$product->name}. " .
                                "Stok: {$product->stock_quantity}, diminta: {$detail['qty']}"
                        ], 422);
                    }
                }
            }

            // === Buat record utama ===
            $itemRequest = ItemRequest::create([
                'user_id'        => $request->user()->id,
                'note'           => $request->note,
                'request_number' => ItemRequest::generateRequestNumber(),
                'status'         => $action === 'submit' ? 'pending' : 'draft',
                'approved_by'    => null,
                'approved_at'    => null,
            ]);

            // === Detail item ===
            foreach ($request->details as $detail) {
                $itemRequest->details()->create([
                    'product_id'         => $detail['product_id'],
                    'requested_quantity' => $detail['qty'],
                    'approved_quantity'  => 0,
                    'status'             => $action === 'submit' ? 'pending' : 'draft',
                ]);

                // Kurangi stok jika submit
                if ($action === 'submit') {
                    $product = Product::findOrFail($detail['product_id']);
                    $oldStock = $product->stock_quantity;
                    $product->decrement('stock_quantity', $detail['qty']);

                    Log::info("Stock reduced for product: {$product->name}", [
                        'product_id' => $product->id,
                        'old_stock' => $oldStock,
                        'new_stock' => $product->stock_quantity,
                        'quantity_used' => $detail['qty'],
                        'request_number' => $itemRequest->request_number,
                        'user_id' => $request->user()->id
                    ]);
                }
            }

            // Catat log
            ItemRequestLog::create([
                'item_request_id' => $itemRequest->id,
                'user_id'         => $request->user()->id,
                'action'          => $action === 'submit'
                    ? 'created_pending'
                    : 'created_draft',
                'old_data'        => null,
                'new_data'        => $itemRequest->toArray(),
                'description'     => $action === 'submit'
                    ? "Request created and waiting for approval"
                    : "Request saved as draft",
            ]);

            DB::commit();

            // === Kirim email hanya jika submit ===
            if ($action === 'submit') {
                try {
                    $emailSettings = EmailSettings::getSettings();
                    if ($emailSettings && $emailSettings->request_notifications) {
                        $itemRequest->load('user', 'details.product');

                        $mail = Mail::to($emailSettings->admin_email);

                        if (!empty($emailSettings->cc_emails)) {
                            $mail->cc($emailSettings->cc_emails);
                        }

                        // GUNAKAN KEMBALI MAILABLE CLASS DENGAN PDF
                        $mail->send(new ItemRequestCreated($itemRequest));

                        Log::info("Email dengan PDF attachment dikirim ke {$emailSettings->admin_email}");
                    }
                } catch (\Exception $mailErr) {
                    Log::error('Gagal kirim email: ' . $mailErr->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'data'    => $itemRequest->load('details.product'),
                'message' => $action === 'submit'
                    ? 'Request berhasil dibuat, stok dikurangi, dan notifikasi terkirim'
                    : 'Draft berhasil disimpan',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage (for draft requests)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'note'    => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.qty'        => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        /** @var User $user */
        $user = Auth::user();

        DB::beginTransaction();

        try {
            // Cari item request yang masih draft dan milik user
            $itemRequest = ItemRequest::where('id', $id)
                ->where('user_id', $user->id)
                ->where('status', 'draft')
                ->firstOrFail();

            // Simpan data lama untuk log
            $oldData = $itemRequest->toArray();
            $oldDetails = $itemRequest->details->toArray();

            // Update header request
            $itemRequest->update([
                'note' => $request->note
            ]);

            // Hapus detail lama
            $itemRequest->details()->delete();

            // Buat detail baru
            foreach ($request->details as $detail) {
                ItemRequestDetail::create([
                    'item_request_id'    => $itemRequest->id,
                    'product_id'         => $detail['product_id'],
                    'requested_quantity' => $detail['qty'],
                    'approved_quantity'  => 0,
                    'status'             => 'draft',
                ]);
            }

            // Catat log
            ItemRequestLog::create([
                'item_request_id' => $itemRequest->id,
                'user_id'         => $user->id,
                'action'          => 'updated_draft',
                'old_data'        => [
                    'header' => $oldData,
                    'details' => $oldDetails
                ],
                'new_data'        => [
                    'header' => $itemRequest->toArray(),
                    'details' => $itemRequest->details->toArray()
                ],
                'description'     => "Draft request updated",
            ]);

            DB::commit();

            // Reload relationships
            $itemRequest->load('details.product');

            return response()->json([
                'success' => true,
                'data'    => $itemRequest,
                'message' => 'Draft request updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating draft request: ' . $e->getMessage(), [
                'request_id' => $id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update draft request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $request = ItemRequest::with([
                'user',
                'details.product.category',
                'approvedBy',
                'logs.user'
            ])->findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // ✅ PERBAIKI: Gunakan case yang sama dengan seeder
            if (!$user->hasRole('Admin') && $request->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this request'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $request,
                'message' => 'Request retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource (untuk admin jika ingin mengubah status)
     */
    public function submit(Request $request, $id)
    {
        $authUser = Auth::user();

        DB::beginTransaction();
        try {
            // Pastikan request milik user login dan status draft
            $itemRequest = ItemRequest::where('id', $id)
                ->where('user_id', $authUser->id)
                ->where('status', 'draft') // cuma draft yang bisa disubmit
                ->firstOrFail();

            // Validasi stok sebelum submit
            foreach ($itemRequest->details as $detail) {
                $product = Product::findOrFail($detail->product_id);
                if ($product->stock_quantity < $detail->requested_quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok tidak mencukupi untuk {$product->name}. " .
                            "Stok: {$product->stock_quantity}, diminta: {$detail->requested_quantity}"
                    ], 422);
                }
            }

            // Update status ke pending
            $itemRequest->update([
                'status' => 'pending'
            ]);

            // Update status details ke pending
            $itemRequest->details()->update(['status' => 'pending']);

            // Kurangi stok
            foreach ($itemRequest->details as $detail) {
                $product = Product::findOrFail($detail->product_id);
                $oldStock = $product->stock_quantity;
                $product->decrement('stock_quantity', $detail->requested_quantity);

                Log::info("Stock reduced for product: {$product->name}", [
                    'product_id' => $product->id,
                    'old_stock' => $oldStock,
                    'new_stock' => $product->stock_quantity,
                    'quantity_used' => $detail->requested_quantity,
                    'request_number' => $itemRequest->request_number,
                    'user_id' => $authUser->id
                ]);
            }

            // Catat log
            ItemRequestLog::create([
                'item_request_id' => $itemRequest->id,
                'user_id'         => $authUser->id,
                'action'          => 'submitted',
                'old_data'        => ['status' => 'draft'],
                'new_data'        => ['status' => 'pending'],
                'description'     => "Draft submitted for approval",
            ]);

            // Mereload data dengan relationship sebelum kirim email
            $itemRequest->refresh();
            $itemRequest->load(['details.product', 'user']);

            // === Kirim email ===
            try {
                $emailSettings = EmailSettings::getSettings(); // samain sama store()
                if ($emailSettings && $emailSettings->request_notifications) {
                    $itemRequest->load('user', 'details.product');

                    $mail = Mail::to($emailSettings->admin_email);

                    if (!empty($emailSettings->cc_emails)) {
                        $mail->cc($emailSettings->cc_emails);
                    }

                    // pakai Mailable yang sama
                    $mail->send(new ItemRequestCreated($itemRequest));

                    Log::info("Email dengan PDF attachment dikirim ke {$emailSettings->admin_email}", [
                        'request_number' => $itemRequest->request_number,
                        'recipient' => $emailSettings->admin_email
                    ]);
                } else {
                    Log::warning('Email tidak dikirim karena setting tidak aktif', [
                        'has_email_settings' => !is_null($emailSettings),
                        'notifications_enabled' => $emailSettings ? $emailSettings->request_notifications : false
                    ]);
                }
            } catch (\Exception $mailErr) {
                Log::error('Gagal kirim email submit: ' . $mailErr->getMessage(), [
                    'request_id' => $itemRequest->id,
                    'trace' => $mailErr->getTraceAsString()
                ]);
            }


            DB::commit();

            return response()->json([
                'success' => true,
                'data'    => $itemRequest,
                'message' => 'Request berhasil dikirim dan stok dikurangi',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting draft request: ' . $e->getMessage(), [
                'request_id' => $id,
                'user_id' => $authUser->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function partialApprove(Request $request, $id): JsonResponse
    {
        try {
            /** @var User|null $user */
            $user = Auth::user();

            if (!$user || !$user->hasRole('Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $itemRequest = ItemRequest::findOrFail($id);

            if ($itemRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Request already processed'
                ], 400);
            }

            $oldStatus = $itemRequest->status;
            $newStatus = 'partially_approved';

            $itemRequest->update([
                'status'      => $newStatus,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            ItemRequestLog::create([
                'item_request_id' => $itemRequest->id,
                'user_id'         => $user->id,
                'action'          => 'partial_approved',
                'old_data'        => ['status' => $oldStatus],
                'new_data'        => ['status' => $newStatus],
                'description'     => "Request {$oldStatus} → {$newStatus}",
            ]);

            $itemRequest->refresh();
            $itemRequest->load(['details.product', 'user']);

            try {
                Mail::to($itemRequest->user->email)
                    ->send(new ItemRequestPartialApproved($itemRequest, $newStatus));
            } catch (\Exception $mailErr) {
                Log::error('Gagal kirim email partial approval: ' . $mailErr->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Request {$newStatus} successfully",
                'data'    => $itemRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to partial approve request',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function approve(Request $request, $id): JsonResponse
    {
        try {
            /** @var User|null $user */
            $user = Auth::user();

            if (!$user || !$user->hasRole('Superadmin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $itemRequest = ItemRequest::findOrFail($id);

            if (!in_array($itemRequest->status, ['pending', 'partially_approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request already processed'
                ], 400);
            }

            $oldStatus = $itemRequest->status;
            $newStatus = 'approved';

            $itemRequest->update([
                'status'      => $newStatus,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            ItemRequestLog::create([
                'item_request_id' => $itemRequest->id,
                'user_id'         => $user->id,
                'action'          => 'approved',
                'old_data'        => ['status' => $oldStatus],
                'new_data'        => ['status' => $newStatus],
                'description'     => "Request {$oldStatus} → {$newStatus}",
            ]);

            $itemRequest->refresh();
            $itemRequest->load(['details.product', 'user']);

            try {
                Mail::to($itemRequest->user->email)
                    ->send(new ItemRequestApproved($itemRequest, $newStatus));
            } catch (\Exception $mailErr) {
                Log::error('Gagal kirim email approval: ' . $mailErr->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Request {$newStatus} successfully",
                'data'    => $itemRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request, $id): JsonResponse
    {
        DB::beginTransaction(); // TAMBAH INI

        try {
            /** @var User|null $user */
            $user = Auth::user();

            // Pastikan user ada dan punya role Admin / Superadmin
            if (!$user || !$user->hasRole(['Admin', 'Superadmin'])) {
                DB::rollBack(); // TAMBAH INI
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $itemRequest = ItemRequest::findOrFail($id);

            // Cegah reject request yang sudah diproses
            if ($itemRequest->status !== 'pending') {
                DB::rollBack(); // TAMBAH INI
                return response()->json([
                    'success' => false,
                    'message' => 'Request already processed'
                ], 400);
            }

            $oldStatus = $itemRequest->status;
            $newStatus = 'rejected';

            // Update data
            $itemRequest->update([
                'status'      => $newStatus,
                'approved_by' => $user->id,   // reuse kolom
                'approved_at' => now(),
                'admin_note'  => $request->input('note') ?? null,
            ]);

            // Log perubahan
            ItemRequestLog::create([
                'item_request_id' => $itemRequest->id,
                'user_id'         => $user->id,
                'action'          => 'rejected',
                'old_data'        => ['status' => $oldStatus],
                'new_data'        => ['status' => $newStatus],
                'description'     => "Request {$oldStatus} → {$newStatus}",
            ]);

            // Reload data untuk email
            $itemRequest->refresh();
            $itemRequest->load(['details.product', 'user']);

            // Kirim email
            try {
                Mail::to($itemRequest->user->email)
                    ->send(new ItemRequestRejected($itemRequest, $newStatus, $request->input('note')));

                Log::info("Email rejection dikirim ke {$itemRequest->user->email}", [
                    'request_number' => $itemRequest->request_number,
                    'status'         => $newStatus,
                ]);
            } catch (\Exception $mailErr) {
                Log::error('Gagal kirim email rejection: ' . $mailErr->getMessage(), [
                    'request_id' => $itemRequest->id,
                ]);
                // Jangan rollback hanya karena email gagal
            }

            DB::commit(); // PASTIKAN INI SEBELUM RETURN

            return response()->json([
                'success' => true,
                'message' => 'Request rejected successfully',
                'data'    => $itemRequest
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // TAMBAH INI DI CATCH

            Log::error('Reject request error: ' . $e->getMessage(), [
                'request_id' => $id,
                'user_id' => $user->id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject request',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $itemRequest = ItemRequest::with('details.product')->findOrFail($id);

            /** @var User $user */
            $user = Auth::user();

            // ✅ PERBAIKI: Gunakan case yang sama dengan seeder
            if (!$user->hasRole('Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only admin can delete requests'
                ], 403);
            }

            DB::beginTransaction();

            // Jika request masih approved, kembalikan stok dulu
            if ($itemRequest->status === 'approved') {
                foreach ($itemRequest->details as $detail) {
                    if ($detail->status === 'approved') {
                        $product = $detail->product;
                        $product->increment('stock_quantity', $detail->approved_quantity);
                    }
                }
            }

            $itemRequest->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a request (for users)
     */
    public function cancel(ItemRequest $itemRequest)
    {
        if ($itemRequest->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak dapat membatalkan request.'
            ], 403);
        }

        // Jalankan transaksi supaya stok dan log konsisten
        DB::transaction(function () use ($itemRequest) {
            // Kembalikan stok tiap produk
            foreach ($itemRequest->details as $detail) {
                // pastikan relasi product sudah ada
                if ($detail->product) {
                    $detail->product->increment('stock_quantity', $detail->requested_quantity);
                }
                $detail->update(['status' => 'cancelled']);
            }

            // Update status di header request
            $oldData = $itemRequest->toArray();
            $itemRequest->update(['status' => 'cancelled']);

            // Simpan log
            $itemRequest->logs()->create([
                'user_id'    => Auth::id(),
                'action'     => 'cancelled',
                'old_data'   => $oldData,
                'new_data'   => $itemRequest->toArray(),
                'description' => 'Request dibatalkan oleh user'
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Request berhasil dibatalkan dan stok dikembalikan!'
        ]);
    }

    /**
     * Get request statistics
     */
    public function stats(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            $userId = $user->id;

            // ✅ PERBAIKI: Gunakan case yang sama dengan seeder
            $isAdmin = $user->hasRole('Admin');

            if ($isAdmin) {
                $stats = [
                    'total_requests' => ItemRequest::count(),
                    'approved_requests' => ItemRequest::where('status', 'approved')->count(),
                    'cancelled_requests' => ItemRequest::where('status', 'cancelled')->count(),
                    'total_items_requested' => ItemRequestDetail::sum('requested_quantity'),
                    'total_items_approved' => ItemRequestDetail::sum('approved_quantity')
                ];
            } else {
                $stats = [
                    'my_total_requests' => ItemRequest::byUser($userId)->count(),
                    'my_approved_requests' => ItemRequest::byUser($userId)->where('status', 'approved')->count(),
                    'my_cancelled_requests' => ItemRequest::byUser($userId)->where('status', 'cancelled')->count()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

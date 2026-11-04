<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\MidtransService;

class MidtransWebhookController extends Controller
{
    protected MidtransService $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Endpoint penerima callback webhook notifikasi transaksi Midtrans.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        $result = $this->midtransService->processNotification($payload);

        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'] ?? 'Gagal memproses webhook.',
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notifikasi Midtrans berhasil diproses.',
            'data' => $result,
        ], 200);
    }
}


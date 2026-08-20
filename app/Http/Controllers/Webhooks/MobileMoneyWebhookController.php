<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\MobileMoneyPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileMoneyWebhookController extends Controller
{
    public function __invoke(Request $request, MobileMoneyPaymentService $payments): JsonResponse
    {
        $payload = $request->validate([
            'reference' => ['required', 'string'],
            'external_id' => ['required', 'string'],
            'status' => ['required', 'in:succeeded,failed,expired'],
            'amount' => ['nullable', 'numeric'],
        ]);

        $payment = $payments->findByReference($payload['reference']);

        if (! $payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        if (isset($payload['amount']) && (float) $payload['amount'] !== (float) $payment->amount) {
            return response()->json(['message' => 'Amount mismatch.'], 422);
        }

        if ($payment->isSucceeded()) {
            return response()->json(['message' => 'Already processed.', 'reference' => $payment->reference]);
        }

        if ($payload['status'] === 'succeeded') {
            $payments->confirm($payment, $payload['external_id'], $payload);
        } else {
            $payments->fail($payment, $payload['status']);
        }

        return response()->json([
            'message' => 'Webhook processed.',
            'reference' => $payment->fresh()->reference,
            'status' => $payment->fresh()->status,
        ]);
    }
}

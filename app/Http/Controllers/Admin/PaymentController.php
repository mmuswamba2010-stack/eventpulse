<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\MobileMoneyPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $payments = Payment::query()
            ->with(['user', 'payable'])
            ->when($status !== '' && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'status' => $status === '' ? 'pending' : $status,
            'pendingCount' => Payment::query()->where('status', Payment::STATUS_PENDING)->count(),
        ]);
    }

    public function confirm(Request $request, Payment $payment, MobileMoneyPaymentService $payments): RedirectResponse
    {
        if (! $payment->isPending()) {
            return back()->with('admin_error', __('Payment already processed'));
        }

        $validated = $request->validate([
            'external_id' => ['nullable', 'string', 'max:100'],
        ]);

        $externalId = filled($validated['external_id'] ?? null)
            ? $validated['external_id']
            : 'admin-'.auth()->id().'-'.now()->timestamp;

        $payments->confirm($payment, $externalId, [
            'source' => 'admin_manual',
            'confirmed_by' => auth()->id(),
        ]);

        return back()->with('admin_success', __('Payment confirmed admin', ['reference' => $payment->reference]));
    }

    public function fail(Payment $payment, MobileMoneyPaymentService $payments): RedirectResponse
    {
        if (! $payment->isPending()) {
            return back()->with('admin_error', __('Payment already processed'));
        }

        $payments->fail($payment, 'admin_rejected');

        return back()->with('admin_success', __('Payment rejected admin', ['reference' => $payment->reference]));
    }
}

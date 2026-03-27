<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function show($invoiceNumber)
    {
        $invoice = Invoice::where('invoice_number', $invoiceNumber)->firstOrFail();
        $invoice->checkExpiry();
        
        $paystackPublicKey = Setting::get('paystack_public_key');

        return view('front.invoice.show', compact('invoice', 'paystackPublicKey'));
    }

    public function initiatePayment(Request $request, $invoiceNumber)
    {
        $invoice = Invoice::where('invoice_number', $invoiceNumber)->firstOrFail();
        
        if ($invoice->isExpired()) {
            return response()->json(['error' => 'Invoice has expired'], 400);
        }

        if ($invoice->isPaid()) {
            return response()->json(['error' => 'Invoice already paid'], 400);
        }

        $secretKey = Setting::get('paystack_secret_key');

        if (empty($secretKey)) {
            return response()->json(['error' => 'Payment not configured'], 500);
        }

        $url = 'https://api.paystack.co/transaction/initialize';

        $data = [
            'amount' => (int) ($invoice->getBalanceDue() * 100),
            'email' => $invoice->client_email,
            'currency' => 'NGN',
            'reference' => 'INV-' . $invoice->invoice_number . '-' . time(),
            'callback_url' => route('invoices.callback', $invoice->invoice_number),
            'metadata' => [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'custom_fields' => [
                    [
                        'display_name' => 'Invoice Number',
                        'variable_name' => 'invoice_number',
                        'value' => $invoice->invoice_number
                    ]
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $secretKey,
            'Content-Type' => 'application/json',
        ])->withOptions(['verify' => false])->post($url, $data);

        $result = $response->json();

        if (empty($result) || !isset($result['status']) || !$result['status']) {
            return response()->json(['error' => $result['message'] ?? 'Payment initialization failed'], 400);
        }

        $invoice->update([
            'payment_reference' => $result['data']['reference']
        ]);

        return response()->json([
            'authorization_url' => $result['data']['authorization_url'],
            'reference' => $result['data']['reference'],
        ]);
    }

    public function callback(Request $request, $invoiceNumber)
    {
        $invoice = Invoice::where('invoice_number', $invoiceNumber)->firstOrFail();
        
        $reference = $request->reference ?: $invoice->payment_reference;

        if (!$reference) {
            return view('front.invoice.error', [
                'message' => 'Invalid payment reference'
            ]);
        }

        $secretKey = Setting::get('paystack_secret_key');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $secretKey,
        ])->withOptions(['verify' => false])->get('https://api.paystack.co/transaction/verify/' . $reference);

        $result = $response->json();

        if ($result['status'] && $result['data']['status'] === 'success') {
            $amountPaid = $result['data']['amount'] / 100;
            
            $invoice->markAsPaid($amountPaid);
            $invoice->update([
                'payment_reference' => $reference
            ]);

            $this->sendPaymentConfirmation($invoice);

            return view('front.invoice.success', compact('invoice'));
        }

        return view('front.invoice.error', [
            'message' => 'Payment verification failed'
        ]);
    }

    protected function sendPaymentConfirmation($invoice)
    {
        try {
            $fromAddress = Setting::get('mail_from_address', 'support@joala.com.ng');
            $fromName = Setting::get('mail_from_name', 'JoAla Support');

            \Illuminate\Support\Facades\Config::set('mail.from.address', $fromAddress);
            \Illuminate\Support\Facades\Config::set('mail.from.name', $fromName);

            \Illuminate\Support\Facades\Mail::send('emails.invoice_paid', [
                'invoice' => $invoice
            ], function ($message) use ($invoice) {
                $message->to($invoice->client_email, $invoice->client_name)
                        ->subject('Payment Received - Invoice #' . $invoice->invoice_number);
            });
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email: ' . $e->getMessage());
        }
    }
}

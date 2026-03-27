<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::orderBy('id', 'desc')->paginate(15);
        return view('admin.invoices.index', compact('invoices'));
    }

    public function create()
    {
        return view('admin.invoices.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email',
            'client_phone' => 'nullable|string',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
        ]);

        $invoice = Invoice::create([
            'client_name' => $request->client_name,
            'client_email' => $request->client_email,
            'client_phone' => $request->client_phone,
            'amount' => $request->amount,
            'description' => $request->description,
            'expires_at' => now()->addHours(24),
        ]);

        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->checkExpiry();
        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        return view('admin.invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email',
            'client_phone' => 'nullable|string',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
        ]);

        $invoice->update($request->only([
            'client_name', 'client_email', 'client_phone', 'amount', 'description'
        ]));

        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully.');
    }

    public function sendInvoice(Request $request, Invoice $invoice)
    {
        try {
            $this->sendInvoiceEmail($invoice);
            return back()->with('success', 'Invoice sent to client email!');
        } catch (\Exception $e) {
            Log::error('Failed to send invoice email: ' . $e->getMessage());
            return back()->with('error', 'Failed to send invoice email.');
        }
    }

    public function generatePaymentLink(Invoice $invoice)
    {
        try {
            $paymentUrl = $this->createPaystackPaymentLink($invoice);
            return back()->with('success', 'Payment link generated!')
                ->with('payment_url', $paymentUrl);
        } catch (\Exception $e) {
            Log::error('Failed to generate payment link: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate payment link: ' . $e->getMessage());
        }
    }

    protected function createPaystackPaymentLink(Invoice $invoice)
    {
        $secretKey = Setting::get('paystack_secret_key');

        if (empty($secretKey)) {
            throw new \Exception('Paystack secret key not configured');
        }

        $url = 'https://api.paystack.co/transaction/initialize';

        $callbackUrl = route('invoices.callback', $invoice->invoice_number);

        $data = [
            'amount' => (int) ($invoice->getBalanceDue() * 100),
            'email' => $invoice->client_email,
            'currency' => 'NGN',
            'reference' => 'INV-' . $invoice->invoice_number . '-' . time(),
            'callback_url' => $callbackUrl,
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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            curl_close($ch);
            throw new \Exception('cURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);

        $result = json_decode($response, true);

        if (empty($result)) {
            throw new \Exception('Invalid response from Paystack. Response: ' . $response);
        }

        if (!isset($result['status']) || !$result['status']) {
            throw new \Exception($result['message'] ?? 'Failed to create payment link');
        }

        if (!isset($result['data']['authorization_url'])) {
            throw new \Exception('No authorization URL in response');
        }

        return $result['data']['authorization_url'];
    }

    protected function sendInvoiceEmail($invoice)
    {
        try {
            $fromAddress = Setting::get('mail_from_address', 'support@joala.com.ng');
            $fromName = Setting::get('mail_from_name', 'JoAla Support');

            // Set full mail config for log driver
            Config::set('mail.default', 'log');
            Config::set('mail.from.address', $fromAddress);
            Config::set('mail.from.name', $fromName);
            Config::set('mail.mailers.log', ['transport' => 'log']);

            $paymentUrl = $invoice->getPaymentUrl();

            Mail::send('emails.invoice', [
                'invoice' => $invoice,
                'paymentUrl' => $paymentUrl
            ], function ($message) use ($invoice) {
                $message->to($invoice->client_email, $invoice->client_name)
                        ->subject('Invoice #' . $invoice->invoice_number . ' - Payment Required');
            });

            Log::info('Invoice email sent to: ' . $invoice->client_email);
            return true;
        } catch (\Exception $e) {
            Log::error('Invoice email failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function markAsPaid(Request $request, Invoice $invoice)
    {
        $invoice->markAsPaid($request->amount ?? null);
        
        return back()->with('success', 'Invoice marked as paid.');
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice deleted.');
    }
}

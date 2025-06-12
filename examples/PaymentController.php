<?php

namespace App\Http\Controllers;

use Asciisd\CyberSource\Exceptions\CyberSourceException;
use Asciisd\CyberSource\Facades\CyberSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Show the payment form.
     *
     * @return \Illuminate\View\View
     */
    public function showPaymentForm()
    {
        return view('payments.form');
    }

    /**
     * Process a payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'card_number' => 'required|string',
            'expiration_month' => 'required|string|size:2',
            'expiration_year' => 'required|string|size:4',
            'cvv' => 'required|string|min:3|max:4',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|size:2',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        try {
            // Generate a unique reference for this payment
            $reference = 'order-' . time();

            // Process the payment with CyberSource
            $transaction = CyberSource::charge([
                'reference' => $reference,
                'amount' => $request->amount,
                'currency' => 'USD',
                'card' => [
                    'number' => $request->card_number,
                    'expiration_month' => $request->expiration_month,
                    'expiration_year' => $request->expiration_year,
                    'security_code' => $request->cvv,
                ],
                'billing' => [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'postal_code' => $request->postal_code,
                    'country' => $request->country,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ],
            ]);

            // Check if the transaction was successful
            if ($transaction->isSuccessful()) {
                // Store the transaction details in your database if needed
                // ...

                // Redirect to success page with transaction details
                return redirect()->route('payment.success')->with([
                    'transaction_id' => $transaction->getId(),
                    'status' => $transaction->getStatus(),
                    'amount' => $transaction->getAmount(),
                    'currency' => $transaction->getCurrency(),
                ]);
            } else {
                // Payment was declined
                return redirect()->back()->withErrors([
                    'payment' => 'Payment was declined: ' . $transaction->getStatus(),
                ])->withInput();
            }
        } catch (CyberSourceException $e) {
            // Log the error
            Log::error('CyberSource payment error: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'error_data' => $e->getErrorData(),
            ]);

            // Redirect back with error message
            return redirect()->back()->withErrors([
                'payment' => 'An error occurred while processing your payment: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Show the payment success page.
     *
     * @return \Illuminate\View\View
     */
    public function showSuccessPage()
    {
        // Check if we have transaction details in the session
        if (!session()->has('transaction_id')) {
            return redirect()->route('payment.form');
        }

        return view('payments.success', [
            'transaction_id' => session('transaction_id'),
            'status' => session('status'),
            'amount' => session('amount'),
            'currency' => session('currency'),
        ]);
    }

    /**
     * Capture a previously authorized payment.
     *
     * @param  string  $transactionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function capturePayment($transactionId)
    {
        try {
            // Capture the payment
            $transaction = CyberSource::capture($transactionId, null, [
                'reference' => 'capture-' . time(),
            ]);

            // Check if the capture was successful
            if ($transaction->isSuccessful()) {
                return redirect()->back()->with('success', 'Payment captured successfully.');
            } else {
                return redirect()->back()->withErrors([
                    'capture' => 'Payment capture failed: ' . $transaction->getStatus(),
                ]);
            }
        } catch (CyberSourceException $e) {
            // Log the error
            Log::error('CyberSource capture error: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'error_data' => $e->getErrorData(),
            ]);

            // Redirect back with error message
            return redirect()->back()->withErrors([
                'capture' => 'An error occurred while capturing the payment: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Void a transaction.
     *
     * @param  string  $transactionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function voidTransaction($transactionId)
    {
        try {
            // Void the transaction
            $transaction = CyberSource::void($transactionId, [
                'reference' => 'void-' . time(),
            ]);

            // Check if the void was successful
            if ($transaction->isSuccessful()) {
                return redirect()->back()->with('success', 'Transaction voided successfully.');
            } else {
                return redirect()->back()->withErrors([
                    'void' => 'Transaction void failed: ' . $transaction->getStatus(),
                ]);
            }
        } catch (CyberSourceException $e) {
            // Log the error
            Log::error('CyberSource void error: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'error_data' => $e->getErrorData(),
            ]);

            // Redirect back with error message
            return redirect()->back()->withErrors([
                'void' => 'An error occurred while voiding the transaction: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Refund a transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $transactionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refundTransaction(Request $request, $transactionId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            // Refund the transaction
            $transaction = CyberSource::refund($transactionId, $request->amount, [
                'reference' => 'refund-' . time(),
                'currency' => 'USD',
            ]);

            // Check if the refund was successful
            if ($transaction->isSuccessful()) {
                return redirect()->back()->with('success', 'Transaction refunded successfully.');
            } else {
                return redirect()->back()->withErrors([
                    'refund' => 'Transaction refund failed: ' . $transaction->getStatus(),
                ]);
            }
        } catch (CyberSourceException $e) {
            // Log the error
            Log::error('CyberSource refund error: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'error_data' => $e->getErrorData(),
            ]);

            // Redirect back with error message
            return redirect()->back()->withErrors([
                'refund' => 'An error occurred while refunding the transaction: ' . $e->getMessage(),
            ]);
        }
    }
}

<?php

namespace Asciisd\CyberSource;

use Asciisd\CyberSource\Exceptions\CyberSourceException;
use Asciisd\CyberSource\Http\CyberSourceApiClient;
use Asciisd\CyberSource\Models\Payment;
use Asciisd\CyberSource\Models\Transaction;
use Illuminate\Support\Arr;

class CyberSource
{
    /**
     * The CyberSource API client instance.
     *
     * @var \Asciisd\CyberSource\Http\CyberSourceApiClient
     */
    protected $apiClient;

    /**
     * The CyberSource configuration options.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new CyberSource instance.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->apiClient = new CyberSourceApiClient($config);
    }

    /**
     * Create a new payment authorization.
     *
     * @param array $options
     * @return \Asciisd\CyberSource\Models\Transaction
     * @throws \Asciisd\CyberSource\Exceptions\CyberSourceException
     */
    public function authorize(array $options)
    {
        return $this->createPayment($options, false);
    }

    /**
     * Create a new payment with immediate capture.
     *
     * @param array $options
     * @return \Asciisd\CyberSource\Models\Transaction
     * @throws \Asciisd\CyberSource\Exceptions\CyberSourceException
     */
    public function charge(array $options)
    {
        return $this->createPayment($options, true);
    }

    /**
     * Create a payment with CyberSource.
     *
     * @param array $options
     * @param bool $capture Whether to capture the payment immediately
     * @return \Asciisd\CyberSource\Models\Transaction
     * @throws \Asciisd\CyberSource\Exceptions\CyberSourceException
     */
    protected function createPayment(array $options, bool $capture = false)
    {
        try {
            // Create a new payment instance
            $payment = new Payment($options);
            
            // Set capture flag
            $payment->setCapture($capture);
            
            // Process the payment
            $response = $this->apiClient->createPayment($payment);
            
            // Create and return a transaction from the response
            return new Transaction($response);
        } catch (\Exception $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Capture a previously authorized payment.
     *
     * @param string $transactionId
     * @param float|null $amount
     * @param array $options
     * @return \Asciisd\CyberSource\Models\Transaction
     * @throws \Asciisd\CyberSource\Exceptions\CyberSourceException
     */
    public function capture(string $transactionId, float $amount = null, array $options = [])
    {
        try {
            $response = $this->apiClient->capturePayment($transactionId, $amount, $options);
            
            return new Transaction($response);
        } catch (\Exception $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Void a transaction.
     *
     * @param string $transactionId
     * @param array $options
     * @return \Asciisd\CyberSource\Models\Transaction
     * @throws \Asciisd\CyberSource\Exceptions\CyberSourceException
     */
    public function void(string $transactionId, array $options = [])
    {
        try {
            $response = $this->apiClient->voidPayment($transactionId, $options);
            
            return new Transaction($response);
        } catch (\Exception $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Refund a transaction.
     *
     * @param string $transactionId
     * @param float|null $amount
     * @param array $options
     * @return \Asciisd\CyberSource\Models\Transaction
     * @throws \Asciisd\CyberSource\Exceptions\CyberSourceException
     */
    public function refund(string $transactionId, float $amount = null, array $options = [])
    {
        try {
            $response = $this->apiClient->refundPayment($transactionId, $amount, $options);
            
            return new Transaction($response);
        } catch (\Exception $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Retrieve a transaction.
     *
     * @param string $transactionId
     * @return \Asciisd\CyberSource\Models\Transaction
     * @throws \Asciisd\CyberSource\Exceptions\CyberSourceException
     */
    public function retrieveTransaction(string $transactionId)
    {
        try {
            $response = $this->apiClient->retrieveTransaction($transactionId);
            
            return new Transaction($response);
        } catch (\Exception $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

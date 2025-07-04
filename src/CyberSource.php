<?php

namespace Asciisd\CyberSource;

use Asciisd\CyberSource\Exceptions\CyberSourceException;
use Asciisd\CyberSource\Http\CyberSourceApiClient;
use Asciisd\CyberSource\Models\Payment;
use Asciisd\CyberSource\Models\Transaction;
use CyberSource\Authentication\Core\AuthException;
use Exception;

class CyberSource
{
    /**
     * The CyberSource API client instance.
     *
     * @var CyberSourceApiClient
     */
    protected CyberSourceApiClient $apiClient;

    /**
     * The CyberSource configuration options.
     *
     * @var array
     */
    protected array $config;

    /**
     * Create a new CyberSource instance.
     *
     * @param array $config
     * @throws AuthException
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
     * @return Transaction
     * @throws CyberSourceException
     */
    public function authorize(array $options): Transaction
    {
        return $this->createPayment($options);
    }

    /**
     * Create a new payment with immediate capture.
     *
     * @param array $options
     * @return Transaction
     * @throws CyberSourceException
     */
    public function charge(array $options): Transaction
    {
        return $this->createPayment($options, true);
    }

    /**
     * Create a payment with CyberSource.
     *
     * @param array $options
     * @param bool $capture Whether to capture the payment immediately
     * @return Transaction
     * @throws CyberSourceException
     */
    protected function createPayment(array $options, bool $capture = false): Transaction
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
        } catch (Exception $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Capture a previously authorized payment.
     *
     * @param string $transactionId
     * @param float|null $amount
     * @param array $options
     * @return Transaction
     * @throws CyberSourceException
     */
    public function capture(string $transactionId, float $amount = null, array $options = []): Transaction
    {
        try {
            $response = $this->apiClient->capturePayment($transactionId, $amount, $options);

            return new Transaction($response);
        } catch (Exception $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Void a transaction.
     *
     * @param string $transactionId
     * @param array $options
     * @return Transaction
     * @throws CyberSourceException
     */
    public function void(string $transactionId, array $options = []): Transaction
    {
        try {
            $response = $this->apiClient->voidPayment($transactionId, $options);

            return new Transaction($response);
        } catch (Exception $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Refund a transaction.
     *
     * @param string $transactionId
     * @param float|null $amount
     * @param array $options
     * @return Transaction
     * @throws CyberSourceException
     */
    public function refund(string $transactionId, float $amount = null, array $options = []): Transaction
    {
        try {
            $response = $this->apiClient->refundPayment($transactionId, $amount, $options);

            return new Transaction($response);
        } catch (Exception $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Retrieve a transaction.
     *
     * @param string $transactionId
     * @return Transaction
     * @throws CyberSourceException
     */
    public function retrieveTransaction(string $transactionId): Transaction
    {
        try {
            $response = $this->apiClient->retrieveTransaction($transactionId);

            return new Transaction($response);
        } catch (Exception $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

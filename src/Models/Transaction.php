<?php

namespace Asciisd\CyberSource\Models;

class Transaction
{
    /**
     * The transaction data.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new Transaction instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the transaction ID.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->data['id'] ?? null;
    }

    /**
     * Get the transaction status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->data['status'] ?? null;
    }

    /**
     * Check if the transaction is successful.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        $status = $this->getStatus();
        return in_array($status, ['AUTHORIZED', 'PENDING', 'TRANSMITTED', 'COMPLETED', 'SETTLED']);
    }

    /**
     * Check if the transaction is authorized.
     *
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->getStatus() === 'AUTHORIZED';
    }

    /**
     * Check if the transaction is pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->getStatus() === 'PENDING';
    }

    /**
     * Check if the transaction is declined.
     *
     * @return bool
     */
    public function isDeclined()
    {
        return $this->getStatus() === 'DECLINED';
    }

    /**
     * Get the transaction amount.
     *
     * @return string|null
     */
    public function getAmount()
    {
        return $this->data['order_information']['amountDetails']['totalAmount'] ?? null;
    }

    /**
     * Get the transaction currency.
     *
     * @return string|null
     */
    public function getCurrency()
    {
        return $this->data['order_information']['amountDetails']['currency'] ?? null;
    }

    /**
     * Get the transaction reference.
     *
     * @return string|null
     */
    public function getReference()
    {
        return $this->data['client_reference_information']['code'] ?? null;
    }

    /**
     * Get the transaction reconciliation ID.
     *
     * @return string|null
     */
    public function getReconciliationId()
    {
        return $this->data['reconciliationId'] ?? null;
    }

    /**
     * Get the processor response code.
     *
     * @return string|null
     */
    public function getResponseCode()
    {
        return $this->data['processor_information']['responseCode'] ?? null;
    }

    /**
     * Get the processor approval code.
     *
     * @return string|null
     */
    public function getApprovalCode()
    {
        return $this->data['processor_information']['approvalCode'] ?? null;
    }

    /**
     * Get the transaction links.
     *
     * @return array|null
     */
    public function getLinks()
    {
        return $this->data['links'] ?? null;
    }

    /**
     * Get the capture URL if available.
     *
     * @return string|null
     */
    public function getCaptureUrl()
    {
        $links = $this->getLinks();
        return $links['capture']['href'] ?? null;
    }

    /**
     * Get the void URL if available.
     *
     * @return string|null
     */
    public function getVoidUrl()
    {
        $links = $this->getLinks();
        return $links['void']['href'] ?? null;
    }

    /**
     * Get the refund URL if available.
     *
     * @return string|null
     */
    public function getRefundUrl()
    {
        $links = $this->getLinks();
        return $links['refund']['href'] ?? null;
    }

    /**
     * Get the payment schema if available
     *
     */

    /**
     * Get the raw response data.
     *
     * @return array
     */
    public function getRawResponse()
    {
        return $this->data['raw_response'] ?? [];
    }

    /**
     * Get a specific data attribute.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Get all transaction data.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }
}

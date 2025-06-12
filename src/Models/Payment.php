<?php

namespace Asciisd\CyberSource\Models;

use CyberSource\Model\CreatePaymentRequest;
use CyberSource\Model\Ptsv2paymentsClientReferenceInformation;
use CyberSource\Model\Ptsv2paymentsOrderInformation;
use CyberSource\Model\Ptsv2paymentsOrderInformationAmountDetails;
use CyberSource\Model\Ptsv2paymentsOrderInformationBillTo;
use CyberSource\Model\Ptsv2paymentsPaymentInformation;
use CyberSource\Model\Ptsv2paymentsPaymentInformationCard;
use CyberSource\Model\Ptsv2paymentsProcessingInformation;

class Payment
{
    /**
     * The payment data.
     *
     * @var array
     */
    protected $data;

    /**
     * Whether to capture the payment immediately.
     *
     * @var bool
     */
    protected $capture = false;

    /**
     * Create a new Payment instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Set whether to capture the payment immediately.
     *
     * @param bool $capture
     * @return $this
     */
    public function setCapture(bool $capture)
    {
        $this->capture = $capture;

        return $this;
    }

    /**
     * Check if the payment should be captured immediately.
     *
     * @return bool
     */
    public function isCapture()
    {
        return $this->capture;
    }

    /**
     * Get the client reference code.
     *
     * @return string
     */
    public function getClientReferenceCode()
    {
        return $this->data['reference'] ?? 'payment-' . time();
    }

    /**
     * Get the card number.
     *
     * @return string
     */
    public function getCardNumber()
    {
        return $this->data['card']['number'] ?? '';
    }

    /**
     * Get the card expiration month.
     *
     * @return string
     */
    public function getCardExpirationMonth()
    {
        return $this->data['card']['expiration_month'] ?? '';
    }

    /**
     * Get the card expiration year.
     *
     * @return string
     */
    public function getCardExpirationYear()
    {
        return $this->data['card']['expiration_year'] ?? '';
    }

    /**
     * Get the card security code.
     *
     * @return string|null
     */
    public function getCardSecurityCode()
    {
        return $this->data['card']['security_code'] ?? null;
    }

    /**
     * Get the payment amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->data['amount'] ?? '0.00';
    }

    /**
     * Get the payment currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->data['currency'] ?? 'USD';
    }

    /**
     * Get the billing first name.
     *
     * @return string
     */
    public function getBillingFirstName()
    {
        return $this->data['billing']['first_name'] ?? '';
    }

    /**
     * Get the billing last name.
     *
     * @return string
     */
    public function getBillingLastName()
    {
        return $this->data['billing']['last_name'] ?? '';
    }

    /**
     * Get the billing address.
     *
     * @return string
     */
    public function getBillingAddress()
    {
        return $this->data['billing']['address'] ?? '';
    }

    /**
     * Get the billing city.
     *
     * @return string
     */
    public function getBillingCity()
    {
        return $this->data['billing']['city'] ?? '';
    }

    /**
     * Get the billing state.
     *
     * @return string
     */
    public function getBillingState()
    {
        return $this->data['billing']['state'] ?? '';
    }

    /**
     * Get the billing postal code.
     *
     * @return string
     */
    public function getBillingPostalCode()
    {
        return $this->data['billing']['postal_code'] ?? '';
    }

    /**
     * Get the billing country.
     *
     * @return string
     */
    public function getBillingCountry()
    {
        return $this->data['billing']['country'] ?? '';
    }

    /**
     * Get the billing email.
     *
     * @return string
     */
    public function getBillingEmail()
    {
        return $this->data['billing']['email'] ?? '';
    }

    /**
     * Get the billing phone.
     *
     * @return string
     */
    public function getBillingPhone()
    {
        return $this->data['billing']['phone'] ?? '';
    }

    /**
     * Convert the payment to a CyberSource request.
     *
     * @return \CyberSource\Model\CreatePaymentRequest
     */
    public function toCyberSourceRequest()
    {
        // Create client reference information
        $clientReferenceInformation = new Ptsv2paymentsClientReferenceInformation([
            'code' => $this->getClientReferenceCode()
        ]);

        // Create processing information
        $processingInformation = new Ptsv2paymentsProcessingInformation([
            'capture' => $this->isCapture()
        ]);

        // Create payment information card
        $paymentInformationCardData = [
            'number' => $this->getCardNumber(),
            'expirationMonth' => $this->getCardExpirationMonth(),
            'expirationYear' => $this->getCardExpirationYear()
        ];
        
        // Add security code if provided
        if ($this->getCardSecurityCode()) {
            $paymentInformationCardData['securityCode'] = $this->getCardSecurityCode();
        }
        
        $paymentInformationCard = new Ptsv2paymentsPaymentInformationCard($paymentInformationCardData);

        // Create payment information
        $paymentInformation = new Ptsv2paymentsPaymentInformation([
            'card' => $paymentInformationCard
        ]);

        // Create order information amount details
        $orderInformationAmountDetails = new Ptsv2paymentsOrderInformationAmountDetails([
            'totalAmount' => $this->getAmount(),
            'currency' => $this->getCurrency()
        ]);

        // Create order information bill to
        $orderInformationBillTo = new Ptsv2paymentsOrderInformationBillTo([
            'firstName' => $this->getBillingFirstName(),
            'lastName' => $this->getBillingLastName(),
            'address1' => $this->getBillingAddress(),
            'locality' => $this->getBillingCity(),
            'administrativeArea' => $this->getBillingState(),
            'postalCode' => $this->getBillingPostalCode(),
            'country' => $this->getBillingCountry(),
            'email' => $this->getBillingEmail(),
            'phoneNumber' => $this->getBillingPhone()
        ]);

        // Create order information
        $orderInformation = new Ptsv2paymentsOrderInformation([
            'amountDetails' => $orderInformationAmountDetails,
            'billTo' => $orderInformationBillTo
        ]);

        // Create payment request
        return new CreatePaymentRequest([
            'clientReferenceInformation' => $clientReferenceInformation,
            'processingInformation' => $processingInformation,
            'paymentInformation' => $paymentInformation,
            'orderInformation' => $orderInformation
        ]);
    }
}

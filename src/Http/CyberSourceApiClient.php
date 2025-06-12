<?php

namespace Asciisd\CyberSource\Http;

use Asciisd\CyberSource\Exceptions\CyberSourceException;
use Asciisd\CyberSource\Models\Payment;
use CyberSource\ApiClient;
use CyberSource\ApiException;
use CyberSource\Api\CaptureApi;
use CyberSource\Api\PaymentsApi;
use CyberSource\Api\RefundApi;
use CyberSource\Api\VoidApi;
use CyberSource\Authentication\Core\MerchantConfiguration;
use CyberSource\Model\CapturePaymentRequest;
use CyberSource\Model\CreatePaymentRequest;
use CyberSource\Model\PtsV2PaymentsRefundPost201Response;
use CyberSource\Model\PtsV2PaymentsVoidsPost201Response;
use CyberSource\Model\RefundCaptureRequest;
use CyberSource\Model\RefundPaymentRequest;
use CyberSource\Model\VoidCaptureRequest;
use CyberSource\Model\VoidPaymentRequest;

class CyberSourceApiClient
{
    /**
     * The CyberSource API client instance.
     *
     * @var \CyberSource\ApiClient
     */
    protected $apiClient;

    /**
     * The merchant configuration.
     *
     * @var \CyberSource\Authentication\Core\MerchantConfiguration
     */
    protected $merchantConfig;

    /**
     * Create a new CyberSourceApiClient instance.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->merchantConfig = $this->createMerchantConfig($config);
        $this->apiClient = new ApiClient($this->connectionConfig(), $this->merchantConfig);
    }

    /**
     * Create a payment with CyberSource.
     *
     * @param \Asciisd\CyberSource\Models\Payment $payment
     * @return array
     * @throws \CyberSource\ApiException
     */
    public function createPayment(Payment $payment)
    {
        $api = new PaymentsApi($this->apiClient);
        
        try {
            $requestObj = $payment->toCyberSourceRequest();
            $response = $api->createPayment($requestObj);
            
            return [
                'id' => $response[0]->getId(),
                'status' => $response[0]->getStatus(),
                'client_reference_information' => $response[0]->getClientReferenceInformation(),
                'processor_information' => $response[0]->getProcessorInformation(),
                'order_information' => $response[0]->getOrderInformation(),
                'payment_information' => $response[0]->getPaymentInformation(),
                'risk_information' => $response[0]->getRiskInformation(),
                'links' => $response[0]->getLinks(),
                'raw_response' => $response
            ];
        } catch (ApiException $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Capture a previously authorized payment.
     *
     * @param string $transactionId
     * @param float|null $amount
     * @param array $options
     * @return array
     * @throws \CyberSource\ApiException
     */
    public function capturePayment(string $transactionId, float $amount = null, array $options = [])
    {
        $api = new CaptureApi($this->apiClient);
        
        try {
            // Create client reference information
            $clientReferenceInformation = new \CyberSource\Model\Ptsv2paymentsClientReferenceInformation([
                'code' => $options['reference'] ?? 'capture-' . time()
            ]);
            
            // Create amount details
            $orderInformationAmountDetails = new \CyberSource\Model\Ptsv2paymentsidcapturesOrderInformationAmountDetails([
                'totalAmount' => $amount ?? $options['amount'] ?? null,
                'currency' => $options['currency'] ?? 'USD'
            ]);
            
            // Create order information
            $orderInformation = new \CyberSource\Model\Ptsv2paymentsidcapturesOrderInformation([
                'amountDetails' => $orderInformationAmountDetails
            ]);
            
            // Create capture request
            $requestObj = new CapturePaymentRequest([
                'clientReferenceInformation' => $clientReferenceInformation,
                'orderInformation' => $orderInformation
            ]);
            
            $response = $api->capturePayment($requestObj, $transactionId);
            
            return [
                'id' => $response[0]->getId(),
                'status' => $response[0]->getStatus(),
                'client_reference_information' => $response[0]->getClientReferenceInformation(),
                'processor_information' => $response[0]->getProcessorInformation(),
                'order_information' => $response[0]->getOrderInformation(),
                'raw_response' => $response
            ];
        } catch (ApiException $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Void a transaction.
     *
     * @param string $transactionId
     * @param array $options
     * @return array
     * @throws \CyberSource\ApiException
     */
    public function voidPayment(string $transactionId, array $options = [])
    {
        $api = new VoidApi($this->apiClient);
        
        try {
            // Create client reference information
            $clientReferenceInformation = new \CyberSource\Model\Ptsv2paymentsClientReferenceInformation([
                'code' => $options['reference'] ?? 'void-' . time()
            ]);
            
            // Create void request
            $requestObj = new VoidPaymentRequest([
                'clientReferenceInformation' => $clientReferenceInformation
            ]);
            
            $response = $api->voidPayment($requestObj, $transactionId);
            
            return [
                'id' => $response[0]->getId(),
                'status' => $response[0]->getStatus(),
                'client_reference_information' => $response[0]->getClientReferenceInformation(),
                'raw_response' => $response
            ];
        } catch (ApiException $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Refund a transaction.
     *
     * @param string $transactionId
     * @param float|null $amount
     * @param array $options
     * @return array
     * @throws \CyberSource\ApiException
     */
    public function refundPayment(string $transactionId, float $amount = null, array $options = [])
    {
        $api = new RefundApi($this->apiClient);
        
        try {
            // Create client reference information
            $clientReferenceInformation = new \CyberSource\Model\Ptsv2paymentsClientReferenceInformation([
                'code' => $options['reference'] ?? 'refund-' . time()
            ]);
            
            // Create amount details
            $orderInformationAmountDetails = new \CyberSource\Model\Ptsv2paymentsidcapturesOrderInformationAmountDetails([
                'totalAmount' => $amount ?? $options['amount'] ?? null,
                'currency' => $options['currency'] ?? 'USD'
            ]);
            
            // Create order information
            $orderInformation = new \CyberSource\Model\Ptsv2paymentsidrefundsOrderInformation([
                'amountDetails' => $orderInformationAmountDetails
            ]);
            
            // Create refund request
            $requestObj = new RefundPaymentRequest([
                'clientReferenceInformation' => $clientReferenceInformation,
                'orderInformation' => $orderInformation
            ]);
            
            $response = $api->refundPayment($requestObj, $transactionId);
            
            return [
                'id' => $response[0]->getId(),
                'status' => $response[0]->getStatus(),
                'client_reference_information' => $response[0]->getClientReferenceInformation(),
                'processor_information' => $response[0]->getProcessorInformation(),
                'order_information' => $response[0]->getOrderInformation(),
                'raw_response' => $response
            ];
        } catch (ApiException $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Retrieve a transaction.
     *
     * @param string $transactionId
     * @return array
     * @throws \CyberSource\ApiException
     */
    public function retrieveTransaction(string $transactionId)
    {
        $api = new PaymentsApi($this->apiClient);
        
        try {
            $response = $api->getPayment($transactionId);
            
            return [
                'id' => $response[0]->getId(),
                'status' => $response[0]->getStatus(),
                'client_reference_information' => $response[0]->getClientReferenceInformation(),
                'processor_information' => $response[0]->getProcessorInformation(),
                'order_information' => $response[0]->getOrderInformation(),
                'payment_information' => $response[0]->getPaymentInformation(),
                'risk_information' => $response[0]->getRiskInformation(),
                'raw_response' => $response
            ];
        } catch (ApiException $e) {
            throw new CyberSourceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Create the merchant configuration.
     *
     * @param array $config
     * @return \CyberSource\Authentication\Core\MerchantConfiguration
     */
    protected function createMerchantConfig(array $config)
    {
        $merchantConfig = new MerchantConfiguration();
        
        // Set authentication type
        $merchantConfig->setAuthenticationType($config['auth_type'] ?? 'http_signature');
        
        // Set merchant credentials
        $merchantConfig->setMerchantID($config['merchant_id']);
        $merchantConfig->setApiKeyID($config['api_key_id']);
        $merchantConfig->setSecretKey($config['secret_key']);
        
        // Set environment
        $merchantConfig->setRunEnvironment($config['environment'] ?? 'apitest.cybersource.com');
        
        // Set logging options
        $merchantConfig->setDebug($config['debug'] ?? false);
        $merchantConfig->setLogSize($config['log_size'] ?? '1048576');
        $merchantConfig->setLogFile($config['log_file'] ?? 'cybersource.log');
        $merchantConfig->setLogFileName($config['log_filename'] ?? 'cybersource');
        
        // Validate merchant data
        $merchantConfig->validateMerchantData();
        
        return $merchantConfig;
    }

    /**
     * Get the connection configuration.
     *
     * @return array
     */
    protected function connectionConfig()
    {
        return [
            'verifySslCerts' => true,
            'timeout' => 300,
            'connectTimeout' => 30,
        ];
    }
}

<?php

namespace Asciisd\CyberSource\Http;

use Asciisd\CyberSource\Exceptions\CyberSourceException;
use Asciisd\CyberSource\Models\Payment;
use CyberSource\Api\CaptureApi;
use CyberSource\Api\PaymentsApi;
use CyberSource\Api\RefundApi;
use CyberSource\Api\VoidApi;
use CyberSource\ApiClient;
use CyberSource\ApiException;
use CyberSource\Authentication\Core\AuthException;
use CyberSource\Authentication\Core\MerchantConfiguration;
use CyberSource\Configuration;
use CyberSource\Logging\LogConfiguration;
use CyberSource\Model\CapturePaymentRequest;
use CyberSource\Model\Ptsv2paymentsClientReferenceInformation;
use CyberSource\Model\Ptsv2paymentsidcapturesOrderInformation;
use CyberSource\Model\Ptsv2paymentsidcapturesOrderInformationAmountDetails;
use CyberSource\Model\Ptsv2paymentsidrefundsOrderInformation;
use CyberSource\Model\RefundPaymentRequest;
use CyberSource\Model\VoidPaymentRequest;

class CyberSourceApiClient
{
    /**
     * The CyberSource API client instance.
     *
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * The merchant configuration.
     *
     * @var MerchantConfiguration
     */
    protected $merchantConfig;

    /**
     * Create a new CyberSourceApiClient instance.
     *
     * @param array $config
     * @throws AuthException
     */
    public function __construct(array $config)
    {
        $this->merchantConfig = $this->createMerchantConfig($config);
        $this->apiClient = new ApiClient($this->connectionConfig(), $this->merchantConfig);
    }

    /**
     * Create a payment with CyberSource.
     *
     * @param Payment $payment
     * @return array
     * @throws ApiException|CyberSourceException
     */
    public function createPayment(Payment $payment): array
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
     * @throws ApiException|CyberSourceException
     */
    public function capturePayment(string $transactionId, float $amount = null, array $options = []): array
    {
        $api = new CaptureApi($this->apiClient);

        try {
            // Create client reference information
            $clientReferenceInformation = new Ptsv2paymentsClientReferenceInformation([
                'code' => $options['reference'] ?? 'capture-'.time()
            ]);

            // Create amount details
            $orderInformationAmountDetails = new Ptsv2paymentsidcapturesOrderInformationAmountDetails([
                'totalAmount' => $amount ?? $options['amount'] ?? null,
                'currency' => $options['currency'] ?? 'USD'
            ]);

            // Create order information
            $orderInformation = new Ptsv2paymentsidcapturesOrderInformation([
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
     * @throws ApiException|CyberSourceException
     */
    public function voidPayment(string $transactionId, array $options = []): array
    {
        $api = new VoidApi($this->apiClient);

        try {
            // Create client reference information
            $clientReferenceInformation = new Ptsv2paymentsClientReferenceInformation([
                'code' => $options['reference'] ?? 'void-'.time()
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
     * @throws ApiException|CyberSourceException
     */
    public function refundPayment(string $transactionId, float $amount = null, array $options = []): array
    {
        $api = new RefundApi($this->apiClient);

        try {
            // Create client reference information
            $clientReferenceInformation = new Ptsv2paymentsClientReferenceInformation([
                'code' => $options['reference'] ?? 'refund-'.time()
            ]);

            // Create amount details
            $orderInformationAmountDetails = new Ptsv2paymentsidcapturesOrderInformationAmountDetails([
                'totalAmount' => $amount ?? $options['amount'] ?? null,
                'currency' => $options['currency'] ?? 'USD'
            ]);

            // Create order information
            $orderInformation = new Ptsv2paymentsidrefundsOrderInformation([
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
     * @throws ApiException|CyberSourceException
     */
    public function retrieveTransaction(string $transactionId): array
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
     * @return MerchantConfiguration
     * @throws AuthException
     */
    protected function createMerchantConfig(array $config): MerchantConfiguration
    {
        $merchantConfig = new MerchantConfiguration();

        // Set an authentication type
        $merchantConfig->setAuthenticationType(strtoupper(trim($config['auth_type'] ?? 'http_signature')));

        // Set merchant credentials
        $merchantConfig->setMerchantID(trim($config['merchant_id']));
        $merchantConfig->setApiKeyID($config['api_key_id']);
        $merchantConfig->setSecretKey($config['secret_key']);

        // Set environment
        $merchantConfig->setRunEnvironment($config['environment']);

        // Set logging options
        $logConfiguration = new LogConfiguration();
        $logConfiguration->enableLogging($config['debug'] ?? false);
        $logConfiguration->setLogMaxFiles(3);
        $logConfiguration->setDebugLogFile($config['log_file']);
        $logConfiguration->setErrorLogFile($config['error_log_file']);
        $logConfiguration->setLogLevel($config['log_level']);

        $merchantConfig->setLogFileName($config['log_filename']);
        $merchantConfig->setLogConfiguration($logConfiguration);

        // Validate merchant data
        $merchantConfig->validateMerchantData();

        return $merchantConfig;
    }

    /**
     * Get the connection configuration.
     */
    protected function connectionConfig(): Configuration
    {
        $config = new Configuration();

        $config->setSSLVerification(true);
        $config->setCurlTimeout(300);
        $config->setCurlConnectTimeout(30);

        return $config;
    }
}

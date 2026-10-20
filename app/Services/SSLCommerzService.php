<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SSLCommerzService
{
    /**
     * Start a hosted SSLCOMMERZ checkout session.
     */
    public function initiate(array $parameters): array
    {
        $storeId = config('services.sslcommerz.store_id');
        $storePassword = config('services.sslcommerz.store_password');
        $apiUrl = config('services.sslcommerz.api_url');

        if (
            blank($storeId) ||
            blank($storePassword) ||
            blank($apiUrl)
        ) {
            throw new RuntimeException(
                'SSLCOMMERZ credentials or API URL are missing.'
            );
        }

        $response = Http::asForm()
            ->acceptJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->post($apiUrl, array_merge($parameters, [
                'store_id' => $storeId,
                'store_passwd' => $storePassword,
            ]));

        if ($response->failed()) {
            throw new RuntimeException(
                'SSLCOMMERZ could not start the payment session.'
            );
        }

        $data = $response->json();

        if (
            ! is_array($data) ||
            ($data['status'] ?? null) !== 'SUCCESS' ||
            blank($data['GatewayPageURL'] ?? null) ||
            blank($data['sessionkey'] ?? null)
        ) {
            throw new RuntimeException(
                'SSLCOMMERZ did not return a valid checkout session.'
            );
        }

        return $data;
    }

    /**
     * Validate a completed transaction using its validation ID.
     */
    public function validateTransaction(string $validationId): array
    {
        $credentials = $this->credentials();

        $response = Http::acceptJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->get($this->validationUrl(), [
                'val_id' => $validationId,
                'store_id' => $credentials['store_id'],
                'store_passwd' => $credentials['store_passwd'],
                'format' => 'json',
                'v' => 1,
            ]);

        return $this->decodeResponse(
            $response,
            'SSLCOMMERZ transaction validation failed.'
        );
    }

    /**
     * Query a transaction by the session key returned at checkout initiation.
     * A result may have PENDING status.
     */
    public function queryBySessionKey(string $sessionKey): array
    {
        $credentials = $this->credentials();

        $response = Http::acceptJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->get($this->transactionQueryUrl(), [
                'sessionkey' => $sessionKey,
                'store_id' => $credentials['store_id'],
                'store_passwd' => $credentials['store_passwd'],
                'format' => 'json',
            ]);

        return $this->decodeResponse(
            $response,
            'SSLCOMMERZ session query failed.'
        );
    }

    /**
     * Query transaction records by Artfy's transaction/order number.
     */
    public function queryByTransactionId(string $transactionId): array
    {
        $credentials = $this->credentials();

        $response = Http::acceptJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->get($this->transactionQueryUrl(), [
                'tran_id' => $transactionId,
                'store_id' => $credentials['store_id'],
                'store_passwd' => $credentials['store_passwd'],
                'format' => 'json',
            ]);

        return $this->decodeResponse(
            $response,
            'SSLCOMMERZ transaction query failed.'
        );
    }

    /**
     * Read gateway credentials from Laravel configuration.
     */
    private function credentials(): array
    {
        $storeId = config('services.sslcommerz.store_id');
        $storePassword = config('services.sslcommerz.store_password');

        if (blank($storeId) || blank($storePassword)) {
            throw new RuntimeException(
                'SSLCOMMERZ credentials are missing.'
            );
        }

        return [
            'store_id' => $storeId,
            'store_passwd' => $storePassword,
        ];
    }

    /**
     * Select the correct validation endpoint for the current environment.
     */
    private function validationUrl(): string
    {
        $sandbox = filter_var(
            config('services.sslcommerz.sandbox', true),
            FILTER_VALIDATE_BOOLEAN
        );

        return $sandbox
            ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
            : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php';
    }

    /**
     * Select the correct transaction-query endpoint.
     */
    private function transactionQueryUrl(): string
    {
        $sandbox = filter_var(
            config('services.sslcommerz.sandbox', true),
            FILTER_VALIDATE_BOOLEAN
        );

        return $sandbox
            ? 'https://sandbox.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php'
            : 'https://securepay.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php';
    }

    /**
     * Check the HTTP response and ensure its body is valid JSON data.
     */
    private function decodeResponse(
        \Illuminate\Http\Client\Response $response,
        string $errorMessage
    ): array {
        if ($response->failed()) {
            throw new RuntimeException($errorMessage);
        }

        $data = $response->json();

        if (! is_array($data) || empty($data)) {
            throw new RuntimeException(
                'SSLCOMMERZ returned an invalid response.'
            );
        }

        return $data;
    }
}

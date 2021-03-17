<?php

namespace App\Integrations\Api;

use Facades\App\Integrations\Classes\RequestApi;

class Telco
{
    protected $apiUrl;
    protected $accessToken;

    /**
     * Create new instance of Telco class.
     */
    public function __construct()
    {
        $this->apiUrl = config('services.telco.url');
    }

    /**
     * Get authorization
     * 
     * @return boolean
     */
    public function authorize()
    {
        $url = $this->apiUrl . '/oauth/token';

        $payload = [
            'grant_type'    => config('services.telco.grant_type'),
            'client_id'     => config('services.telco.client_id'),
            'client_secret' => config('services.telco.client_secret'),
            'username'      => config('services.telco.username'),
            'password'      => config('services.telco.password'),
            'scope'         => str_replace(',', ' ', config('services.telco.scope'))
        ];

        $requestOptions = [
            'form_params' => $payload,
            'headers'     => []
        ];

        $logMessage = 'Retrieving user privileges';

        $response = RequestApi::request('POST', $url, $requestOptions, $payload, $logMessage);
        $response = json_decode($response->getBody());

        if (empty($response)) {
            return false;
        }

        $this->accessToken = $response->access_token;
    }

    /**
     * Get profile
     * 
     * @return json
     */
    public function getProfile()
    {
        if (!isset($this->accessToken)) {
            $this->authorize();
        }

        $url = $this->apiUrl . '/profile';

        $requestOptions = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ]
        ];

        $logMessage = 'Retrieving reseller profile';

        $response = RequestApi::request('GET', $url, $requestOptions, [], $logMessage);
        $response = json_decode($response->getBody());

        if (empty($response)) {
            return false;
        }

        return $response;
    }

    /**
     * Get reseller account id
     * 
     * @return string
     */
    public function getResellerAccountId()
    {
        if (!isset($this->accessToken)) {
            $this->authorize();
        }

        $telcoAccountId = (string) $this->getProfile()->account_id;
        $url = $this->apiUrl . '/accounts/' . $telcoAccountId . '/sub-accounts';

        $data = [
            'account_number' => config('services.telco.reseller_id'),
            'recursive'      => 1
        ];

        $requestOptions = [
            'query' => $data,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken
            ]
        ];

        $logMessage = 'Retrieving skyswitch account id';

        $response = RequestApi::request('GET', $url, $requestOptions, $data, $logMessage);
        $response = json_decode($response->getBody());
        $response = $response[0];

        $childrenAccounts = $response->children;

        foreach ($childrenAccounts as $childrenAccount) {
            if ($response->parent_id == $telcoAccountId) {
                return $childrenAccount->id;
            }

            if (count($childrenAccount->child) > 0) {
                foreach ($childrenAccount as $childchildrenAccount) {
                    if ($response->parent_id == $telcoAccountId) {
                        return $childchildrenAccount->id;
                    }
                }
            }
        }
    }

    /**
     * Send message
     * 
     * @return json
     */
    public function sendMessage($messageRequest)
    {
        if (!isset($this->accessToken)) {
            $this->authorize();
        }

        $url = $this->apiUrl . '/accounts/' . $this->getResellerAccountId() . '/messaging/send/inbound';

        $data = [
            'source' => 'tel:' . $messageRequest['reply_to'],
            'destination' => 'user:' . config('services.recipient.user') . '@' . config('services.recipient.domain'),
            'message' => $messageRequest['message']
        ];

        $requestOptions = [
            'body' => json_encode($data),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json'
            ]
        ];

        $logMessage = 'Sending Message';

        $response = RequestApi::request('POST', $url, $requestOptions, $data, $logMessage);
        $response = json_decode($response->getBody());

        return $response;
    }
}
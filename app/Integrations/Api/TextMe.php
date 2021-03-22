<?php

namespace App\Integrations\Api;

use Facades\App\Integrations\Classes\RequestApi;

class TextMe
{
    protected $apiUrl;
    protected $accessToken;

    /**
     * Create new instance of TextMe class.
     */
    public function __construct()
    {
        $this->apiUrl = config('services.text_me_api.url');
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
            'grant_type'    => config('services.text_me_api.grant_type'),
            'client_id'     => config('services.text_me_api.client_id'),
            'client_secret' => config('services.text_me_api.client_secret'),
            'username'      => config('services.text_me_api.username'),
            'password'      => config('services.text_me_api.password'),
            'scope'         => str_replace(',', ' ', config('services.text_me_api.scope'))
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

        $accountId = (string) $this->getProfile()->account_id;
        $url = $this->apiUrl . '/accounts/' . $accountId . '/sub-accounts';

        $data = [
            'account_number' => config('services.text_me_api.reseller_id'),
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
            if ($response->parent_id == $accountId) {
                return $childrenAccount->id;
            }

            if (count($childrenAccount->child) > 0) {
                foreach ($childrenAccount as $childchildrenAccount) {
                    if ($response->parent_id == $accountId) {
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
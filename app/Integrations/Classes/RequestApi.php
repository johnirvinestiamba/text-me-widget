<?php

namespace App\Integrations\Classes;

use GuzzleHttp\Client;

class RequestApi
{
    /**
     * Send request to APIs.
     *
     * @param  string  $requestType     GET or POST
     * @param  string  $url             API URL
     * @param  array   $requestOptions  Request options
     * @param  array   $data            Data
     * @param  string  $logMessage      Log message
     * @return object
     */
    public function request($requestType, $url, $requestOptions, $data, $logMessage)
    {
        $client = new Client();

        $timeStart = microtime(true);
        info(session()->getId() . ': Start ' . $logMessage . ' ' . $url, $data);

        if ($requestType == "GET") {
            $response = $client->get($url, $requestOptions);
        }

        if ($requestType == "POST") {
            $response = $client->post($url, $requestOptions);
        }

        if ($requestType == "PUT") {
            $response = $client->put($url, $requestOptions);
        }

        if ($requestType == "PATCH") {
            $response = $client->patch($url, $requestOptions);
        }

        if ($requestType == "DELETE") {
            $response = $client->delete($url, $requestOptions);
        }

        $timeDiff = microtime(true) - $timeStart;
        info('Time diff: ' . $timeDiff . '. ' . session()->getId() . ': End ' . $logMessage . ' ' . $url, $data);

        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 499) {
            warning('Unauthorized - ' . $logMessage . ' - ' . $url, $data);
        }

        if ($response->getStatusCode() >= 500 && $response->getStatusCode() < 599) {
            danger('Unauthorized - ' . $logMessage . ' - ' . $url, $data);
        }

        return $response;
    }
}
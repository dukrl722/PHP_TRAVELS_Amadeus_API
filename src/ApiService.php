<?php

namespace Src;

use Exception;

class ApiService
{
    private function AmadeusAuth(): void
    {
        try {
            $curl = curl_init();

            $url = $_ENV['API_URL_V1']
                . $_ENV['API_TOKEN_URL'];

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => [
                    'Content-type: application/x-www-form-urlencoded',
                    'Accept: application/json'
                ],
                CURLOPT_POSTFIELDS => http_build_query([
                    'grant_type' => 'client_credentials',
                    'client_id' => $_ENV['API_KEY'],
                    'client_secret' => $_ENV['API_SECRET'],
                ]),
            ]);

            $response = json_decode(curl_exec($curl), true);

            curl_close($curl);

            $_SERVER['token'] = $response['access_token'];

        } catch (Exception $exception) {

            json_encode([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function AmadeusHotelsAPI(array $params)
    {
        try {

            if (empty($_SERVER['token'])) {
                $this->AmadeusAuth();
            }

            $url = $_ENV['API_URL_V1'] . $_ENV['API_HOTELS_URL'] . '?' . http_build_query($params);

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Authorization: Bearer ' . $_SERVER['token']
                ]
            ]);

            $response = json_decode(curl_exec($curl), true);

            curl_close($curl);

            return $response;

        } catch (Exception $exception) {
            return json_encode([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }
}

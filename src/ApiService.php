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

            foreach ($response['data'] as $key => $value) {
                $response['data'][$key] = $this->AmadeusHotelDetailAPI($value);
                $response['data'][$key]['address'] = $this->GoogleMapsAPI($value);
            }

            return $response;

        } catch (Exception $exception) {
            return json_encode([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }

    /**
     * @throws Exception
     */
    private function AmadeusHotelDetailAPI(array $hotel): array
    {
        try {

            $url = $_ENV['API_URL_V3'] . $_ENV['API_HOTEL_DETAIL_URL'] . '?hotelIds=' . $hotel['hotelId'];

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => [
                    'Accept: application/vnd.amadeus+json',
                    'Authorization: Bearer ' . $_SERVER['token']
                ]
            ]);

            $response = json_decode(curl_exec($curl), true);

            curl_close($curl);

            $hotel['price'] = 0.00;
            $hotel['price_per_night'] = 0.00;
            $hotel['currency'] = 'USD';
            $hotel['booking_currency'] = 'USD';
            $hotel['service_fee'] = 0.00;

            if (!empty($response['data'])) {
                $hotel['price'] = floatval($response['data'][0]['offers'][0]['price']['total']);
                $hotel['price_per_night'] = floatval($response['data'][0]['offers'][0]['price']['total']);
                $hotel['currency'] = $response['data'][0]['offers'][0]['price']['currency'];
                $hotel['booking_currency'] = $response['data'][0]['offers'][0]['price']['currency'];

                if (!empty($response['data'][0]['offers'][0]['price']['base'])) {
                    $hotel['service_fee'] = number_format(
                        floatval(
                            $response['data'][0]['offers'][0]['price']['total'] - $response['data'][0]['offers'][0]['price']['base']
                        )
                    , 2);
                }
            }

            return $hotel;

        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * @author Eduardo da Silva
     * @since 23/11/2023
     * @return mixed
     *
     * Don't work
     */
    private function AmadeusRatingDetailAPI()
    {
        if (empty($_SERVER['token'])) {
            $this->AmadeusAuth();
        }

        $url = $_ENV['API_URL_V2'] . $_ENV['API_HOTEL_RATING_URL'] . '?hotelIds=MUDXBAIR';

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Accept: application/vnd.amadeus+json',
                'Authorization: Bearer ' . $_SERVER['token']
            ]
        ]);

        $response = json_decode(curl_exec($curl), true);

        curl_close($curl);

        return $response;
    }

    /**
     * @throws Exception
     */
    private function GoogleMapsAPI(array $data)
    {
        try {

            $url = $_ENV['API_GOOGLE_MAPS_URL']
                . '?latlng='
                . $data['geoCode']['latitude']
                . ','
                . $data['geoCode']['longitude']
                . '&key='
                . $_ENV['API_GOOGLE_MAPS_KEY'];

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]);

            $response = json_decode(curl_exec($curl), true);

            curl_close($curl);

            if ($response['status'] == 'OK') {
                return $response['results'][0]['formatted_address'];
            }

        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}

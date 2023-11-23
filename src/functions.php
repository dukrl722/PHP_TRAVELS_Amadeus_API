<?php

function hotelsResource(array $data) {

    $response = [];

    foreach ($data as $key => $value) {
        $response[] = [
            'hotel_id' => $value['hotelId'],
            'img' => '',
            'name' => $value['name'],
            'location' => '', // x
            'address' => $value['address'],
            'stars' => 5, // x
            'rating' => 100, // x
            'latitude' => $value['geoCode']['latitude'],
            'longitude' => $value['geoCode']['longitude'],
            'price' => $value['price'],
            'price_per_night' => $value['price_per_night'],
            'currency' => $value['currency'],
            'booking_currency' => $value['booking_currency'],
            'service_fee' => floatval($value['service_fee']),
            'suplier_name' => 'amadeus_hotels',
            'redirect' => '',
            'booking_data' => []
        ];
    }

    return $response;
}

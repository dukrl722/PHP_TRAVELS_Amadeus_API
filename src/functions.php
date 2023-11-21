<?php

function hotelsResource(array $data) {

    $response = [];

    foreach ($data as $value) {
        $response[] = [
            'hotel_id' => $value['hotelId'],
            'img' => null,
            'name' => $value['name'],
            'location' => '',
            'address' => 'Dubai - AE',
            'latitude' => $value['geoCode']['latitude'],
            'longitude' => $value['geoCode']['longitude'],
            'suplier_name' => 'amadeus_hotels',
            'redirect' => '',
            'booking_data' => []
        ];
    }

    return $response;
}

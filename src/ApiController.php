<?php

namespace Src;

use Exception;

class ApiController
{
    public function getAPIData(array $query): bool|string
    {

        $service = new ApiService();

        try {

            $params = $this->queryValidation($query);

            $response = $service->AmadeusHotelsAPI($params);

            if (!empty($response['errors'])) {
                return json_encode([
                    'code' => 400 . ' - ' . end($response['errors'])['code'],
                    'title' => end($response['errors'])['title'],
                    'message' => end($response['errors'])['detail']
                ]);
            }

            if ($response['meta']['count'] === 0) {
                return json_encode([
                    'status' => 404,
                    'count' => $response['meta']['count'],
                    'data' => [],
                    'message' => 'Nothing for you, bro'
                ]);
            }

            return json_encode([
                'status' => 200,
                'count' => $response['meta']['count'],
                'data' => $response['data']
            ]);

        } catch (Exception $exception) {
            return json_encode([
                'status' => 500,
                'message' => $exception->getMessage()
            ]);
        }
    }

    private function queryValidation(array $query): array|string
    {

        $filter = [
            'cityCode' => 'DXB',
            'radius' => 5
        ];

        if (!empty($query['radius']) && is_integer($query['radius'])) {
            $filter['radius'] = $query['radius'];
        } elseif (!empty($query['radius'])) {
            return json_encode([
                'status' => 400,
                'message' => 'This field need to be an integer'
            ]);
        }

        if (!empty($query['radiusUnit']) && in_array(mb_strtoupper($query['radiusUnit']), ['KM', 'MILE'])) {
            $filter['radiusUnit'] = mb_strtoupper($query['radiusUnit']);
        } elseif (!empty($query['radiusUnit'])) {
            return json_encode([
                'status' => 400,
                'message' => 'This field need to be KM or MILE'
            ]);
        }

        if (!empty($query['ratings']) && in_array($query['ratings'], [1, 2, 3, 4, 5])) {
            $filter['ratings'] = $query['ratings'];
        } elseif (!empty($query['ratings'])) {
            return json_encode([
                'status' => 400,
                'message' => 'This field need to be between 1 and 5'
            ]);
        }

        if (!empty($query['hotelSource']) && in_array(mb_strtoupper($query['hotelSource']), ['ALL', 'BEDBANK', 'DIRECTCHAIN'])) {
            $filter['hotelSource'] = mb_strtoupper($query['hotelSource']);
        } elseif (!empty($query['hotelSource'])) {
            return json_encode([
                'status' => 400,
                'message' => 'This field need to be ALL, BEDBANK OR DIRECTCHAIN'
            ]);
        }

        return $filter;
    }
}

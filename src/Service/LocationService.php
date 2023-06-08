<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class LocationService extends AbstractController
{
    // Shop Coordinates
    const LATITUDE_SHOP = 48.62944030761719;
    const LONGITUDE_SHOP = 2.421143054962158;

    // Shop Shipping Prices
    const MIN_SHIPPING_FEES = 10;
    const AVERAGE_SHIPPING_FEES = 100;
    const CAR_FUEL_CONSUMPTION = 10.00;
    const FUEL_PRICE = 2.00;
    const ROUND_TRIP = 2;

    public function getDistanceAndDurationFromShopToCustomer($coordinatesCustomer)
    {
        $httpClient = HttpClient::create();

        $accessToken = $_ENV['API_LOCATIONIQ_KEY'];

        $lngShop = self::LONGITUDE_SHOP;
        $latShop = self::LATITUDE_SHOP;

        $lngCustomer = $coordinatesCustomer['lng'];
        $latCustomer = $coordinatesCustomer['lat'];

        $coordinates = $lngShop . ',' . $latShop . ';' . $lngCustomer . ',' . $latCustomer;

        $url = 'https://eu1.locationiq.com/v1/matrix/driving/' . $coordinates . '?key=' . $accessToken . '&sources=0&destinations=1&annotations=duration,distance';

        try {
            $response = $httpClient->request('GET', $url);
            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $content = $response->getContent();

                $jsonDecode = new JsonDecode();

                $data = $jsonDecode->decode($content, 'json');

                $dataFormatted = ['distance' => $data->distances[0][0], 'duration' => $data->durations[0][0]];
            } else {
                $dataFormatted = ['distance' => 0, 'duration' => 0];
            }

            return $dataFormatted;
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }
    }

    public function calculateShippingFeesFromDistance($routingInfo)
    {
        $distance = $routingInfo['distance'] / 1000;

        if ($distance == 0) {
            return self::AVERAGE_SHIPPING_FEES;
        }

        $shippingFees = self::FUEL_PRICE * self::CAR_FUEL_CONSUMPTION * $distance * self::ROUND_TRIP / 100;

        if ($shippingFees < 10) {
            return self::MIN_SHIPPING_FEES;
        }

        return $shippingFees;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class DeliveryService
{
    private string $baseUrl = 'https://api.openrouteservice.org';

    private const MAX_REFERENCE_DISTANCE_KM = 750.0;
    private const MIN_DELIVERY_FEE = 40.0;
    private const MAX_DELIVERY_FEE = 300.0;

    /**
     * Retrieve the OpenRouteService API key from Laravel configuration.
     */
    private function apiKey(): string
    {
        $key = config('services.openrouteservice.key');

        if (blank($key)) {
            throw new RuntimeException(
                'OpenRouteService API key is missing. Check your Laravel configuration.'
            );
        }

        return (string) $key;
    }

    /**
     * Approximate Bangladesh coordinate validation.
     *
     * This bounding box is not an exact national boundary polygon.
     */
    private function isWithinBangladesh(
        float $latitude,
        float $longitude
    ): bool {
        if (
            !is_finite($latitude)
            || !is_finite($longitude)
        ) {
            return false;
        }

        return $latitude >= 20.5
            && $latitude <= 26.7
            && $longitude >= 88.0
            && $longitude <= 92.8;
    }

    /**
     * Find a customer address in Bangladesh using OpenRouteService.
     *
     * ORS returns coordinates in [longitude, latitude] order.
     *
     * @return array{lng: float, lat: float}
     */
    public function geocode(string $address): array
    {
        $address = trim($address);

        if ($address === '') {
            throw new RuntimeException(
                'Please enter a complete delivery address.'
            );
        }

        $response = Http::connectTimeout(10)
            ->timeout(20)
            ->withHeaders([
                'Authorization' => $this->apiKey(),
                'Accept' => 'application/json',
            ])
            ->get(
                $this->baseUrl . '/geocode/search',
                [
                    'text' => $address,
                    'boundary.country' => 'BGD',
                    'size' => 1,
                ]
            );

        $response->throw();

        $feature = $response->json('features.0');
        $coordinates = data_get(
            $feature,
            'geometry.coordinates'
        );

        if (
            !is_array($coordinates)
            || !isset($coordinates[0], $coordinates[1])
            || !is_numeric($coordinates[0])
            || !is_numeric($coordinates[1])
        ) {
            throw new RuntimeException(
                'Address not found in Bangladesh. '
                . 'Please enter a more complete address or select the location on the map.'
            );
        }

        $longitude = (float) $coordinates[0];
        $latitude = (float) $coordinates[1];

        if (
            !$this->isWithinBangladesh($latitude, $longitude)
        ) {
            throw new RuntimeException(
                'Please enter a delivery address within Bangladesh.'
            );
        }

        return [
            'lng' => $longitude,
            'lat' => $latitude,
        ];
    }

    /**
     * Calculate road distance and delivery fee.
     *
     * If both customer coordinates are supplied, use the selected map
     * marker. If neither is supplied, geocode the address for compatibility.
     *
     * Fee formula:
     *   ৳300 × (road distance in km / 750)
     *
     * Minimum fee: ৳40
     * Maximum fee: ৳300
     *
     * @return array{distance_km: float, delivery_fee: float}
     */
    public function calculateFromCoordinates(
        float $artistLat,
        float $artistLng,
        string $customerAddress,
        ?float $customerLat = null,
        ?float $customerLng = null
    ): array {
        if (
            !$this->isWithinBangladesh($artistLat, $artistLng)
        ) {
            throw new RuntimeException(
                'The artist pickup location must be in Bangladesh.'
            );
        }

        /*
         * Coordinates must be supplied together. Reject incomplete pairs
         * instead of silently using a different destination.
         */
        if (
            ($customerLat === null && $customerLng !== null)
            || ($customerLat !== null && $customerLng === null)
        ) {
            throw new RuntimeException(
                'Both customer latitude and longitude must be provided.'
            );
        }

        if ($customerLat !== null && $customerLng !== null) {
            if (
                !$this->isWithinBangladesh(
                    $customerLat,
                    $customerLng
                )
            ) {
                throw new RuntimeException(
                    'Please select a delivery location within Bangladesh.'
                );
            }

            $destination = [
                'lat' => $customerLat,
                'lng' => $customerLng,
            ];
        } else {
            // Backward compatibility for address-only calls.
            $destination = $this->geocode($customerAddress);
        }

        $response = Http::connectTimeout(10)
            ->timeout(30)
            ->withHeaders([
                'Authorization' => $this->apiKey(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
            ->post(
                $this->baseUrl . '/v2/directions/driving-car',
                [
                    'coordinates' => [
                        [$artistLng, $artistLat],
                        [$destination['lng'], $destination['lat']],
                    ],
                ]
            );

        $response->throw();

        $distanceMeters = $response->json(
            'routes.0.summary.distance'
        );

        if (
            !is_numeric($distanceMeters)
            || !is_finite((float) $distanceMeters)
            || (float) $distanceMeters < 0
        ) {
            throw new RuntimeException(
                'OpenRouteService did not return a valid road distance.'
            );
        }

        $distanceKm = (float) $distanceMeters / 1000;

        if (!is_finite($distanceKm)) {
            throw new RuntimeException(
                'The calculated road distance is invalid.'
            );
        }


        $calculatedFee = self::MAX_DELIVERY_FEE
            * ($distanceKm / self::MAX_REFERENCE_DISTANCE_KM);

        $deliveryFee = round(
            min(
                self::MAX_DELIVERY_FEE,
                max(
                    self::MIN_DELIVERY_FEE,
                    $calculatedFee
                )
            ),
            2
        );

        return [
            'distance_km' => round($distanceKm, 2),
            'delivery_fee' => $deliveryFee,
        ];
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
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

    /** Return several likely matches so the buyer can pick the correct one. */
    public function geocodeCandidates(string $address): array
    {
        $address = trim($address);

        if ($address === '') {
            throw new RuntimeException(
                'Please enter a complete delivery address.'
            );
        }

        $cacheKey = 'delivery-geocode:' . hash('sha256', mb_strtolower($address));
        $cachedMatches = Cache::get($cacheKey);
        if (is_array($cachedMatches) && $cachedMatches !== []) {
            return $cachedMatches;
        }

        $matches = (function () use ($address): array {
                $matches = [];
                try {
                    $response = Http::connectTimeout(8)
                        ->timeout(12)
                        ->withHeaders([
                            'Authorization' => $this->apiKey(),
                            'Accept' => 'application/json',
                        ])
                        ->get($this->baseUrl . '/geocode/search', [
                            'text' => $address,
                            'boundary.country' => 'BGD',
                            'size' => 5,
                        ]);

                    $response->throw();
                    foreach ($response->json('features', []) as $feature) {
                        $coordinates = data_get($feature, 'geometry.coordinates');
                        if (! is_array($coordinates)
                            || ! isset($coordinates[0], $coordinates[1])
                            || ! is_numeric($coordinates[0])
                            || ! is_numeric($coordinates[1])) {
                            continue;
                        }
                        $point = [
                            'lng' => (float) $coordinates[0],
                            'lat' => (float) $coordinates[1],
                            'label' => (string) (data_get($feature, 'properties.label')
                                ?: data_get($feature, 'properties.name')
                                ?: 'Possible match in Bangladesh'),
                        ];
                        if ($this->isWithinBangladesh($point['lat'], $point['lng'])) {
                            $matches[] = $point;
                        }
                    }
                } catch (\Throwable) {
                    // Continue through independent geocoding providers.
                }

                foreach ($this->geocodeWithPhoton($address) as $point) {
                    $duplicate = collect($matches)->contains(fn (array $match): bool =>
                        abs($match['lat'] - $point['lat']) < 0.001
                        && abs($match['lng'] - $point['lng']) < 0.001
                    );
                    if (! $duplicate) {
                        $matches[] = $point;
                    }
                }

                foreach ($this->geocodeWithOpenStreetMap($address) as $point) {
                    $duplicate = collect($matches)->contains(fn (array $match): bool =>
                        abs($match['lat'] - $point['lat']) < 0.001
                        && abs($match['lng'] - $point['lng']) < 0.001
                    );
                    if (! $duplicate) {
                        $matches[] = $point;
                    }
                }

                return array_slice($matches, 0, 8);
        })();

        if ($matches !== []) {
            Cache::put($cacheKey, $matches, now()->addDays(30));
        }

        return $matches;

    }

    /** Search OpenStreetMap data through Photon as an additional fallback. */
    private function geocodeWithPhoton(string $address): array
    {
        try {
            $response = Http::connectTimeout(8)
                ->timeout(12)
                ->withHeaders([
                    'User-Agent' => (string) config('app.name', 'Artfy')
                        . ' address lookup (' . rtrim((string) config('app.url'), '/') . ')',
                    'Accept' => 'application/json',
                ])
                ->get('https://photon.komoot.io/api/', [
                    'q' => $address,
                    'limit' => 5,
                    'lang' => 'en',
                    'bbox' => '88,20.5,92.8,26.7',
                ]);

            if (! $response->successful()) {
                return [];
            }
        } catch (\Throwable) {
            return [];
        }

        $matches = [];
        foreach ($response->json('features', []) as $feature) {
            $coordinates = data_get($feature, 'geometry.coordinates');
            if (! is_array($coordinates)
                || ! isset($coordinates[0], $coordinates[1])
                || ! is_numeric($coordinates[0])
                || ! is_numeric($coordinates[1])) {
                continue;
            }

            $longitude = (float) $coordinates[0];
            $latitude = (float) $coordinates[1];
            if (! $this->isWithinBangladesh($latitude, $longitude)) {
                continue;
            }

            $properties = (array) data_get($feature, 'properties', []);
            $labelParts = array_filter([
                $properties['name'] ?? null,
                $properties['street'] ?? null,
                $properties['district'] ?? null,
                $properties['county'] ?? null,
                $properties['city'] ?? null,
                $properties['state'] ?? null,
                $properties['country'] ?? null,
            ], fn ($part) => is_string($part) && trim($part) !== '');

            $matches[] = [
                'lat' => $latitude,
                'lng' => $longitude,
                'label' => implode(', ', array_unique($labelParts))
                    ?: 'Possible match in Bangladesh',
            ];
        }

        return $matches;
    }

    /** Keep the first-match API for server-side compatibility. */
    public function geocode(string $address): array
    {
        $matches = $this->geocodeCandidates($address);
        $result = $matches[0] ?? null;

        if (! is_array($result)) {
            throw new RuntimeException(
                'We could not find that address automatically. Add the area and district, or click the correct location on the map.'
            );
        }

        $longitude = (float) $result['lng'];
        $latitude = (float) $result['lat'];

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
     * Low-volume fallback for local addresses ORS does not recognize.
     * Results are cached, and requests are limited to one per second per app.
     */
    private function geocodeWithOpenStreetMap(string $address): array
    {
        $url = rtrim((string) config('app.url'), '/');
        $appName = preg_replace('/[^A-Za-z0-9 ._-]/', '', (string) config('app.name', 'Artfy'));
        $userAgent = trim($appName . ' address lookup (' . $url . ')');

        try {
            $response = RateLimiter::attempt(
                'openstreetmap-nominatim-geocode',
                1,
                fn () => Http::connectTimeout(8)
                    ->timeout(12)
                    ->withHeaders([
                        'User-Agent' => $userAgent,
                        'Referer' => $url,
                        'Accept' => 'application/json',
                    ])
                    ->get('https://nominatim.openstreetmap.org/search', [
                        'q' => $address,
                        'format' => 'jsonv2',
                        'countrycodes' => 'bd',
                        'limit' => 5,
                    ]),
                1
            );
        } catch (\Throwable) {
            return [];
        }

        if (! $response || ! $response->successful()) {
            return [];
        }

        $matches = [];
        foreach (array_slice($response->json() ?: [], 0, 5) as $match) {
            if (! is_array($match)
                || ! is_numeric($match['lat'] ?? null)
                || ! is_numeric($match['lon'] ?? null)) {
                continue;
            }
            $point = [
                'lat' => (float) $match['lat'],
                'lng' => (float) $match['lon'],
                'label' => (string) ($match['display_name'] ?? 'Possible match in Bangladesh'),
            ];
            if ($this->isWithinBangladesh($point['lat'], $point['lng'])) {
                $matches[] = $point;
            }
        }

        return $matches;
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

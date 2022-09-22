<?php declare(strict_types=1);

namespace DOvereem\RDW\RDWOpenDataApiClient;

use DOvereem\RDW\RDWOpenDataApiClient\Exception\ServiceUnavailableException;
use DOvereem\RDW\RDWOpenDataApiClient\Exception\UnexpectedApiResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use JsonException;

class RDWOpenDataApiClient
{
    protected function createGuzzleClient(): Client
    {
        return new Client([
            'connect_timeout' => 5,
            'timeout' => 10,
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
    }

    protected function createQuery($key, $value, $limit=1, $offset=0): array
    {
        return [
            '$limit' => $limit,
            '$offset' => $offset,
            "{$key}" => $value
        ];
    }

    protected function normalizeLicensePlateNumber(string $licensePlateNumber): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper($licensePlateNumber));
    }

    protected function processApiResponse(Response $response): array
    {
        if ($response->getStatusCode() !== 200) {
            throw new ServiceUnavailableException("API endpoint returned an invalid status. Expected: 200, Received: {$response->getStatusCode()}");
        }

        try {
            $results = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new UnexpectedApiResponseException("API endpoint did not return valid JSON. Received: " . $response->getBody()->getContents());
        }

        if (!is_array($results)) {
            throw new UnexpectedApiResponseException("API endpoint did not return an expected JSON array. Received: " . $response->getBody()->getContents());
        }

        return $results;
    }


    public function getLicensedVehicleDataByLicensePlateNumber(string $licensePlateNumber): array
    {
        $licensePlateNumber = $this->normalizeLicensePlateNumber($licensePlateNumber);

        $response = $this->createGuzzleClient()->get(
            RDWOpenDataApiEndpoints::LICENCED_VEHICLES,
            [
                'query' => $this->createQuery('kenteken', $licensePlateNumber)
            ]
        );
        $results = $this->processApiResponse($response);

        return empty($results) ? [] : $results[0];
    }

}

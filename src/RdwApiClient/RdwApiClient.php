<?php declare(strict_types=1);

namespace DOvereem\RdwApiClient;

use DOvereem\RdwApiClient\Exception\ServiceUnavailableException;
use DOvereem\RdwApiClient\Exception\UnexpectedApiResponseException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use JsonException;

class RdwApiClient
{

    private $apiUrl = 'https://opendata.rdw.nl/resource/m9d7-ebf2.json';



    protected function createGuzzleClient(): Client
    {
        return new Client([
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
            RdwApiEndpoints::LICENCED_VEHICLES,
            [
                'query' => $this->createQuery('kenteken', $licensePlateNumber)
            ]
        );
        $results = $this->processApiResponse($response);

        return empty($results) ? [] : $results[0];
    }

}

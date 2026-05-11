<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DaDataService
{
    private const ORGANIZATION_BY_INN_ENDPOINT = '/suggestions/api/4_1/rs/findById/party';

    public function __construct(
        private readonly string $daDataHost,
        private readonly string $daDataToken,
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    /**
     * @param string $inn
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function getOrganizationsByInn(string $inn): ResponseInterface
    {
        $url = $this->daDataHost . self::ORGANIZATION_BY_INN_ENDPOINT;

        return $this->httpClient->request('POST', $url, [
            'headers' => $this->getHeaders(),
            'json' => $this->getInnRequest($inn)]);
    }

    /**
     * @return string[]
     */
    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Token ' . $this->daDataToken,
        ];
    }

    /**
     * @param string $inn
     * @return string[]
     */
    private function getInnRequest(string $inn): array
    {
        return [
            'query' => $inn,
            'branch_type' => 'MAIN',
        ];
    }
}
